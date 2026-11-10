<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\User;

use Api\Ui\Controller\User\SignUpController;
use App\Domain\ValueObject\EmailAddress;
use App\Infrastructure\Factory\User\UserWebFactory;
use Doctrine\ORM\Events;
use Faker\Generator;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\Ui\Controller\ControllerTest;
use Zenstruck\Foundry\ModelFactory;

use function json_decode;
use function json_encode;

/**
 * POST /api/users/signup-user: el único endpoint de escritura que no pide
 * cuenta, porque es el que la crea.
 *
 * @group signUp
 */
class SignUpControllerTest extends ControllerTest
{
    private const PASSWORD = '1234567890';

    private const YA_EXISTE = 'Ya existe un usuario registrado con este correo';

    private Generator $faker;

    public function setUp(): void
    {
        parent::initClient();
        $this->faker = ModelFactory::faker();
    }

    public function testANewAccountIsCreated(): void
    {
        $response = $this->signUp($this->faker->unique()->safeEmail());

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        self::assertTrue($body['success']);
        self::assertNotEmpty($body['extraData']['user_id']);
    }

    public function testAnEmailThatAlreadyHasAnAccountIsRefused(): void
    {
        $email = $this->faker->unique()->safeEmail();
        UserWebFactory::createOne(['emailAddress' => EmailAddress::fromString($email)]);

        $response = $this->signUp($email);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        self::assertFalse($body['success']);
        self::assertSame(self::YA_EXISTE, $body['message']);
    }

    /**
     * Dos registros simultáneos con el mismo email pasan los dos la
     * comprobación previa del handler, y el índice único email_address_uniq
     * para al segundo cuando el bus hace flush. Ese segundo es tan "ya existe"
     * como el que llega después: antes se contestaba con el error genérico
     * ("inténtalo de nuevo más tarde", que tampoco iba a funcionar) y se
     * registraba como un fallo inesperado.
     *
     * La carrera se reproduce desde onFlush: justo antes de que Doctrine
     * inserte la cuenta nueva, otra cuenta pasa a tener ese mismo email,
     * como si la otra petición hubiera confirmado su registro en ese momento.
     */
    public function testTheLoserOfARaceForTheSameEmailIsToldTheEmailIsTaken(): void
    {
        $email = $this->faker->unique()->safeEmail();
        $other = $this->faker->unique()->safeEmail();
        UserWebFactory::createOne(['emailAddress' => EmailAddress::fromString($other)]);

        $connection = $this->entityManager->getConnection();
        $this->entityManager->getEventManager()->addEventListener(
            [Events::onFlush],
            new class ($connection, $email, $other) {
                private bool $done = false;

                public function __construct(
                    private readonly \Doctrine\DBAL\Connection $connection,
                    private readonly string $email,
                    private readonly string $other,
                ) {
                }

                public function onFlush(): void
                {
                    if ($this->done) {
                        return;
                    }

                    $this->done = true;
                    $this->connection->executeStatement(
                        'UPDATE user SET email_address = ? WHERE email_address = ?',
                        [$this->email, $this->other]
                    );
                }
            }
        );

        $response = $this->signUp($email);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        self::assertFalse($body['success']);
        self::assertSame(self::YA_EXISTE, $body['message']);
    }

    public function testPasswordsThatDoNotMatchAreRefused(): void
    {
        $response = $this->signUp($this->faker->unique()->safeEmail(), self::PASSWORD . 'x');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame('Las contraseñas no coinciden', json_decode($response->getContent(), true)['message']);
    }

    private function signUp(string $email, string $passwordRepeat = self::PASSWORD): Response
    {
        $this->client->request(
            'POST',
            $this->router->generate('api_signup', ['tipoForm' => SignUpController::TIPO_FORM]),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'nombre' => $this->faker->name(),
                'email' => $email,
                'password' => self::PASSWORD,
                'passwordRepeat' => $passwordRepeat,
            ])
        );

        return $this->client->getResponse();
    }
}
