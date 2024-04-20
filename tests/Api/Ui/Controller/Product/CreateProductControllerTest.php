<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\Product;

use Api\Ui\Controller\Product\CreateProductController;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Security\PasswordHash;
use App\Infrastructure\Factory\User\UserWebFactory;
use App\Infrastructure\Security\User\SfUserWeb;
use Faker\Generator;
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
                server: ['CONTENT_TYPE' => 'application/json', 'Authorization' => 'Bearer '.$responseContent['token']],
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
}
