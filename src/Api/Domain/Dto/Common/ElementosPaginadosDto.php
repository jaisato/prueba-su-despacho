<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common;

final class ElementosPaginadosDto
{
    private function __construct(
        public readonly ?PaginacionDto $paginacion,
        public readonly array $elementos
    ) {
    }

    public static function create(
        ?PaginacionDto $paginacion,
        array $elementos
    ): self {
        return new self(
            $paginacion,
            $elementos
        );
    }
}
