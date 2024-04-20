<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User;

use App\Domain\ValueObject\EmailAddress;
use Exception;

use function sprintf;

class UserAlreadyExistsWithEmail extends Exception
{
    public static function withEmailAddress(EmailAddress $emailAddress): self
    {
        return new self(
            sprintf(
                'Ya existe un usuario con este e-mail: %s',
                $emailAddress->asString()
            )
        );
    }
}
