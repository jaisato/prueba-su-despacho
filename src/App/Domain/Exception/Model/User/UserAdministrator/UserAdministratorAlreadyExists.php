<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User\UserAdministrator;

use App\Domain\ValueObject\EmailAddress;
use Exception;

use function sprintf;

class UserAdministratorAlreadyExists extends Exception
{
    public static function withEmailAddress(EmailAddress $emailAddress): self
    {
        return new self(
            'Ya existe un usuario con este e-mail'
        );
    }
}
