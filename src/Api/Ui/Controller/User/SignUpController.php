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
        ValidatorInterface $validator
    ): JsonResponse
    {
        $this->commandBusWrite = $commandBusWrite;
        $this->commandBusRead = $commandBusRead;
        $this->validator = $validator;
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