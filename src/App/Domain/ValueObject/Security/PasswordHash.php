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
use function trim;

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

    public static function fromString(string $string): self
    {
        self::validate(trim($string));
        $hash = password_hash(
            $string,
            self::PASSWORD_ALGORITHM
        );

        if ($hash === false) {
            throw new InvalidArgumentException('Invalid password');
        }

        return new self(
            $hash
        );
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

    /** @throws ClearTextPasswordIsNotValid */
    private static function validate(string $string): void
    {
        if ($string === '') {
            throw ClearTextPasswordIsNotValid::becauseStringIsEmpty();
        }
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
