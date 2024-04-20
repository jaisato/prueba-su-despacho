<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Security;

use App\Domain\Exception\ValueObject\ValueObjectException;
use App\Domain\ValueObject\Security\ClearTextPassword;

use function mb_strlen;
use function sprintf;

final class ClearTextPasswordIsNotValid extends ValueObjectException
{
    public static function becauseStringIsEmpty(): self
    {
        return new self(
            'Password is empty'
        );
    }

    public static function becauseDoesNotHaveMinimalLength(string $clearTextPassword): self
    {
        return new self(
            sprintf(
                'Password requires a minimum of %s characters. Passed password has only %s',
                ClearTextPassword::MIN_PASSWORD_LENGTH,
                mb_strlen($clearTextPassword)
            )
        );
    }
}
