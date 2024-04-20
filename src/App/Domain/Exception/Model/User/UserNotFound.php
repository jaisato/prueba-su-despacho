<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User;

use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function sprintf;

final class UserNotFound extends NotFoundHttpException
{
    public static function withId(Id $id): self
    {
        return new self(
            sprintf(
                'No se ha encontrado ningún usuario con el id %s',
                $id->asString()
            )
        );
    }

    public static function withRole(string $rol): self
    {
        return new self(
            sprintf(
                'No se ha encontrado ningún usuario con el rol %s',
                $rol
            )
        );
    }

    public static function withEmail(EmailAddress $email): self
    {
        return new self(
            sprintf(
                'No se ha encontrado ningún usuario con el email %s',
                $email->asString()
            )
        );
    }
}
