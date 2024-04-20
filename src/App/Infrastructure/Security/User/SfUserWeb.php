<?php

namespace App\Infrastructure\Security\User;

use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\Rol;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SfUserWeb implements UserInterface, PasswordAuthenticatedUserInterface
{
    private UserWeb $userWeb;

    public function __construct(UserWeb $userWeb)
    {
        $this->userWeb = $userWeb;
    }

    public static function fromDomainModel(UserWeb $userWeb): self
    {
        return new self($userWeb);
    }

    public function getRoles(): array
    {
        return $this->userWeb->roles()->asArray();
    }

    public function getPassword(): string
    {
        return $this->userWeb->password()->asString();
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->userWeb->emailAddress()->asString();
    }

    public function getId(): string
    {
        return $this->userWeb->id()->asString();
    }

    public function getName(): string
    {
        return $this->userWeb->name()->asString();
    }

    public function eraseCredentials() {}

    public function getUserIdentifier(): string
    {
        return $this->userWeb->id()->asString();
    }

    public function user(): UserWeb
    {
        return $this->userWeb;
    }

    public function getUserType(): string
    {
        return 'Web';
    }
}
