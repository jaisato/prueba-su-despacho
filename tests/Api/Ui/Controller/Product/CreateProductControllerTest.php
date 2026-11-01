<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\Product;

use Api\Ui\Controller\Product\CreateProductController;
use App\Domain\ValueObject\EmailAddress;
use App\Infrastructure\Factory\User\UserWebFactory;
use App\Infrastructure\Security\User\SfUserWeb;
use Faker\Generator;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\Ui\Controller\ControllerTest;
use Zenstruck\Foundry\ModelFactory;

use function array_column;
use function array_merge;
use function json_decode;
use function json_encode;

/**
 * @group createProduct
 */
class CreateProductControllerTest extends ControllerTest
{
    private Generator $faker;

    /** Gets execute before every test */
    public function setUp(): void
    {
        parent::initClient();
        $this->faker = ModelFactory::faker();
    }

    public function testCreateProductAPI(): void
    {
        // Buscar también por 'password' no encontraba nunca al usuario (cada
        // PasswordHash lleva su propia sal), así que cada ejecución creaba
        // otra cuenta con el mismo email y, desde la segunda, el login fallaba.
        // La contraseña es la de UserWebFactory::getDefaults(): '123456789'.
        UserWebFactory::findOrCreate(['emailAddress' => EmailAddress::fromString('jasato.holmes@gmail.com')]);


        $this->client
            ->request(
                'POST',
                $this->router->generate('api_login'),
                server: ['CONTENT_TYPE' => 'application/json'],
                content: json_encode([
                    'email' => 'jasato.holmes@gmail.com',
                    'password' => '123456789',
                ])
            );

        $response        = $this->client->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->client
            ->request(
                'POST',
                $this->router->generate(
                    'api_create_product_form',
                    ['tipoForm' => CreateProductController::TIPO_FORM]
                ),
                // BrowserKit reads request headers out of the server array under
                // their HTTP_ names. Spelling this 'Authorization' meant the
                // token was never sent: the request was authenticated by the
                // session cookie the login firewall used to open, so the test
                // passed while proving nothing about the JWT.
                server: ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.$responseContent['token']],
                content: json_encode([
                    'name' => $this->faker->name(),
                    'description' => $this->faker->paragraph,
                    'price' => '1,77',
                    'iva' => 21,
                ])
            );
        $response        = $this->client->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertTrue($responseContent['success']);
    }

    public function testCreateProductIsRejectedWithoutAToken(): void
    {
        // The firewall pattern used to be ^/area-usuario/, which matches none
        // of the routes, and access_control pointed at ^/api/area-usuario/,
        // which matches nothing either - so this endpoint required nothing at
        // all despite the README documenting it as authenticated.
        $this->client
            ->request(
                'POST',
                $this->router->generate(
                    'api_create_product_form',
                    ['tipoForm' => CreateProductController::TIPO_FORM]
                ),
                server: ['CONTENT_TYPE' => 'application/json'],
                content: json_encode([
                    'name' => $this->faker->name(),
                    'description' => $this->faker->paragraph,
                    'price' => '1,77',
                    'iva' => 21,
                ])
            );

        self::assertContains(
            $this->client->getResponse()->getStatusCode(),
            [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN]
        );
    }

    /**
     * Un campo con el tipo JSON equivocado es un formulario inválido (400 con
     * el campo señalado), no un TypeError al construir la petición (500).
     *
     * @dataProvider badlyTypedFields
     */
    public function testABadlyTypedFieldIsAnInvalidForm(array $override, string $field): void
    {
        $response = $this->postProduct(array_merge($this->validProduct(), $override));

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        self::assertFalse($body['success']);
        self::assertContains($field, array_column($body['errors'], 'parameter'));
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function badlyTypedFields(): array
    {
        return [
            'precio como número' => [['price' => 1.77], 'price'],
            'nombre como lista'  => [['name' => ['x']], 'name'],
            'IVA como texto'     => [['iva' => 'veintiuno'], 'iva'],
        ];
    }

    public function testAQuotedIvaIsAccepted(): void
    {
        $response = $this->postProduct(array_merge($this->validProduct(), ['iva' => '21']));

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    /**
     * "1.77" se guardaba como 177 €: el punto se quitaba como separador de
     * millares estuviera donde estuviera.
     */
    public function testAPriceWithADecimalPointIsRefusedInsteadOfMultipliedByAHundred(): void
    {
        $response = $this->postProduct(array_merge($this->validProduct(), ['price' => '1.77']));

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertStringContainsString('6,50', $response->getContent());
    }

    /**
     * @return array<string, mixed>
     */
    private function validProduct(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->paragraph,
            'price' => '1,77',
            'iva' => 21,
        ];
    }

    /**
     * @param array<string, mixed> $product
     */
    private function postProduct(array $product): Response
    {
        UserWebFactory::findOrCreate(['emailAddress' => EmailAddress::fromString('jasato.holmes@gmail.com')]);

        $this->client->request(
            'POST',
            $this->router->generate('api_login'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'jasato.holmes@gmail.com', 'password' => '123456789'])
        );
        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];

        $this->client->request(
            'POST',
            $this->router->generate('api_create_product_form', ['tipoForm' => CreateProductController::TIPO_FORM]),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.$token],
            content: json_encode($product)
        );

        return $this->client->getResponse();
    }
}
