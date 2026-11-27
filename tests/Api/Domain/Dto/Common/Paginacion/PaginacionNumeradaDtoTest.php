<?php

declare(strict_types=1);

namespace Tests\Api\Domain\Dto\Common\Paginacion;

use Api\Domain\Dto\Common\Paginacion\PaginacionNumeradaDto;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * `indiceInicio` e `indiceFinal` son el primer y el último elemento de la
 * página, contados desde 1, para el texto "Mostrando del 21 al 25 de 25".
 * Se calculaban como (página - 1) * tamaño y página * tamaño: la página 2 de
 * 10 en 10 empezaba en el 10, y la última página (o la única) terminaba en un
 * múltiplo del tamaño aunque hubiera menos elementos.
 */
final class PaginacionNumeradaDtoTest extends TestCase
{
    /**
     * @dataProvider paginas
     */
    public function testLosIndicesSonElPrimerYElUltimoElementoDeLaPagina(
        int $total,
        int $porPagina,
        int $pagina,
        int $inicio,
        int $final
    ): void {
        $dto = PaginacionNumeradaDto::fromData(
            $this->urlGenerator(),
            Quantity::fromInt($total),
            Quantity::fromInt($total),
            $porPagina,
            $pagina,
            'api_products_list',
            ['pagina' => $pagina, 'resultadosPorPagina' => $porPagina]
        );

        self::assertNotNull($dto);
        self::assertSame($inicio, $dto->indiceInicio);
        self::assertSame($final, $dto->indiceFinal);
    }

    /**
     * @return iterable<string, array{int, int, int, int, int}>
     */
    public static function paginas(): iterable
    {
        yield 'una sola página, incompleta' => [3, 10, 1, 1, 3];
        yield 'una sola página, completa' => [10, 10, 1, 1, 10];
        yield 'primera de varias' => [25, 10, 1, 1, 10];
        yield 'una del medio' => [25, 10, 2, 11, 20];
        yield 'la última, incompleta' => [25, 10, 3, 21, 25];
        yield 'más allá de la última' => [25, 10, 4, 0, 0];
    }

    public function testLosEnlacesLlevanLosParametrosDelListado(): void
    {
        $dto = PaginacionNumeradaDto::fromData(
            $this->urlGenerator(),
            Quantity::fromInt(25),
            Quantity::fromInt(25),
            10,
            2,
            'api_products_list',
            ['pagina' => 2, 'resultadosPorPagina' => 10, 'name' => 'mesa', 'orden' => 'fechaCreacion_asc']
        );

        self::assertNotNull($dto);
        self::assertSame('/api/productos/1/10?name=mesa&orden=fechaCreacion_asc', $dto->anterior?->url);
        self::assertSame('/api/productos/3/10?name=mesa&orden=fechaCreacion_asc', $dto->siguiente?->url);
    }

    private function urlGenerator(): UrlGenerator
    {
        $routes = new RouteCollection();
        $routes->add('api_products_list', new Route('/api/productos/{pagina}/{resultadosPorPagina}'));

        return new UrlGenerator($routes, new RequestContext());
    }
}
