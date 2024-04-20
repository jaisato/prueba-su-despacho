<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\User\UserAdministrator;

use App\Domain\Model\User\UserAdministrator;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Rol;

interface UserAdministratorReadRepository
{
    public function ofIdAndActiveOrFail(Id $id): UserAdministrator;

    public function ofEmailAddressAndFail(EmailAddress $emailAddress): void;

    public function ofEmailAddressOrFail(EmailAddress $emailAddress): UserAdministrator;

    public function ofEmailAddressAndRolOrFail(EmailAddress $emailAddress, Rol $rol): UserAdministrator;


    public function countAll(array $filters = []): Quantity;
}
