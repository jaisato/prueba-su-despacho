<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User\UserWeb;

use App\Domain\ValueObject\EmailAddress;
use Exception;

use function sprintf;

class UserWebAlreadyExists extends Exception
{
    public static function withEmailAddress(EmailAddress $emailAddress): self
    {
        return new self(
            sprintf(
                'Ya existe un usuario con este e-mail',
            )
        );
    }
}
