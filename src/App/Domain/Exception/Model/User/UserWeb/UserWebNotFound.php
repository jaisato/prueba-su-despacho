<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\User\UserWeb;

use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function sprintf;

final class UserWebNotFound extends NotFoundHttpException
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
                'UserWeb with id %s was not found',
                $id->asString()
            )
        );
    }

    public static function withToken(): self
    {
        return new self(
            sprintf(
                'User not found with token'
            )
        );
    }
}
