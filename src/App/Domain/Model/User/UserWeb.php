<?php

declare(strict_types=1);

namespace App\Domain\Model\User;

use App\Domain\Collection\ValueObject\RolCollection;
use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\Exception\ValueObject\NameIsNotValid;
use App\Domain\Exception\ValueObject\Security\ClearTextPasswordIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordHashIsNotValid;
use App\Domain\ValueObject\DateTime;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Rol;
use App\Domain\ValueObject\Security\ActivationToken;
use App\Domain\ValueObject\Security\ClearTextPassword;
use App\Domain\ValueObject\Security\PasswordHash;
use App\Domain\ValueObject\Security\RecoveryToken;
use App\Domain\ValueObject\Security\Token\SplittedToken;

class UserWeb extends User
{
    /**
     * @throws NameIsNotValid
     */
    public function __construct(
        protected Id $id,
        protected Name $name,
        protected EmailAddress $emailAddress,
        protected PasswordHash $password
    ) {
        parent::__construct(
            $id,
            $name,
            $emailAddress,
            $password,
            RolCollection::fromElements([Rol::web()])
        );
    }

    public static function crear(
        Id $id,
        Name $name,
        EmailAddress $emailAddress,
        PasswordHash $password
    ): self
    {
        return new self(
            $id,
            $name,
            $emailAddress,
            $password
        );
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

    public static function crearFromApi(
        Id $id,
        Name $name,
        EmailAddress $emailAddress,
        PasswordHash $password
    ): self {
        $element = new self(
            $id,
            $name,
            $emailAddress,
            $password
        );

        $element->activationToken = ActivationToken::fromSplittedToken(
            SplittedToken::generate()
        );

        return $element;
    }

    public function updatePasswordConToken(PasswordHash $passwordHash): void
    {
        $this->password      = $passwordHash;
        $this->updatedOn     = DateTime::now();
        $this->recoveryToken = null;
    }

    public function recuperarContrasena(): void
    {
        $splittedToken = SplittedToken::generate();

        $this->recoveryToken = RecoveryToken::fromSplittedToken(
            $splittedToken
        );
    }
}
