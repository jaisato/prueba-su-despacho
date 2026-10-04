<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\Product;

use Api\Application\Command\Product\CreateProductCommand;
use App\Domain\CommandBus\CommandBusWrite;
use App\Domain\Model\User\UserWeb;
use App\Infrastructure\Factory\User\UserWebFactory;
use Tests\Api\Ui\Controller\ControllerTest;

use function bin2hex;
use function json_decode;
use function parse_str;
use function parse_url;
use function random_bytes;
use function sprintf;
use function urlencode;

use const JSON_THROW_ON_ERROR;
use const PHP_URL_QUERY;

/**
 * @group listProducts
 */
class ListProductsControllerTest extends ControllerTest
{
    public function setUp(): void
    {
        parent::initClient();
    }

    /**
     * El controlador devuelve el DTO tal cual y es API Platform quien lo
     * serializa. Nada lo comprobaba: la suite seguía en verde con el listado
     * respondiendo 500, que es exactamente lo que pasó a mitad de la migración
     * de API Platform 2.7 a 4.x. Las claves a null forman parte del contrato
     * (la 3.0 pasó a omitirlas por defecto), así que se compara el cuerpo
     * entero. El filtro no casa con ningún producto para no depender de lo que
     * otros tests hayan dejado en la base de datos.
     */
    public function testListIsSerializedAsJsonKeepingNullKeys(): void
    {
        $this->client->request('GET', '/api/productos/1/10?name=no-existe-' . bin2hex(random_bytes(8)));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        self::assertSame(
            ['pagina' => 1, 'paginacion' => null, 'products' => []],
            json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)
        );
    }

    /**
     * "Mostrando del X al Y de Z": the first position used to be the offset
     * (page 2 of 2 started at 2, not 3) and the last one page × size, past the
     * end of a short last page. The links to the other pages also dropped the
     * name filter, so following them listed every product.
     */
    public function testPaginationCountsFromOneStopsAtTheTotalAndKeepsTheFilter(): void
    {
        $marca = $this->createProducts(3);

        $primera = $this->list(1, 2, $marca);
        self::assertCount(2, $primera['products']);
        self::assertSame(3, $primera['paginacion']['totalFiltrado']);
        self::assertSame([1, 2], [$primera['paginacion']['indiceInicio'], $primera['paginacion']['indiceFinal']]);

        parse_str((string) parse_url($primera['paginacion']['siguiente']['url'], PHP_URL_QUERY), $query);
        self::assertSame($marca, $query['name'] ?? null);

        $segunda = $this->list(2, 2, $marca);
        self::assertCount(1, $segunda['products']);
        self::assertSame([3, 3], [$segunda['paginacion']['indiceInicio'], $segunda['paginacion']['indiceFinal']]);

        $vacia = $this->list(3, 2, $marca);
        self::assertSame([], $vacia['products']);
        self::assertSame([0, 0], [$vacia['paginacion']['indiceInicio'], $vacia['paginacion']['indiceFinal']]);
    }

    /**
     * The name is searched for literally: `_` and `%` used to be LIKE
     * wildcards, so "<marca>_" matched "<marca> 1" and `%` matched everything.
     */
    public function testTheNameFilterIsNotALikePattern(): void
    {
        $marca = $this->createProducts(1);

        self::assertCount(1, $this->list(1, 10, $marca)['products']);
        self::assertSame([], $this->list(1, 10, $marca . '_')['products']);
        self::assertSame([], $this->list(1, 10, $marca . '%')['products']);
    }

    /**
     * Page and page size must be positive integers. Anything else used to reach
     * the controller and come back as a 500 (a TypeError for a non-numeric
     * segment, LimitIsNotValid for zero); now the route simply does not match.
     *
     * @dataProvider invalidPagination
     */
    public function testInvalidPaginationDoesNotMatchTheRoute(string $pagina, string $resultadosPorPagina): void
    {
        $this->client->request('GET', sprintf('/api/productos/%s/%s', $pagina, $resultadosPorPagina));

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Creates $count products whose names share a fresh marker and returns it.
     */
    private function createProducts(int $count): string
    {
        $marca = 'lista-' . bin2hex(random_bytes(6));
        $user  = UserWebFactory::createOne()->object();
        \assert($user instanceof UserWeb);

        $commandBus = static::getContainer()->get(CommandBusWrite::class);
        for ($i = 1; $i <= $count; $i++) {
            $commandBus->handle(new CreateProductCommand($user->id()->asString(), $marca . ' ' . $i, 'Descripción', '1,75', 21));
        }

        return $marca;
    }

    /**
     * @return array<string, mixed>
     */
    private function list(int $pagina, int $porPagina, string $name): array
    {
        $this->client->request('GET', sprintf('/api/productos/%d/%d?name=%s', $pagina, $porPagina, urlencode($name)));

        $this->assertResponseIsSuccessful();

        return json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidPagination(): iterable
    {
        yield 'page zero' => ['0', '10'];
        yield 'page size zero' => ['1', '0'];
        yield 'negative page' => ['-1', '10'];
        yield 'non-numeric page' => ['abc', '10'];
        yield 'page past the bound' => ['99999999999999999999', '10'];
    }
}
