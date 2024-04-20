<?php

declare(strict_types=1);

namespace Api\Application\Query\Product;

final class GetProductsQuery
{
    public function __construct(
        public readonly int $pagina,
        public readonly int $resultadosPorPagina,
        public readonly array $filters,
        public readonly ?string $orden,
    ) {
    }
}
