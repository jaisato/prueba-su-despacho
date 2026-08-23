<?php

declare(strict_types=1);

namespace Api\Domain\Service\User;

use Api\Domain\Exception\Service\User\UserIsNotWebUser;
use App\Infrastructure\Security\User\SfUserWeb;
use Symfony\Component\Security\Core\User\UserInterface;

class UserWebTransformer
{
    /**
     * Accepts null so that an anonymous request produces the same
     * UserIsNotWebUser the API already knows how to report. The parameter used
     * to be non-nullable, so getUser() returning null - which is what an
     * unauthenticated request gives you - raised a TypeError and surfaced as a
     * 500.
     *
     * @throws UserIsNotWebUser
     */
    public static function transform(?UserInterface $user): SfUserWeb
    {
        if ($user instanceof SfUserWeb) {
            return $user;
        }

        throw UserIsNotWebUser::throw();
    }
}
