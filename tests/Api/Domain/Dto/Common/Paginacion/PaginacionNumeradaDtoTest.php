<?php

declare(strict_types=1);

namespace Tests\Api\Domain\Dto\Common\Paginacion;

use Api\Domain\Dto\Common\Paginacion\PaginacionNumeradaDto;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

use function http_build_query;

/**
 * @group paginacion
 */
final class PaginacionNumeradaDtoTest extends TestCase
{
    /**
     * @dataProvider rangos
     */
    public function testIndicesSonLasPosicionesDelPrimerYUltimoElementoDeLaPagina(
        int $total,
        int $porPagina,
        int $pagina,
        int $inicio,
        int $final
    ): void {
        $dto = PaginacionNumeradaDto::fromData(
            self::urlGenerator(),
            Quantity::fromInt($total),
            Quantity::fromInt($total),
            $porPagina,
            $pagina,
            'api_products_list',
            ['pagina' => $pagina, 'resultadosPorPagina' => $porPagina]
        );

        self::assertSame($inicio, $dto->indiceInicio, 'indiceInicio');
        self::assertSame($final, $dto->indiceFinal, 'indiceFinal');
    }

    /**
     * @return iterable<string, array{int, int, int, int, int}>
     */
    public static function rangos(): iterable
    {
        yield 'primera página' => [23, 10, 1, 1, 10];
        yield 'página intermedia' => [23, 10, 2, 11, 20];
        yield 'última página incompleta' => [23, 10, 3, 21, 23];
        yield 'una sola página incompleta' => [7, 10, 1, 1, 7];
        yield 'página más allá de la última' => [23, 10, 9, 0, 0];
    }

    public function testLosEnlacesConservanLosParametrosDeLaConsulta(): void
    {
        $dto = PaginacionNumeradaDto::fromData(
            self::urlGenerator(),
            Quantity::fromInt(23),
            Quantity::fromInt(23),
            10,
            2,
            'api_products_list',
            ['pagina' => 2, 'resultadosPorPagina' => 10, 'name' => 'mesa', 'orden' => 'fechaCreacion_asc']
        );

        self::assertNotNull($dto->siguiente);
        self::assertSame(
            'api_products_list?pagina=3&resultadosPorPagina=10&name=mesa&orden=fechaCreacion_asc',
            $dto->siguiente->url
        );
    }

    /**
     * Un generador de URL que devuelve la ruta y los parámetros tal cual, para
     * poder comprobar qué parámetros recibe sin arrancar el router.
     */
    public static function urlGenerator(): UrlGeneratorInterface
    {
        return new class implements UrlGeneratorInterface {
            private RequestContext $context;

            public function __construct()
            {
                $this->context = new RequestContext();
            }

            public function setContext(RequestContext $context): void
            {
                $this->context = $context;
            }

            public function getContext(): RequestContext
            {
                return $this->context;
            }

            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return $name . '?' . http_build_query($parameters);
            }
        };
    }
}
