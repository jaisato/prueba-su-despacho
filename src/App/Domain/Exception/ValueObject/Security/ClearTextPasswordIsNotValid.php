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

    /**
     * El mensaje no incluye la longitud recibida a propósito: describir la
     * entrada rechazada en un error que puede acabar en un log es exactamente
     * lo que no interesa cuando esa entrada es una contraseña.
     */
    public static function becauseExceedsMaximumLength(): self
    {
        return new self(
            sprintf(
                'Password exceeds the maximum of %s characters',
                ClearTextPassword::MAX_PASSWORD_LENGTH
            )
        );
    }
}
