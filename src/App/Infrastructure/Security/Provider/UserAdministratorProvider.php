<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Provider;

use App\Domain\Exception\Model\User\UserAdministrator\UserAdministratorBlocked;
use App\Domain\Exception\Model\User\UserAdministrator\UserAdministratorNotFound;
use App\Domain\Exception\ValueObject\EmailAddressEncryptedIsNotValid;
use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\Repository\Doctrine\User\UserAdministrator\UserAdministratorReadRepository;
use App\Domain\ValueObject\EmailAddressEncrypted;
use App\Infrastructure\Security\User\SfUserAdministrator;
use App\Infrastructure\Service\EncryptRGPD\EncryptRGPD;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserAdministratorProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UserAdministratorReadRepository $userAdministratorRepository,
        private readonly EncryptRGPD $encryptRGPD,
    ) {
    }

    /**
     * @throws EmailAddressIsNotValid
     * @throws EmailAddressEncryptedIsNotValid
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByEncryptedEmail($user->getUsername());
    }

    public function supportsClass(string $class): bool
    {
        return $class === SfUserAdministrator::class;
    }

    /**
     * @throws EmailAddressEncryptedIsNotValid
     */
    public function loadUserByEncryptedEmail(string $emailEncrypted): UserInterface
    {
        try {
            return SfUserAdministrator::fromDomainModel(
                $this->userAdministratorRepository->ofEmailAddressAndActiveOrFail(
                    EmailAddressEncrypted::fromEncryptedString($emailEncrypted)
                )
            );
        } catch (UserAdministratorNotFound | UserAdministratorBlocked $e) {
        }

        throw new UserNotFoundException($e->getMessage());
    }

    /**
     * @throws EmailAddressEncryptedIsNotValid
     */
    public function loadUserByIdentifier(string $username): UserInterface
    {
        $emailEncrypted = $this->encryptRGPD->encryptFromString($username);

        return $this->loadUserByEncryptedEmail($emailEncrypted);
    }
}
