<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Security\Token;

use App\Domain\ValueObject\Security\Token\SplittedToken;
use Exception;

use function mb_strlen;
use function sprintf;

final class SplittedTokenIsNotValid extends Exception
{
    /**
     * @return SplittedTokenIsNotValid
     */
    public static function becauseStringIsEmpty(): self
    {
        return new self(
            'String for token must not be empty'
        );
    }

    /**
     * @return SplittedTokenIsNotValid
     */
    public static function becauseStringLenghtIsInvalid(string $string): self
    {
        return new self(
            sprintf(
                'Length of string is not valid. "%s" was expected and "%s" was given',
                SplittedToken::TOKEN_LENGTH,
                mb_strlen($string)
            )
        );
    }
}
