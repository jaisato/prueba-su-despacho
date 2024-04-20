<?php

declare(strict_types=1);

namespace App\Domain\Model\User;

use App\Domain\Collection\ValueObject\RolCollection;
use App\Domain\Exception\ValueObject\Security\ClearTextPasswordIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordHashIsNotValid;
use App\Domain\ValueObject\DateTime;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Security\ActivationToken;
use App\Domain\ValueObject\Security\ClearTextPassword;
use App\Domain\ValueObject\Security\PasswordHash;
use App\Domain\ValueObject\Security\RecoveryToken;
use Doctrine\Common\Collections\ArrayCollection;

abstract class User
{
    public const INTERVALO_MESES_CADUCIDAD_CONTRASENA = 3;

    protected DateTime $createdOn;
    protected DateTime $updatedOn;
    protected ?DateTime $lastAccessOn;
    protected ?DateTime $lastPasswordUpdate;

    protected ?RecoveryToken $recoveryToken;
    protected ?ActivationToken $activationToken;

    /** @var ArrayCollection */
    protected $products;

    public function __construct(
        protected Id $id,
        protected Name $name,
        protected EmailAddress $emailAddress,
        protected PasswordHash $password,
        protected RolCollection $roles
    ) {
        $this->createdOn = DateTime::now();
        $this->updatedOn = DateTime::now();
        $this->activationToken = null;
        $this->recoveryToken   = null;
        $this->products = new ArrayCollection();
    }


    public function actualizar(
        Name $name,
        EmailAddress $emailAddress,
    ): self {
        $this->name         = $name;
        $this->emailAddress = $emailAddress;
        $this->updatedOn    = DateTime::now();

        return $this;
    }

    public function actualizarRoles(RolCollection $rolCollection): self
    {
        $this->roles     = $rolCollection;
        $this->updatedOn = DateTime::now();

        return $this;
    }

    //</editor-fold>

    public function updatePassword(PasswordHash $passwordHash): void
    {
        $this->password           = $passwordHash;
        $this->updatedOn          = DateTime::now();
        $this->lastPasswordUpdate = DateTime::now();
    }

    public function login(): void
    {
        $this->lastAccessOn = DateTime::now();
    }

    public function recuperarContrasena(): void
    {
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function createdOn(): DateTime
    {
        return $this->createdOn;
    }

    public function updatedOn(): DateTime
    {
        return $this->updatedOn;
    }

    public function lastAccessOn(): ?DateTime
    {
        return $this->lastAccessOn;
    }

    public function emailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }

    public function password(): PasswordHash
    {
        return $this->password;
    }

    public function lastPasswordUpdate(): ?DateTime
    {
        return $this->lastPasswordUpdate;
    }

    public function roles(): RolCollection
    {
        return $this->roles;
    }

    public function activationToken(): ?ActivationToken
    {
        return $this->activationToken;
    }

    public function actualizarLastAccessOn(DateTime $lastAccessOn): void
    {
        $this->lastAccessOn = $lastAccessOn;
    }

    public function recoveryToken(): RecoveryToken
    {
        return $this->recoveryToken;
    }

    /**
     * @throws ClearTextPasswordIsNotValid
     * @throws PasswordHashIsNotValid
     */
    public function validarContrasena(string $passwordActual): bool
    {
        return ClearTextPassword::fromString($passwordActual)
            ->matches(PasswordHash::fromHash($this->password()->asString()));
    }

    /**
     * @return array
     */
    public function products(): array
    {
        return $this->products->toArray();
    }
}
