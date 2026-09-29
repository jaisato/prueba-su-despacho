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
