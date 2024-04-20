<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common\Paginacion;

use Api\Domain\Dto\Common\Paginacion\PaginacionCargarMas\PaginacionCargarMasItemDto;
use Api\Domain\Dto\Common\PaginacionDto;
use Api\Infrastructure\Service\Paginacion\PaginacionService;
use App\Domain\ValueObject\Quantity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function array_key_exists;

final class PaginacionCargarMasDto extends PaginacionDto
{
    public int $paginaActual = 0;

    public function __construct(
        public PaginacionCargarMasItemDto $siguiente
    ) {
    }

    public static function fromData(
        UrlGeneratorInterface $urlGenerator,
        Quantity $elementosTotal,
        int $elementosPorPagina,
        int $paginaActual,
        string $url,
        array $urlParams = []
    ): ?self {
        if ($elementosTotal->asInt() <= $elementosPorPagina) {
            return null;
        }

        $numeroPaginas = PaginacionService::getNumeroDePaginasFromLimitAndElementosTotales(
            $elementosPorPagina,
            $elementosTotal->asInt()
        );
        if ($numeroPaginas <= $paginaActual) {
            return null;
        }

        $model = new self(
            new PaginacionCargarMasItemDto(
                $paginaActual + 1,
                self::buildNextPageUrl(
                    $urlGenerator,
                    $url,
                    $urlParams
                )
            )
        );

        $model->paginaActual = $paginaActual;

        return $model;
    }

    public static function buildNextPageUrl(
        UrlGeneratorInterface $urlGenerator,
        string $url,
        array $urlParams
    ): string {
        if (array_key_exists('pagina', $urlParams)) {
            $urlParams['pagina']++;
        }

        return $urlGenerator->generate(
            $url,
            $urlParams
        );
    }
}
