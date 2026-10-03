<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common;

use Api\Domain\Collection\Common\FormErrorDtoCollection;
use Api\Ui\Controller\Product\CreateProductController;
use Api\Ui\Controller\User\SignUpController;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\RequestBody;
use ArrayObject;

/**
 * Recurso de API Platform usado únicamente para documentar en OpenAPI los dos
 * formularios de la API. Las peticiones las atienden controladores propios
 * (`routeName`), por eso `read: false` e `input: false`: API Platform no lee ni
 * deserializa nada, solo describe la ruta.
 *
 * Los `operationId` se fijan a los que generaba API Platform 2.7 para que el
 * contrato OpenAPI no cambie con la migración.
 */
#[ApiResource(
    description: 'Devuelve la respuesta de un formulario',
    operations: [
        new Post(
            name: 'signup',
            routeName: 'api_signup',
            read: false,
            input: false,
            openapi: new Operation(
                operationId: 'signupFormResponseDtoItem',
                tags: ['Users'],
                parameters: [
                    new Parameter(
                        name: 'tipoForm',
                        in: 'path',
                        description: 'Tipo de formulario que se envía',
                        required: true,
                        schema: ['type' => 'string'],
                        examples: new ArrayObject([
                            SignUpController::TIPO_FORM => [
                                'summary' => 'Crear usuario',
                                'value' => SignUpController::TIPO_FORM,
                                'description' => 'Permite crear un usuario para la autenticación de la API',
                            ],
                        ]),
                    ),
                ],
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        SignUpController::TIPO_FORM => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'nombre' => ['type' => 'string'],
                                    'email' => ['type' => 'string'],
                                    'password' => ['type' => 'string'],
                                    'passwordRepeat' => ['type' => 'string'],
                                ],
                            ],
                            'example' => [
                                'nombre' => 'Mr. Rabbit',
                                'email' => 'mr_rabbit_69@gmail.es',
                                'password' => '12345678',
                                'passwordRepeat' => '12345678',
                            ],
                        ],
                    ]),
                    required: true,
                ),
                security: [],
            ),
        ),
        new Post(
            name: 'product_form',
            routeName: 'api_create_product_form',
            read: false,
            input: false,
            openapi: new Operation(
                operationId: 'product_formFormResponseDtoItem',
                tags: ['Productos'],
                parameters: [
                    new Parameter(
                        name: 'tipoForm',
                        in: 'path',
                        description: 'Tipo de formulario que se envía',
                        required: true,
                        schema: ['type' => 'string'],
                        examples: new ArrayObject([
                            CreateProductController::TIPO_FORM => [
                                'summary' => 'Crear producto',
                                'value' => CreateProductController::TIPO_FORM,
                                'description' => 'Permite a un usuario autenticado crear un nuevo producto',
                            ],
                        ]),
                    ),
                ],
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        CreateProductController::TIPO_FORM => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'price' => ['type' => 'string'],
                                    'iva' => ['type' => 'integer'],
                                ],
                            ],
                            'example' => [
                                'name' => 'Chicles Trident',
                                'description' => 'Paquete de 10 chicles',
                                'price' => '1,75',
                                'iva' => 21,
                            ],
                        ],
                    ]),
                    required: true,
                ),
            ),
        ),
    ],
)]
final class FormResponseDto
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $tipoForm,
        public bool $success,
        public string $message,
        public ?FormErrorDtoCollection $errors = null,
        public array $extraData = []
    ) {
    }

    public static function formSuccess(string $tipoForm, string $message, array $extraData = []): self
    {
        return new self(
            $tipoForm,
            true,
            $message,
            extraData: $extraData
        );
    }

    public static function formFail(string $tipoForm, string $message, ?FormErrorDtoCollection $errors = null, array $extraData = []): self
    {
        return new self(
            $tipoForm,
            false,
            $message,
            $errors,
            $extraData
        );
    }

    public function toArray(): array
    {
        return [
            'tipoForm' => $this->tipoForm,
            'success' => $this->success,
            'message' => $this->message,
            'errors' => $this->errors?->toArray(),
            'extraData' => $this->extraData,
        ];
    }
}
