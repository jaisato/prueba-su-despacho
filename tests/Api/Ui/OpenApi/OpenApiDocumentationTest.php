<?php

declare(strict_types=1);

namespace Tests\Api\Ui\OpenApi;

use Tests\Api\Ui\Controller\ControllerTest;

use function in_array;
use function json_decode;
use function ksort;
use function strtoupper;

use const JSON_THROW_ON_ERROR;

/**
 * La documentación OpenAPI es lo único que API Platform genera por su cuenta en
 * esta API (las peticiones las atienden controladores propios), y no había
 * ningún test que la mirase. Fija el contrato que no debe moverse al actualizar
 * la librería: qué operaciones se publican, con qué identificador y cuáles
 * exigen token.
 *
 * @group openApi
 */
class OpenApiDocumentationTest extends ControllerTest
{
    private const HTTP_METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    public function setUp(): void
    {
        parent::initClient();
    }

    public function testDocumentationPublishesTheFourOperationsWithStableIds(): void
    {
        $document = $this->requestDocumentation();

        $operationIds = [];
        foreach ($document['paths'] as $path => $pathItem) {
            foreach ($pathItem as $method => $operation) {
                if (in_array($method, self::HTTP_METHODS, true)) {
                    $operationIds[strtoupper($method) . ' ' . $path] = $operation['operationId'];
                }
            }
        }

        ksort($operationIds);

        // Las rutas internas de API Platform (la operación no expuesta con la
        // que se genera el IRI de los recursos) no deben aparecer aquí.
        self::assertSame(
            [
                'GET /api/productos/{pagina}/{resultadosPorPagina}' => 'products_listProductsPaginatedDtoItem',
                'POST /api/form/{tipoForm}' => 'product_formFormResponseDtoItem',
                'POST /api/login' => 'postCredentialsItem',
                'POST /api/users/{tipoForm}' => 'signupFormResponseDtoItem',
            ],
            $operationIds
        );
    }

    public function testOnlyProductCreationRequiresTheBearerToken(): void
    {
        $document = $this->requestDocumentation();

        // Seguridad global: el esquema bearer que declara JwtDecorator.
        self::assertSame([['user_web' => []]], $document['security']);
        self::assertSame('bearer', $document['components']['securitySchemes']['user_web']['scheme']);

        // `security: []` en una operación anula la global: es pública.
        self::assertSame([], $document['paths']['/api/login']['post']['security']);
        self::assertSame([], $document['paths']['/api/users/{tipoForm}']['post']['security']);
        self::assertSame([], $document['paths']['/api/productos/{pagina}/{resultadosPorPagina}']['get']['security']);

        // La creación de productos no la anula, así que hereda el bearer.
        self::assertArrayNotHasKey('security', $document['paths']['/api/form/{tipoForm}']['post']);
    }

    public function testSwaggerUiIsServedAsHtml(): void
    {
        $this->client->request('GET', '/api/docs', server: ['HTTP_ACCEPT' => 'text/html']);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        self::assertStringContainsString('API Productos', (string) $this->client->getResponse()->getContent());
    }

    /**
     * @return array<string, mixed>
     */
    private function requestDocumentation(): array
    {
        $this->client->request('GET', '/api/docs.json');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        return json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }
}
