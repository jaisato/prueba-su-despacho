<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common\Paginacion;

use Api\Domain\Dto\Common\Paginacion\PaginacionNumerada\PaginacionNumeradaItemDto;
use Api\Domain\Dto\Common\PaginacionDto;
use Api\Infrastructure\Service\Paginacion\PaginacionService;
use App\Domain\ValueObject\Quantity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function min;

final class PaginacionNumeradaDto extends PaginacionDto
{
    public int $paginaActual;

    private function __construct(
        public ?PaginacionNumeradaItemDto $anterior,
        public ?PaginacionNumeradaItemDto $siguiente,
        public array $paginas,
        // El total de elementos sin filtrar y filtrados, junto con el índice de inicio y final,
        // sirven para generar el texto "Mostrando del 1 al 20 de 2345 elementos"
        public int $total,
        public int $totalFiltrado,
        public int $indiceInicio,
        public int $indiceFinal,
    ) {
    }

    public static function fromData(
        UrlGeneratorInterface $urlGenerator,
        Quantity $elementosTotalFiltrado,
        Quantity $elementosTotal,
        int $elementosPorPagina,
        int $paginaActual,
        string $url,
        array $urlParams = []
    ): ?self {
        $paginaAnterior  = null;
        $paginaSiguiente = null;

        // Posiciones 1-based del primer y el último elemento de la página, para
        // el texto "Mostrando del X al Y de Z". Antes el inicio era el offset
        // (0-based: la página 2 de 10 en 10 decía "del 10 al 20", y sólo la
        // primera se corregía de 0 a 1) y el final era siempre
        // pagina * porPagina, también en la última página: 23 elementos de 10
        // en 10 acababan en "del 20 al 30 de 23".
        $offset       = $elementosPorPagina * ($paginaActual - 1);
        $indiceInicio = $offset + 1;
        $indiceFinal  = min($offset + $elementosPorPagina, $elementosTotalFiltrado->asInt());

        // Una página más allá de la última no muestra nada: 0 y 0, en lugar de
        // un rango invertido ("del 31 al 23").
        if ($indiceInicio > $indiceFinal) {
            $indiceInicio = 0;
            $indiceFinal  = 0;
        }

        if ($paginaActual > 1) {
            $paginaAnterior = new PaginacionNumeradaItemDto(
                $paginaActual - 1,
                self::buildPageUrl(
                    $urlGenerator,
                    $paginaActual - 1,
                    $url,
                    $urlParams
                ),
                false
            );
        }

        $numeroPaginas = PaginacionService::getNumeroDePaginasFromLimitAndElementosTotales(
            $elementosPorPagina,
            $elementosTotalFiltrado->asInt()
        );

        if ($numeroPaginas === 1) {
            $dto = new self(
                $paginaAnterior,
                $paginaSiguiente,
                [],
                $elementosTotal->asInt(),
                $elementosTotalFiltrado->asInt(),
                $indiceInicio,
                $indiceFinal
            );

            $dto->paginaActual = $paginaActual;

            return $dto;
        }

        if ($numeroPaginas > $paginaActual) {
            $paginaSiguiente = new PaginacionNumeradaItemDto(
                $paginaActual + 1,
                self::buildPageUrl(
                    $urlGenerator,
                    $paginaActual + 1,
                    $url,
                    $urlParams
                ),
                false
            );
        }

        $paginas = [];
        for ($i = 1; $i <= $numeroPaginas; $i++) {
            $paginas[] = new PaginacionNumeradaItemDto(
                $i,
                self::buildPageUrl(
                    $urlGenerator,
                    $i,
                    $url,
                    $urlParams
                ),
                $i === $paginaActual
            );
        }

        $dto = new self(
            $paginaAnterior,
            $paginaSiguiente,
            $paginas,
            $elementosTotal->asInt(),
            $elementosTotalFiltrado->asInt(),
            $indiceInicio,
            $indiceFinal
        );

        $dto->paginaActual = $paginaActual;

        return $dto;
    }

    public static function buildPageUrl(
        UrlGeneratorInterface $urlGenerator,
        int $pagina,
        string $url,
        array $urlParams
    ): string {
        $urlParams['pagina'] = $pagina;

        return $urlGenerator->generate(
            $url,
            $urlParams
        );
    }
}
