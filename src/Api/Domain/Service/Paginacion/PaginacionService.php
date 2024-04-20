<?php

declare(strict_types=1);

namespace Api\Domain\Service\Paginacion;

use Api\Domain\Dto\Common\PaginacionDto;
use App\Domain\ValueObject\Quantity;

interface PaginacionService
{
    public function crearNumerada(
        Quantity $elementosTotalFiltrado,
        Quantity $elementosTotal,
        int $elementosPorPagina,
        int $paginaActual,
        string $url,
        array $urlParams = []
    ): ?PaginacionDto;

    public function crearCargarMas(
        Quantity $elementosTotal,
        int $elementosPorPagina,
        int $paginaActual,
        string $url,
        array $urlParams = []
    ): ?PaginacionDto;

    public static function getNumeroDePaginasFromLimitAndElementosTotales(
        int $limit,
        int $elementosTotal
    ): int;
}
