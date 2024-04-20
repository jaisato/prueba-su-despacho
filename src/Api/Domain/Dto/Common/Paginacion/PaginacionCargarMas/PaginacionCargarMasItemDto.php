<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common\Paginacion\PaginacionCargarMas;

final class PaginacionCargarMasItemDto
{
    public function __construct(
        public int $pagina,
        public string $url
    ) {
    }
}
