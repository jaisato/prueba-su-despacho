<?php

declare(strict_types=1);

namespace Api\Infrastructure\Service\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;

use function array_values;
use function assert;
use function preg_match;
use function str_replace;
use function strtolower;
use function ucfirst;

final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $paths   = $openApi->getPaths()->getPaths();

        $filteredPaths = new Model\Paths();
        foreach ($paths as $path => $pathItem) {
            assert($pathItem instanceof Model\PathItem);
            if ($pathItem->getGet() && $pathItem->getGet()->getSummary() === 'hidden') {
                continue;
            }

            foreach (PathItem::$methods as $method) {
                $getter = 'get' . ucfirst(strtolower($method));
                $setter = 'with' . ucfirst(strtolower($method));

                $operation = $pathItem->$getter();
                if (! $operation) {
                    continue;
                }

                assert($operation instanceof Operation);

                $parameters = $operation->getParameters();
                foreach ($parameters as $i => $parameter) {
                    if (
                        preg_match('/identifier/i', $parameter->getDescription())
                        && preg_match('/#withoutIdentifier/', $operation->getSummary())
                    ) {
                        unset($parameters[$i]);
                        break;
                    }
                }

                $summary = str_replace('#withoutIdentifier', '', $operation->getSummary());

                $filteredPaths->addPath($path, $pathItem = $pathItem->$setter($operation->withSummary($summary)->withParameters(array_values($parameters))));
            }
        }

        return $openApi->withPaths($filteredPaths);
    }
}
