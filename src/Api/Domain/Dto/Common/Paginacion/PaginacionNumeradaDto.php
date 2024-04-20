<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common\Paginacion;

use Api\Domain\Dto\Common\Paginacion\PaginacionNumerada\PaginacionNumeradaItemDto;
use Api\Domain\Dto\Common\PaginacionDto;
use Api\Infrastructure\Service\Paginacion\PaginacionService;
use App\Domain\ValueObject\Quantity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        $indiceFinal  = $elementosPorPagina * $paginaActual;
        $indiceInicio = $indiceFinal - $elementosPorPagina;

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
                $indiceInicio > 0 ? $indiceInicio : 1, // Cambia 0 por 1
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
            $indiceInicio > 0 ? $indiceInicio : 1, // Cambia 0 por 1
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

        var_dump($url);
        var_dump($urlParams);
        var_dump($urlGenerator->generate(
            $url,
            $urlParams
        ));

        return $urlGenerator->generate(
            $url,
            $urlParams
        );
    }
}
