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

use function array_column;
use function bin2hex;
use function json_decode;
use function json_encode;
use function random_bytes;

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
        // Unique per run: findOrCreate() does not find a user by its e-mail
        // value object, so against a database kept from an earlier run it
        // created a second jasato.holmes@gmail.com and the login failed.
        $email = 'jasato.holmes.' . bin2hex(random_bytes(6)) . '@example.com';
        UserWebFactory::createOne(['emailAddress' => EmailAddress::fromString($email), 'password' => PasswordHash::fromString('123456789')]);


        $this->client
            ->request(
                'POST',
                $this->router->generate('api_login'),
                server: ['CONTENT_TYPE' => 'application/json'],
                content: json_encode([
                    'email' => $email,
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

    /**
     * Wrong JSON types used to reach the typed request constructor as is: a
     * TypeError, i.e. a 500. Now they are reported per field.
     */
    public function testFieldsOfTheWrongTypeAreReportedNotA500(): void
    {
        $response = $this->createProduct([
            'name' => 'Producto',
            'description' => 'Descripción',
            'price' => 1.75,
            'iva' => 21.5,
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response['status']);
        self::assertEqualsCanonicalizing(
            [
                ['parameter' => 'price', 'message' => 'El valor tiene que ser un texto'],
                ['parameter' => 'iva', 'message' => 'El valor tiene que ser un número entero'],
            ],
            $response['body']['errors']
        );
    }

    public function testMissingFieldsAreNamed(): void
    {
        $response = $this->createProduct([]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response['status']);
        self::assertEqualsCanonicalizing(
            ['name', 'description', 'price', 'iva'],
            array_column($response['body']['errors'], 'parameter')
        );
    }

    /**
     * "6.50" used to be stored as 650 €: every dot was dropped as a thousands
     * separator. It is now refused, and the message shows the right notation.
     */
    public function testAPriceWithADotAsDecimalSeparatorIsRefused(): void
    {
        $response = $this->createProduct([
            'name' => 'Producto',
            'description' => 'Descripción',
            'price' => '6.50',
            'iva' => '21',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response['status']);
        self::assertStringContainsString('"6,50"', $response['body']['errors'][0]['message']);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array{status: int, body: array<string, mixed>}
     */
    private function createProduct(array $body): array
    {
        // A fresh user each time: findOrCreate() does not find a user by its
        // e-mail value object, so it creates a second one, and the login then
        // fails on an e-mail that matches two rows.
        $email = 'tipos.producto.' . bin2hex(random_bytes(6)) . '@example.com';
        UserWebFactory::createOne(['emailAddress' => EmailAddress::fromString($email), 'password' => PasswordHash::fromString('123456789')]);

        $this->client->request(
            'POST',
            $this->router->generate('api_login'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => '123456789'])
        );
        $token = json_decode((string) $this->client->getResponse()->getContent(), true)['token'];

        $this->client->request(
            'POST',
            $this->router->generate('api_create_product_form', ['tipoForm' => CreateProductController::TIPO_FORM]),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $token],
            content: json_encode($body)
        );

        $response = $this->client->getResponse();

        return [
            'status' => $response->getStatusCode(),
            'body' => json_decode((string) $response->getContent(), true),
        ];
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
