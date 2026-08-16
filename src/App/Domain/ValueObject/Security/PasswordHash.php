<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Security;

use App\Domain\Exception\ValueObject\Security\ClearTextPasswordIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordHashIsNotValid;
use InvalidArgumentException;

use function bin2hex;
use function password_hash;
use function random_bytes;
use function strpos;

use const PASSWORD_ARGON2ID;

final class PasswordHash
{
    public const FAKER_METHOD = 'PasswordHash::generateRandom()';

    public const PASSWORD_ALGORITHM = PASSWORD_ARGON2ID;

    private const RANDOM_PASSWORD_LENGTH = 100;

    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function generateRandom(?int $passwordLength): self
    {
        if (! $passwordLength) {
            $passwordLength = self::RANDOM_PASSWORD_LENGTH;
        }

        $hash = password_hash(
            bin2hex(
                random_bytes(
                    $passwordLength
                )
            ),
            self::PASSWORD_ALGORITHM
        );

        if ($hash === false) {
            throw new InvalidArgumentException('Invalid random password');
        }

        return new self(
            $hash
        );
    }

    /**
     * Deriva el hash de una contraseña en claro.
     *
     * Delega en ClearTextPassword para que **haya una sola política**. Este
     * método aplicaba sus propias reglas -sólo "no vacía"- mientras que
     * ClearTextPassword exige longitud mínima y máxima, y el registro pasa por
     * aquí mientras que el login pasa por allí. Las dos rutas no coincidían:
     *
     * - Una contraseña de menos de MIN_PASSWORD_LENGTH se aceptaba al
     *   registrarse y se rechazaba al entrar, así que la cuenta quedaba
     *   inutilizable desde el primer momento.
     * - Una contraseña de más de MAX_PASSWORD_LENGTH esquivaba el tope: se
     *   pagaba el Argon2id completo desde un endpoint sin autenticar y la
     *   cuenta resultante tampoco podía autenticarse después.
     *
     * También unifica el recorte: se validaba `trim($string)` pero se hasheaba
     * `$string` sin recortar, de modo que la misma contraseña con un espacio al
     * final producía hashes distintos según el camino.
     *
     * No hay recursión: ClearTextPassword::makeHash() construye el resultado con
     * self::fromHash(), no con este método.
     *
     * @throws ClearTextPasswordIsNotValid
     * @throws PasswordHashIsNotValid
     */
    public static function fromString(string $string): self
    {
        return ClearTextPassword::fromString($string)->makeHash();
    }

    /**
     * @throws PasswordHashIsNotValid
     */
    public static function fromHash(string $hash): self
    {
        self::validateHash($hash);

        return new self($hash);
    }

    public function asString(): string
    {
        return $this->hash;
    }

    /**
     * @throws PasswordHashIsNotValid
     */
    private static function validateHash(string $hash): void
    {
        if (strpos($hash, '$') !== 0) {
            throw PasswordHashIsNotValid::becauseItIsNotARealHash($hash);
        }
    }
}
