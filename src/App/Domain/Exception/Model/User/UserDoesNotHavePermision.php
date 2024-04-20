<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User;

use Exception;

use function sprintf;

final class UserDoesNotHavePermision extends Exception
{
    public static function withCreateUsers(): self
    {
        return new self(
            'El usuario no tiene permisos para crear usuarios'
        );
    }

    public static function withUpdateUsers(): self
    {
        return new self(
            'El usuario no tiene permisos para editar usuarios'
        );
    }

    public static function withCreateUserRole(string $rol): self
    {
        return new self(
            sprintf(
                'El usuario no tiene permisos para crear un usuario con el rol %s',
                $rol
            )
        );
    }

    public static function withUpdateUserRole(string $rol): self
    {
        return new self(
            sprintf(
                'El usuario no tiene permisos para modificar un usuario con el rol %s',
                $rol
            )
        );
    }
}
