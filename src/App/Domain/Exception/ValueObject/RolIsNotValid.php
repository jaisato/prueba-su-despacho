<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject;

use function sprintf;

final class RolIsNotValid extends ValueObjectException
{
    public static function becauseRolIsNotValid(string $rol): self
    {
        return new self(
            sprintf(
                'El rol no "%s"  no es válido.',
                $rol
            )
        );
    }

    public static function becauseUserClassNotExists(): self
    {
        return new self(
            'El tipo de usuario no tiene un rol válido'
        );
    }
}
