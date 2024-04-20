<?php

declare(strict_types=1);

namespace Api\Domain\Exception\Service\User;

use Exception;

class UserIsNotWebUser extends Exception
{
    public static function throw(): UserIsNotWebUser
    {
        return new self('El usuario no es un usuario de tipo web');
    }
}
