<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Security\Token;

use App\Domain\Exception\ValueObject\Security\Token\ClearTextTokenIsNotValid;
use App\Domain\Exception\ValueObject\Security\Token\SplittedTokenIsNotValid;
use InvalidArgumentException;
use ParagonIE\ConstantTime\Hex;
use Webmozart\Assert\Assert;

use function random_bytes;
use function sprintf;
use function substr;
use function trim;

final class SplittedToken
{
    public const TOKEN_LENGTH = 32;

    public const SINGLE_TOKEN_LENGTH = 16;

    private const TOKEN_BYTES_LENGTH = 8;

    private ClearTextToken $selector;

    private ClearTextToken $verifier;

    private function __construct(
        ClearTextToken $selector,
        ClearTextToken $verifier
    ) {
        $this->selector = $selector;
        $this->verifier = $verifier;
    }

    /**
     * @return SplittedToken
     */
    public static function generate(): self
    {
        return new self(
            ClearTextToken::fromString(
                Hex::encode(random_bytes(self::TOKEN_BYTES_LENGTH))
            ),
            ClearTextToken::fromString(
                Hex::encode(random_bytes(self::TOKEN_BYTES_LENGTH))
            )
        );
    }

    /**
     * @return SplittedToken
     *
     * @throws SplittedTokenIsNotValid
     * @throws ClearTextTokenIsNotValid
     */
    public static function fromString(string $token): self
    {
        $token = trim($token);

        self::validate($token);

        return new self(
            ClearTextToken::fromString(substr($token, 0, self::SINGLE_TOKEN_LENGTH)),
            ClearTextToken::fromString(substr($token, self::SINGLE_TOKEN_LENGTH))
        );
    }

    public function selector(): ClearTextToken
    {
        return $this->selector;
    }

    public function verifier(): TokenHash
    {
        return $this->verifier->makeHash();
    }

    public function asString(): string
    {
        return sprintf(
            '%s%s',
            $this->selector->asString(),
            $this->verifier->asString()
        );
    }

    public function matchesVerifier(TokenHash $hash): bool
    {
        return $this->verifier->matches($hash);
    }

    /**
     * @throws SplittedTokenIsNotValid
     */
    private static function validate(string $token): void
    {
        try {
            Assert::stringNotEmpty($token);
        } catch (InvalidArgumentException $e) {
            throw SplittedTokenIsNotValid::becauseStringIsEmpty();
        }

        try {
            Assert::length($token, self::TOKEN_LENGTH);
        } catch (InvalidArgumentException $e) {
            throw SplittedTokenIsNotValid::becauseStringLenghtIsInvalid($token);
        }
    }
}
