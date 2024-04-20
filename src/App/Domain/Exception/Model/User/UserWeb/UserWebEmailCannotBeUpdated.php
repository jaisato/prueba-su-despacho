<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User\UserWeb;

use Exception;

final class UserWebEmailCannotBeUpdated extends Exception
{
    public static function becauseEmailBelongsToAnotherUser(): self
    {
        return new self(
            'Este correo ya pertenece a un usuario'
        );
    }
}
