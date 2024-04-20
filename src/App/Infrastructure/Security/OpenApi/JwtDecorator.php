<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model;
use ApiPlatform\Core\OpenApi\OpenApi;
use ArrayObject;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function rtrim;
use function sprintf;

final class JwtDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token']       = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);
        $schemas['Credentials'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'example' => 'web@test.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => '123456789',
                ],
            ],
        ]);

        $schemas             = $openApi->getComponents()->getSecuritySchemes() ?? [];
        $schemas['user_web'] = new ArrayObject([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'user_web',
        ]);

        $pathItem = new Model\PathItem(
            ref: 'JWT Token',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Login'],
                responses: [
                    '200' => [
                        'description' => 'Get JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/Token'],
                            ],
                        ],
                    ],
                ],
                summary: 'Get JWT token to login.',
                requestBody: new Model\RequestBody(
                    description: 'Generate new JWT Token',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/Credentials'],
                        ],
                    ]),
                ),
                security: [],
            ),
        );

        $openApi->getPaths()->addPath(
            sprintf(
                '%s%s',
                rtrim($this->parameterBag->get('api_route_prefix'), '/'),
                '/login'
            ),
            $pathItem,
        );

        return $openApi;
    }
}
