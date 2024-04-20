<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\User\UserAdministrator;

use App\Domain\Model\User\UserAdministrator;
use App\Domain\ValueObject\Id;

interface UserAdministratorWriteRepository
{
    public function nextIdentity(): Id;
    public function save(UserAdministrator $userAdministrator): void;
}
