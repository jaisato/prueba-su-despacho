<?php

declare(strict_types=1);

namespace Api\Infrastructure\Service\Paginacion;

use Api\Domain\Dto\Common\Paginacion\PaginacionCargarMasDto;
use Api\Domain\Dto\Common\Paginacion\PaginacionNumeradaDto;
use Api\Domain\Dto\Common\PaginacionDto;
//use ApiPlatform\Api\UrlGeneratorInterface;
use App\Domain\ValueObject\Quantity;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function ceil;

final class PaginacionService implements \Api\Domain\Service\Paginacion\PaginacionService
{
    public function __construct(
        public UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function crearNumerada(
        Quantity $elementosTotalFiltrado,
        Quantity $elementosTotal,
        int $elementosPorPagina,
        int $paginaActual,
        string $url,
        array $urlParams = []
    ): ?PaginacionDto {
        return PaginacionNumeradaDto::fromData(
            $this->urlGenerator,
            $elementosTotalFiltrado,
            $elementosTotal,
            $elementosPorPagina,
            $paginaActual,
            $url,
            $urlParams
        );
    }

    public function crearCargarMas(
        Quantity $elementosTotal,
        int $elementosPorPagina,
        int $paginaActual,
        string $url,
        array $urlParams = []
    ): ?PaginacionDto {
        return PaginacionCargarMasDto::fromData(
            $this->urlGenerator,
            $elementosTotal,
            $elementosPorPagina,
            $paginaActual,
            $url,
            $urlParams
        );
    }

    public static function getNumeroDePaginasFromLimitAndElementosTotales(
        int $limit,
        int $elementosTotal
    ): int {
        return (int) ceil($elementosTotal / $limit);
    }
}
