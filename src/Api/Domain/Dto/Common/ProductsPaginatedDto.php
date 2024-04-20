<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common;

use Api\Domain\Collection\Common\ProductDtoCollection;
use Api\Domain\Dto\Common\Paginacion\PaginacionNumeradaDto;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

#[ApiResource(
    description: 'Devuelve los datos de los productos paginados',
    collectionOperations: [],
    itemOperations: [
        'get' => ['openapi_context' => ['summary' => 'hidden']],
        'products_list' => [
            'route_name' => 'api_products_list',
            'read' => false,
            'openapi_context' => [
                'tags' => ['Productos'],
                'security' => [],
                'parameters' => [
                    [
                        'required' => true,
                        'name' => 'pagina',
                        'in' => 'path',
                        'description' => 'Página actual',
                        'schema' => ['type' => 'number'],
                        'example' => 1,
                    ],
                    [
                        'required' => true,
                        'name' => 'resultadosPorPagina',
                        'in' => 'path',
                        'description' => 'Número de resultados por página',
                        'schema' => ['type' => 'number'],
                        'example' => 10,
                    ],
                    [
                        'required' => false,
                        'name' => 'orden',
                        'in' => 'query',
                        'description' => 'Orden de los resultados (fechaCreacion_desc | fechaCreacion_asc)',
                        'schema' => ['type' => 'string'],
                        'example' => 'fechaCreacion_desc' ,
                    ],
                    [
                        'required' => false,
                        'name' => 'name',
                        'in' => 'query',
                        'description' => 'Filtrar por nombre de producto',
                        'schema' => ['type' => 'string'],
                        'example' => 'Trident' ,
                    ],
                ],
            ],
        ],
    ],
)]
class ProductsPaginatedDto
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int                    $pagina,
        public ?PaginacionNumeradaDto $paginacion,
        public ProductDtoCollection   $products,
    ) {
    }

    public static function fromResults(
        ?PaginacionNumeradaDto $paginacion,
        ProductDtoCollection $products
    ): self {
        return new self(
            $paginacion ? $paginacion->paginaActual : 1,
            $paginacion,
            $products
        );
    }

    public static function createEmpty(): self
    {
        return new self(
            1,
            null,
            ProductDtoCollection::createEmpty(),
        );
    }
}
