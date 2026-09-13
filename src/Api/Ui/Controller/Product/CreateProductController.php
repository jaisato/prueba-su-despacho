<?php

declare(strict_types=1);

namespace Api\Ui\Controller\Product;

use Api\Application\Command\Product\CreateProductCommand;
use Api\Domain\Collection\Common\FormErrorDtoCollection;
use Api\Domain\Dto\Common\FormErrorDto;
use Api\Domain\Dto\Common\FormResponseDto;
use Api\Domain\Service\User\UserWebTransformer;
use Api\Ui\Request\Product\CreateProductRequest;
use App\Domain\Dto\Product\DetalleProduct;
use App\Domain\Exception\Model\User\UserWeb\UserWebNotFound;
use App\Domain\Exception\ValueObject\ValueObjectException;
use App\Infrastructure\Security\User\SfUserWeb;
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
final class CreateProductController extends AbstractController
{

    public const TIPO_FORM = 'create-product';

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


    private SfUserWeb $userWeb;

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
     * de los value objects ("el precio no es válido", "la descripción es
     * demasiado larga"): describen lo que el propio llamante acaba de enviar.
     */
    private const MENSAJES_PARA_EL_USUARIO = [
        ValueObjectException::class,
        UserWebNotFound::class,
    ];

    private const ERROR_GENERICO = 'No se ha podido crear el producto. Inténtalo de nuevo más tarde.';

    #[Route(
        path: '/form/{tipoForm}',
        name: 'api_create_product_form',
        defaults: [
            '_api_resource_class' => FormResponseDto::class,
            '_api_item_operation_name' => 'product_form',
        ],
        methods: ['POST'],
    )]
    public function createProduct(
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

        // Defence in depth: access_control already requires ROLE_WEB here, but
        // this endpoint reads the authenticated user unconditionally, and a
        // firewall pattern that stops matching (which is exactly what had
        // happened) must fail closed with a 403 rather than reach
        // UserWebTransformer with null and blow up as a TypeError 500.
        $this->denyAccessUnlessGranted('ROLE_WEB');

        $this->userWeb       = UserWebTransformer::transform($this->getUser());

        $postData = $this->getPostData($request);
        $formResponseDto = null;
        if ($tipoForm === self::TIPO_FORM) {
            $formResponseDto = $this->formCreateProduct($postData);
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

    private function getPostData(Request $request): array
    {
        $postData = json_decode($request->getContent(), true);

        if (!$postData) {
            $postData = [];
        }

        return $postData;
    }

    /**
     * @param array $postData
     *
     * @return FormResponseDto|null
     */
    private function formCreateProduct(array $postData): ?FormResponseDto
    {
        $request = CreateProductRequest::fromArray($postData, $this->validator);

        if (!$request->isValid()) {
            $this->errorMessage = 'No se ha podido crear el producto';
            $this->errors = $request->getErrors();

            return null;
        }

        try {
            $product = $this->commandBusWrite->handle(
                new CreateProductCommand(
                    $this->userWeb->getId(),
                    $request->name,
                    $request->description,
                    $request->price,
                    $request->iva
                )
            );

            /** @var DetalleProduct $product */
            return FormResponseDto::formSuccess(
                self::TIPO_FORM,
                'Se ha creado el producto',
                [
                    'product_id' => $product->id->asString(),
                ],
            );
        } catch (\Throwable $e) {
            // Mismo criterio que en SignUpController: el mensaje de la excepción
            // sólo sale al cliente si está escrito para él. Antes salía siempre,
            // de modo que una DriverException de Doctrine llegaba al cuerpo del
            // 400 con la consulta y el DSN dentro. Aquí hace falta estar
            // autenticado, así que el alcance es menor que en el registro, pero
            // describir la infraestructura a cualquier usuario con cuenta sigue
            // sin ser algo que esta respuesta deba hacer.
            if (! $this->esMensajeParaElUsuario($e)) {
                $this->logger->error(
                    'Fallo inesperado al crear un producto',
                    ['exception' => $e]
                );

                $this->errorMessage = self::ERROR_GENERICO;
                $this->errors = null;

                return null;
            }

            $this->errorMessage = 'No se ha podido crear el producto';
            $this->errors = FormErrorDtoCollection::fromElements(
                [
                    FormErrorDto::create(
                        'error',
                        $e->getMessage()
                    ),
                ]
            );

            if ($e instanceof UserWebNotFound) {
                $this->errorMessage = 'El usuario no existe';
            }

            return null;
        }
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
}