<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User\UserAdministrator;

use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Rol;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function sprintf;

final class UserAdministratorNotFound extends NotFoundHttpException
{
    public static function withEmailAddress(EmailAddress $emailAddress): self
    {
        return new self(
            sprintf(
                'No se ha encontrado ningún usuario con el email %s',
                $emailAddress->asString()
            )
        );
    }

    public static function withId(Id $id): self
    {
        return new self(
            sprintf(
                'No se ha encontrado ningún usuario con el id %s',
                $id->asString()
            )
        );
    }

    public static function withEmailAddressAndRol(EmailAddress $emailAddress, Rol $rol): self
    {
        return new self(
            sprintf(
                'No se ha encontrado ningún usuario con el email %s y rol %s',
                $emailAddress->asString(),
                $rol->asString()
            )
        );
    }
}
