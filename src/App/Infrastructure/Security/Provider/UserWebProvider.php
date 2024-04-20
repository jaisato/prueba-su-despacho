<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Provider;

use App\Domain\Exception\Model\User\UserWeb\UserWebNotFound;
use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\Exception\ValueObject\IdIsNotValid;
use App\Domain\Repository\Doctrine\User\UserWeb\UserWebReadRepository;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Security\User\SfUserWeb;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserWebProvider implements UserProviderInterface
{
    public function __construct(
        private UserWebReadRepository $userWebRepository
    ) {
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByEmail($user->getUsername());
    }

    public function supportsClass(string $class): bool
    {
        return $class === SfUserWeb::class;
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    public function loadUserByEmail(string $emailAddress): ?UserInterface
    {
        try {
            return SfUserWeb::fromDomainModel(
                $this->userWebRepository->ofEmailAddressOrFail(
                    EmailAddress::fromString($emailAddress)
                )
            );
        } catch (UserWebNotFound | EmailAddressIsNotValid $e) {
        }

        return null;
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    public function loadUserByIdentifier(string $username): UserInterface
    {
        $user = $this->loadUserByEmail($username);

        if ($user) {
            return $user;
        }

        try {
            return SfUserWeb::fromDomainModel(
                $this->userWebRepository->ofIdAndActiveOrFail(
                    Id::fromString($username)
                )
            );
        } catch (UserWebNotFound | IdIsNotValid $e) {
        }

        throw new UserNotFoundException($e->getMessage());
    }
}
