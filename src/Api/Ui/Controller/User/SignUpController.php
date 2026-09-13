<?php

declare(strict_types=1);

namespace Api\Ui\Controller\User;

use Api\Application\Command\User\UserWeb\RegistrarUsuarioCommand;
use Api\Domain\Collection\Common\FormErrorDtoCollection;
use Api\Domain\Dto\Common\FormErrorDto;
use Api\Domain\Dto\Common\FormResponseDto;
use Api\Ui\Request\User\SignUpUserRequest;
use App\Domain\Dto\User\DetalleUser;
use App\Domain\Exception\Model\User\UserWeb\UserWebAlreadyExists;
use App\Domain\Exception\ValueObject\Security\PasswordsDoNotMatch;
use App\Domain\Exception\ValueObject\ValueObjectException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Domain\CommandBus\CommandBusRead;
use App\Domain\CommandBus\CommandBusWrite;

#[AsController]
final class SignUpController extends AbstractController
{

    public const TIPO_FORM = 'signup-user';

    /**
     * @var CommandBusWrite
     */
    private CommandBusWrite $commandBusWrite;

    /**
     * @var CommandBusRead
     */
    private CommandBusRead $commandBusRead;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * Both of these were written and read without ever being declared. PHP 8.2
     * deprecates creating dynamic properties, and reading $this->errors before
     * anything set it (an unknown {tipoForm}) raised an undefined-property
     * warning on the way to the 400.
     */
    private ?string $errorMessage = null;

    private ?FormErrorDtoCollection $errors = null;

    private LoggerInterface $logger;

    /**
     * Las excepciones cuyo mensaje está escrito para que lo lea quien rellena el
     * formulario. Todo lo demás -un fallo de Doctrine, un TypeError, una
     * conexión caída- se registra y se contesta con un mensaje genérico.
     *
     * `ValueObjectException` entra entera porque es la base de las validaciones
     * de los value objects ("el e-mail no es válido", "el nombre es demasiado
     * largo"): describen lo que el propio llamante acaba de enviar.
     */
    private const MENSAJES_PARA_EL_USUARIO = [
        ValueObjectException::class,
        UserWebAlreadyExists::class,
        PasswordsDoNotMatch::class,
    ];

    private const ERROR_GENERICO = 'No se ha podido completar el registro. Inténtalo de nuevo más tarde.';

    #[Route(
        path: '/users/{tipoForm}',
        name: 'api_signup',
        defaults: [
            '_api_resource_class' => FormResponseDto::class,
            '_api_item_operation_name' => 'signup',
        ],
        methods: ['POST'],
    )]
    public function signUpUser(
        Request            $request,
        string             $tipoForm,
        CommandBusWrite    $commandBusWrite,
        CommandBusRead     $commandBusRead,
        ValidatorInterface $validator,
        LoggerInterface    $logger
    ): JsonResponse
    {
        $this->commandBusWrite = $commandBusWrite;
        $this->commandBusRead = $commandBusRead;
        $this->validator = $validator;
        $this->logger = $logger;
        $postData = $this->getPostData($request);
        $formResponseDto = null;
        if ($tipoForm === self::TIPO_FORM) {
            $formResponseDto = $this->formRegistrarUsuario($postData);
        }

        if ($formResponseDto === null) {
            return new JsonResponse(FormResponseDto::formFail(
                self::TIPO_FORM,
                $this->errorMessage ?? 'Error al procesar el formulario',
                $this->errors
            )->toArray(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($formResponseDto->toArray(), Response::HTTP_CREATED);
    }

    /**
     * ¿El mensaje de esta excepción está escrito para quien rellena el
     * formulario?
     */
    private function esMensajeParaElUsuario(\Throwable $e): bool
    {
        foreach (self::MENSAJES_PARA_EL_USUARIO as $clase) {
            if ($e instanceof $clase) {
                return true;
            }
        }

        return false;
    }

    private function getPostData(Request $request): array
    {
        $postData = json_decode($request->getContent(), true);

        if (!$postData) {
            $postData = [];
        }

        return $postData;
    }

    private function formRegistrarUsuario(array $postData): ?FormResponseDto
    {
        $request = SignUpUserRequest::fromArray($postData, $this->validator);

        if (!$request->isValid()) {
            $this->errorMessage = 'No se ha podido registrar el usuario';
            $this->errors = $request->getErrors();

            return null;
        }

        try {
            $user = $this->commandBusWrite->handle(
                new RegistrarUsuarioCommand(
                    $request->email,
                    $request->nombre,
                    $request->password,
                    $request->passwordRepeat
                )
            );

            /** @var DetalleUser $user */
            return FormResponseDto::formSuccess(
                self::TIPO_FORM,
                'Se ha registrado el usuario',
                [
                    'user_id' => $user->id->asString(),
                ],
            );
        } catch (\Throwable $e) {
            // El mensaje de la excepción sólo sale al cliente si está escrito
            // para él. Antes salía siempre: este endpoint es público y sin
            // autenticar, así que cualquier fallo inesperado -una
            // DriverException de Doctrine con la consulta y el DSN dentro, un
            // TypeError con la ruta del fichero, un timeout con el host y el
            // puerto de la base de datos- se devolvía tal cual en el cuerpo del
            // 400. Eso describe la infraestructura a quien sepa provocarlo.
            if (! $this->esMensajeParaElUsuario($e)) {
                // El detalle se conserva donde puede verlo quien opera el
                // servicio, con la traza completa, en vez de en la respuesta.
                $this->logger->error(
                    'Fallo inesperado al registrar un usuario',
                    ['exception' => $e]
                );

                $this->errorMessage = self::ERROR_GENERICO;
                $this->errors = null;

                return null;
            }

            $this->errorMessage = 'No se ha podido registrar el usuario';
            $this->errors = FormErrorDtoCollection::fromElements(
                [
                    FormErrorDto::create(
                        'error',
                        $e->getMessage()
                    ),
                ]
            );

            if ($e instanceof UserWebAlreadyExists) {
                $this->errorMessage = 'Ya existe un usuario registrado con este correo';
            }

            if ($e instanceof PasswordsDoNotMatch) {
                $this->errorMessage = 'Las contraseñas no coinciden';
            }

            return null;
        }
    }
}