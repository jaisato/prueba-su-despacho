<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Security;

use App\Domain\Exception\ValueObject\ValueObjectException;

use function sprintf;

final class PasswordHashIsNotValid extends ValueObjectException
{
    public static function becauseItIsNotARealHash(string $hash): self
    {
        return new self(
            sprintf(
                'Invalid password hash "%s" provided',
                $hash
            )
        );
    }
}
