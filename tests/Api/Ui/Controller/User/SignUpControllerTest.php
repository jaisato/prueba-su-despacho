<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller\User;

use Api\Ui\Controller\User\SignUpController;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\Ui\Controller\ControllerTest;

use function array_column;
use function json_decode;
use function json_encode;

/**
 * @group signUp
 */
class SignUpControllerTest extends ControllerTest
{
    public function setUp(): void
    {
        parent::initClient();
    }

    /**
     * The required-field constraints were docblock annotations, which this
     * application never reads (framework.annotations is off): nothing checked
     * them, the nulls reached the command's string parameters and the caller
     * got the generic "try again later" error instead of the missing fields.
     */
    public function testMissingFieldsAreReportedOneByOne(): void
    {
        $response = $this->signUp([]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response['status']);
        self::assertSame('No se ha podido registrar el usuario', $response['body']['message']);
        self::assertEqualsCanonicalizing(
            ['nombre', 'email', 'password', 'passwordRepeat'],
            array_column($response['body']['errors'], 'parameter')
        );
    }

    /**
     * A field of the wrong JSON type used to reach the typed request
     * constructor as is: a TypeError, i.e. a 500.
     */
    public function testAFieldOfTheWrongTypeIsAValidationErrorNotA500(): void
    {
        $response = $this->signUp([
            'nombre' => ['Ana'],
            'email' => 'ana@example.com',
            'password' => '1234567890',
            'passwordRepeat' => '1234567890',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response['status']);
        self::assertSame(
            [['parameter' => 'nombre', 'message' => 'El valor tiene que ser un texto']],
            $response['body']['errors']
        );
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array{status: int, body: array<string, mixed>}
     */
    private function signUp(array $body): array
    {
        $this->client->request(
            'POST',
            $this->router->generate('api_signup', ['tipoForm' => SignUpController::TIPO_FORM]),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($body)
        );

        $response = $this->client->getResponse();

        return [
            'status' => $response->getStatusCode(),
            'body' => json_decode((string) $response->getContent(), true),
        ];
    }
}
