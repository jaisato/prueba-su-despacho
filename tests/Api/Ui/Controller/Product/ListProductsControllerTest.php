<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\Product;

use App\Domain\ValueObject\Name;
use App\Infrastructure\Factory\Product\ProductFactory;
use App\Infrastructure\Factory\User\UserWebFactory;
use Tests\Api\Ui\Controller\ControllerTest;

use function array_column;
use function bin2hex;
use function http_build_query;
use function json_decode;
use function parse_str;
use function parse_url;
use function random_bytes;

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
     * El filtro `name` busca un trozo del nombre, letra por letra: `%` y `_`
     * son caracteres como cualquier otro. Iban sin escapar dentro del LIKE,
     * así que buscar "100%" devolvía también "1000 ..." (el `%` casaba con
     * cualquier cosa) y "a_b" devolvía "axb".
     *
     * @dataProvider likeWildcards
     */
    public function testTheNameFilterTakesLikeWildcardsLiterally(
        string $wanted,
        string $lookalike,
        string $filter
    ): void {
        $token = bin2hex(random_bytes(8));
        $user  = UserWebFactory::createOne();
        ProductFactory::createOne(['name' => Name::fromString($token . $wanted), 'user' => $user]);
        ProductFactory::createOne(['name' => Name::fromString($token . $lookalike), 'user' => $user]);

        $this->client->request('GET', '/api/productos/1/10?' . http_build_query(['name' => $token . $filter]));

        $this->assertResponseIsSuccessful();
        $body = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([$token . $wanted], array_column($body['products'], 'name'));
    }

    /**
     * Los enlaces de la paginación son los del mismo listado: llevan el
     * filtro `name` (y el `orden`) con el que se pidió la página. Antes solo
     * llevaban el orden, así que la página 2 de `?name=x` era la página 2 de
     * todos los productos mientras los totales seguían siendo los del filtro.
     */
    public function testPaginationLinksKeepTheNameFilterAndTheOrder(): void
    {
        $token = bin2hex(random_bytes(8));
        $user  = UserWebFactory::createOne();
        foreach (['uno', 'dos', 'tres'] as $sufijo) {
            ProductFactory::createOne(['name' => Name::fromString($token . ' ' . $sufijo), 'user' => $user]);
        }

        $this->client->request('GET', '/api/productos/1/2?' . http_build_query([
            'name'  => $token,
            'orden' => 'fechaCreacion_asc',
        ]));

        $this->assertResponseIsSuccessful();
        $content    = (string) $this->client->getResponse()->getContent();
        $paginacion = json_decode($content, true, flags: JSON_THROW_ON_ERROR)['paginacion'];

        $urls = [$paginacion['siguiente']['url'], ...array_column($paginacion['paginas'], 'url')];
        self::assertCount(3, $urls);
        foreach ($urls as $url) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            self::assertSame(['name' => $token, 'orden' => 'fechaCreacion_asc'], $query, $url);
        }

        $this->client->request('GET', $paginacion['siguiente']['url']);

        $this->assertResponseIsSuccessful();
        $body = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(1, $body['products']);
        self::assertStringStartsWith($token, $body['products'][0]['name']);
        self::assertSame(3, $body['paginacion']['totalFiltrado']);
        // La última página, a medias: del 3 al 3 de 3, no del 2 al 4.
        self::assertSame(3, $body['paginacion']['indiceInicio']);
        self::assertSame(3, $body['paginacion']['indiceFinal']);
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function likeWildcards(): iterable
    {
        yield 'percent sign' => [' 100% algodón', ' 1000 algodón', ' 100%'];
        yield 'underscore' => [' talla_m', ' tallaxm', ' talla_m'];
        yield 'backslash' => [' a\\b', ' ab', ' a\\b'];
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
