<?php

declare(strict_types=1);

namespace Api\Domain\Service\User;

use Api\Domain\Exception\Service\User\UserIsNotWebUser;
use App\Infrastructure\Security\User\SfUserWeb;
use Symfony\Component\Security\Core\User\UserInterface;

class UserWebTransformer
{
    /**
     * @throws UserIsNotWebUser
     */
    public static function transform(UserInterface $user): SfUserWeb
    {
        if ($user instanceof SfUserWeb) {
            return $user;
        }

        throw UserIsNotWebUser::throw();
    }
}
