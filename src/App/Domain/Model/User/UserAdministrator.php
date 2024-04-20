<?php

declare(strict_types=1);

namespace App\Domain\Model\User;

use App\Domain\Collection\ValueObject\RolCollection;
use App\Domain\ValueObject\DateTime;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Security\PasswordHash;

class UserAdministrator extends User
{
    public function __construct(
        protected Id $id,
        protected Name $name,
        protected EmailAddress $emailAddress,
        protected PasswordHash $password,
        protected RolCollection $roles,
    ) {
        parent::__construct(
            $id,
            $name,
            $emailAddress,
            $password,
            $roles,
        );
    }

    public static function crear(
        Id $id,
        Name $name,
        EmailAddress $emailAddress,
        PasswordHash $password,
        RolCollection $roles,
    ): self {
        return new self(
            $id,
            $name,
            $emailAddress,
            $password,
            $roles,
        );
    }

    public function actualizarAdministrator(
        Name $name,
        EmailAddress $emailAddress,
        RolCollection $roles,
    ): self {
        $this->name         = $name;
        $this->emailAddress = $emailAddress;
        $this->roles        = $roles;
        $this->updatedOn    = DateTime::now();

        return $this;
    }
}
