<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User\UserAdministrator;

use Exception;

use function sprintf;

final class CrearUserAdministratorInvalidRoles extends Exception
{
    public static function invalidRoles(string $rol): self
    {
        return new self(
            sprintf(
                'No es posible crear un usuario con rol %s',
                $rol
            )
        );
    }
}
