<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\User;

use App\Domain\Model\User\UserAdministrator;
use App\Domain\ValueObject\Rol;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SfUserAdministrator implements UserInterface, PasswordAuthenticatedUserInterface
{
    private UserAdministrator $userAdministrator;

    public function __construct(UserAdministrator $userAdministrator)
    {
        $this->userAdministrator = $userAdministrator;
    }

    public static function fromDomainModel(UserAdministrator $userAdministrator): self
    {
        return new self($userAdministrator);
    }

    public function getRoles(): array
    {
        return $this->userAdministrator->roles()->asArray();
    }

    public function getPassword(): string
    {
        return $this->userAdministrator->password()->asString();
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->userAdministrator->emailAddress()->asString();
    }

    public function getId(): string
    {
        return $this->userAdministrator->id()->asString();
    }

    public function getName(): string
    {
        return $this->userAdministrator->name()->asString();
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userAdministrator->emailAddress()->asString();
    }

    public function getUserType(): string
    {
        return 'Administrador';
    }
}
