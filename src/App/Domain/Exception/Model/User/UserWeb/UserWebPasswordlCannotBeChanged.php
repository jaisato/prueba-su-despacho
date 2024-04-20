<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User\UserWeb;

use Exception;

final class UserWebPasswordlCannotBeChanged extends Exception
{
    public static function becauseCurrentPasswordIsWrong(): self
    {
        return new self(
            'La contraseña actual es incorrecta'
        );
    }
}
