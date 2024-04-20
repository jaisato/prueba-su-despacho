<?php
declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Security\Token;

use App\Domain\Exception\ValueObject\ValueObjectException;
use App\Domain\ValueObject\Security\Token\TokenHash;

final class TokenHashIsNotValid extends ValueObjectException
{
    public static function becauseHashSizeIsNotValid(string $hash): self
    {
        return new self(
            sprintf(
                'Hash size must be "%s". A hash with "%s" character(s) was given',
                TokenHash::HASH_SIZE,
                mb_strlen($hash)
            )
        );
    }
}
