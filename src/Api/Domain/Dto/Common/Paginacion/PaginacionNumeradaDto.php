<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common\Paginacion;

use Api\Domain\Dto\Common\Paginacion\PaginacionNumerada\PaginacionNumeradaItemDto;
use Api\Domain\Dto\Common\PaginacionDto;
use Api\Infrastructure\Service\Paginacion\PaginacionService;
use App\Domain\ValueObject\Quantity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function min;
use function round;

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

        [$indiceInicio, $indiceFinal] = self::indices(
            $elementosPorPagina,
            $paginaActual,
            $elementosTotalFiltrado->asInt()
        );

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

    /**
     * Posiciones (empezando en 1) del primer y del último elemento de la
     * página, para el texto "Mostrando del X al Y de Z".
     *
     * Antes el inicio era el desplazamiento (0, 10, 20...) con el 0 cambiado
     * por 1, y el final, página × tamaño sin más: la página 2 de 10 en 10 decía
     * "del 10 al 20", y la última página o una lista corta anunciaban
     * elementos que no existen ("del 1 al 10" de 3). Una página más allá del
     * final no muestra ninguno: 0 y 0.
     *
     * @return array{int, int}
     */
    private static function indices(int $elementosPorPagina, int $paginaActual, int $totalFiltrado): array
    {
        $desplazamiento = $elementosPorPagina * ($paginaActual - 1);

        if ($desplazamiento >= $totalFiltrado) {
            return [0, 0];
        }

        return [$desplazamiento + 1, min($desplazamiento + $elementosPorPagina, $totalFiltrado)];
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
