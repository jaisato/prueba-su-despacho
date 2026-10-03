<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common;

use Api\Domain\Collection\Common\ProductDtoCollection;
use Api\Domain\Dto\Common\Paginacion\PaginacionNumeradaDto;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\NotExposed;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;

/**
 * Recurso de API Platform para el listado paginado de productos. La petición
 * la atiende un controlador propio (`routeName`, `read: false`) que devuelve
 * este DTO; API Platform lo serializa y lo documenta en OpenAPI.
 *
 * Al serializar un recurso, API Platform genera siempre su IRI, y la ruta del
 * listado (`/productos/{pagina}/{resultadosPorPagina}`) no se puede construir
 * solo con el identificador. Para eso existe la operación `NotExposed`: es la
 * heredera de la operación `get` "oculta" que este recurso declaraba con API
 * Platform 2.7 (misma ruta y mismo nombre), responde 404 y no aparece en
 * OpenAPI; `item_uri_template` le dice al listado que el IRI sale de ella.
 *
 * El `operationId` se fija al que generaba la 2.7 para que el contrato OpenAPI
 * no cambie con la migración.
 */
#[ApiResource(
    description: 'Devuelve los datos de los productos paginados',
    operations: [
        new NotExposed(name: self::IRI_OPERATION),
        new Get(
            name: 'products_list',
            routeName: 'api_products_list',
            read: false,
            normalizationContext: ['item_uri_template' => self::IRI_OPERATION],
            openapi: new Operation(
                operationId: 'products_listProductsPaginatedDtoItem',
                tags: ['Productos'],
                parameters: [
                    new Parameter(
                        name: 'pagina',
                        in: 'path',
                        description: 'Página actual',
                        required: true,
                        schema: ['type' => 'number'],
                        example: 1,
                    ),
                    new Parameter(
                        name: 'resultadosPorPagina',
                        in: 'path',
                        description: 'Número de resultados por página',
                        required: true,
                        schema: ['type' => 'number'],
                        example: 10,
                    ),
                    new Parameter(
                        name: 'orden',
                        in: 'query',
                        description: 'Orden de los resultados (fechaCreacion_desc | fechaCreacion_asc)',
                        required: false,
                        schema: ['type' => 'string'],
                        example: 'fechaCreacion_desc',
                    ),
                    new Parameter(
                        name: 'name',
                        in: 'query',
                        description: 'Filtrar por nombre de producto',
                        required: false,
                        schema: ['type' => 'string'],
                        example: 'Trident',
                    ),
                ],
                security: [],
            ),
        ),
    ],
)]
class ProductsPaginatedDto
{
    /** Nombre de la operación (y de la ruta) con la que se genera el IRI del recurso. */
    public const IRI_OPERATION = 'api_products_paginated_dtos_get_item';

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
