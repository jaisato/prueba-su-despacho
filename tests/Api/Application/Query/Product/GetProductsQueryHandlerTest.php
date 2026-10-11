<?php

declare(strict_types=1);

namespace Tests\Api\Application\Query\Product;

use Api\Application\Query\Product\GetProductsQuery;
use Api\Application\Query\Product\GetProductsQueryHandler;
use Api\Infrastructure\Service\Paginacion\PaginacionService;
use App\Domain\Repository\Doctrine\Product\ProductReadRepository;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;
use Tests\Api\Domain\Dto\Common\Paginacion\PaginacionNumeradaDtoTest;

/**
 * @group listProducts
 */
final class GetProductsQueryHandlerTest extends TestCase
{
    /**
     * El total que acompaña a los enlaces es el filtrado, así que los enlaces
     * tienen que llevar el mismo filtro: antes "siguiente" llevaba a la página
     * 2 del listado completo.
     */
    public function testLosEnlacesDePaginacionConservanElFiltroPorNombre(): void
    {
        $repository = $this->createMock(ProductReadRepository::class);
        $repository->method('countAll')->with(['name' => 'mesa'])->willReturn(Quantity::fromInt(23));
        $repository->method('all')->willReturn([]);

        $handler = new GetProductsQueryHandler(
            $repository,
            new PaginacionService(PaginacionNumeradaDtoTest::urlGenerator())
        );

        $result = $handler(new GetProductsQuery(1, 10, ['name' => 'mesa'], 'fechaCreacion_asc'));

        self::assertNotNull($result->paginacion);
        self::assertNotNull($result->paginacion->siguiente);
        self::assertSame(
            'api_products_list?pagina=2&resultadosPorPagina=10&orden=fechaCreacion_asc&name=mesa',
            $result->paginacion->siguiente->url
        );

        foreach ($result->paginacion->paginas as $pagina) {
            self::assertStringContainsString('name=mesa', $pagina->url);
        }
    }

    public function testSinFiltroLosEnlacesNoInventanUno(): void
    {
        $repository = $this->createMock(ProductReadRepository::class);
        $repository->method('countAll')->willReturn(Quantity::fromInt(23));
        $repository->method('all')->willReturn([]);

        $handler = new GetProductsQueryHandler(
            $repository,
            new PaginacionService(PaginacionNumeradaDtoTest::urlGenerator())
        );

        $result = $handler(new GetProductsQuery(1, 10, [], null));

        self::assertNotNull($result->paginacion);
        self::assertSame(
            'api_products_list?pagina=2&resultadosPorPagina=10',
            $result->paginacion->siguiente->url
        );
    }
}
