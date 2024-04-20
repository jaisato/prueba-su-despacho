<?php
declare(strict_types=1);

namespace App\Domain\ValueObject\Security;

use App\Domain\Exception\ValueObject\Security\ClearTextPasswordIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordHashIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordsDoNotMatch;
use RandomLib\Factory;
use Webmozart\Assert\Assert;

final class ClearTextPassword
{
    public const MIN_PASSWORD_LENGTH = 6;
    private const RANDOM_PASSWORD_LENGTH = 16;
    private const RANDOM_PASSWORD_CHARACTER_LIST = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';

    private string $password;

    private function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * @throws ClearTextPasswordIsNotValid
     */
    public static function fromString(string $password): self
    {
        $password = trim($password);

        self::validate($password);

        return new self($password);
    }

    public static function generate(): self
    {
        $generator = (new Factory())->getMediumStrengthGenerator();

        return new self(
            $generator->generateString(
                self::RANDOM_PASSWORD_LENGTH,
                self::RANDOM_PASSWORD_CHARACTER_LIST
            )
        );
    }

    /**
     * @throws PasswordHashIsNotValid
     */
    public function makeHash(): PasswordHash
    {
        $hash = password_hash(
            $this->password,
            PasswordHash::PASSWORD_ALGORITHM
        );

        if (false === $hash) {
            throw PasswordHashIsNotValid::becauseItIsNotARealHash('');
        }

        return PasswordHash::fromHash(
            $hash
        );
    }

    public function matches(PasswordHash $hash): bool
    {
        return password_verify(
            $this->password,
            $hash->asString()
        );
    }

    public function asString(): string
    {
        return $this->password;
    }

    /**
     * @throws ClearTextPasswordIsNotValid
     */
    private static function validate(string $password): void
    {
        try {
            Assert::stringNotEmpty($password);
        } catch (\InvalidArgumentException $e) {
            throw ClearTextPasswordIsNotValid::becauseStringIsEmpty();
        }

        try {
            Assert::minLength($password, self::MIN_PASSWORD_LENGTH);
        } catch (\InvalidArgumentException $e) {
            throw ClearTextPasswordIsNotValid::becauseDoesNotHaveMinimalLength($password);
        }
    }

    /**
     * @throws PasswordsDoNotMatch
     */
    public static function passwordsMatch(
        string $passwordOne,
        string $passwordTwo
    ) {
        $matches = self::fromString($passwordOne)->matches(
            self::fromString($passwordTwo)->makeHash()
        );

        if (!$matches) {
            throw PasswordsDoNotMatch::withDifferentValues();
        }
    }
}
