<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common;

use Api\Domain\Collection\Common\FormErrorDtoCollection;
use Api\Ui\Controller\Product\CreateProductController;
use Api\Ui\Controller\User\SignUpController;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

#[ApiResource(
    description: 'Devuelve la respuesta de un formulario',
    collectionOperations: [],
    itemOperations: [
        'get' => ['openapi_context' => ['summary' => 'hidden']],
        'signup' => [
            'method' => 'POST',
            'route_name' => 'api_signup',
            'read' => false,
            'input' => false,
            'openapi_context' => [
                'tags' => ['Users'],
                'security' => [],
                'parameters' => [
                    [
                        'required' => true,
                        'name' => 'tipoForm',
                        'in' => 'path',
                        'description' => 'Tipo de formulario que se envía',
                        'schema' => ['type' => 'string'],
                        'examples' => [
                            SignUpController::TIPO_FORM => [
                                'summary' => 'Crear usuario',
                                'value' => SignUpController::TIPO_FORM,
                                'description' => 'Permite crear un usuario para la autenticación de la API',
                            ],
                        ],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
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
                    ],
                ],
            ],
        ],
        'product_form' => [
            'method' => 'POST',
            'route_name' => 'api_create_product_form',
            'read' => false,
            'input' => false,
            'openapi_context' => [
                'tags' => ['Productos'],
                'parameters' => [
                    [
                        'required' => true,
                        'name' => 'tipoForm',
                        'in' => 'path',
                        'description' => 'Tipo de formulario que se envía',
                        'schema' => ['type' => 'string'],
                        'examples' => [
                            CreateProductController::TIPO_FORM => [
                                'summary' => 'Crear producto',
                                'value' => CreateProductController::TIPO_FORM,
                                'description' => 'Permite a un usuario autenticado crear un nuevo producto',
                            ],
                        ],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
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
                    ],
                ],
            ],
        ],
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
