<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\User\UserWeb;

use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\Id;

interface UserWebWriteRepository
{
    public function nextIdentity(): Id;

    public function save(UserWeb $UserWeb): void;
}
