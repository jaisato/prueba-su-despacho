<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common\Paginacion\PaginacionNumerada;

final class PaginacionNumeradaItemDto
{
    public function __construct(
        public int $pagina,
        public string $url,
        public bool $activa
    ) {
    }
}
