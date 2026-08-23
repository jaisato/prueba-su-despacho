<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\Product;

use Api\Ui\Controller\Product\CreateProductController;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Security\PasswordHash;
use App\Infrastructure\Factory\User\UserWebFactory;
use App\Infrastructure\Security\User\SfUserWeb;
use Faker\Generator;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\Ui\Controller\ControllerTest;
use Zenstruck\Foundry\ModelFactory;

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
        $user = UserWebFactory::findOrCreate(['emailAddress' => EmailAddress::fromString('jasato.holmes@gmail.com'), 'password' => PasswordHash::fromString('123456789')])->object();


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
}
