<?php

declare(strict_types=1);

namespace App\Domain\Dto\User;

use App\Domain\Collection\ValueObject\RolCollection;
use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\Model\User\User;
use App\Domain\Model\User\UserAdministrator;
use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;

class DetalleUser
{
    public function __construct(
        public Id $id,
        public Name $name,
        public EmailAddress $emailAddress,
        public ?RolCollection $roles,
        public bool $isUserWeb,
    ) {
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    public static function fromModel(User $element): self
    {
        return new self(
            $element->id(),
            $element->name(),
            $element->emailAddress(),
            $element->roles(),
            $element::class === UserWeb::class
        );
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    public static function fromUserAdministratorModel(UserAdministrator $element): self
    {
        return new self(
            $element->id(),
            $element->name(),
            $element->emailAddress(),
            $element->roles(),
            false
        );
    }
}
