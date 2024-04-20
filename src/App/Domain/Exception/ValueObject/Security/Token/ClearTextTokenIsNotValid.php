<?php
declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Security\Token;

use App\Domain\Exception\ValueObject\ValueObjectException;

final class ClearTextTokenIsNotValid extends ValueObjectException
{
    public static function becauseTokenIsEmpty(): self
    {
        return new self(
            'Given token is empty'
        );
    }
}
