<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User;

use Exception;

final class UserPasswordCannotBeChanged extends Exception
{
    public static function becauseCurrentPasswordIsWrong(): self
    {
        return new self(
            'La contraseña actual es incorrecta'
        );
    }
}
