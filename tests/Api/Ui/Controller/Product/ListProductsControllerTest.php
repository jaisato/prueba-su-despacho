<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\Product;

use Tests\Api\Ui\Controller\ControllerTest;

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
