<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\User;

use App\Domain\Model\User\User;
use App\Domain\ValueObject\Id;

interface UserWriteRepository
{
    public function save(User $user): void;

    public function nextIdentity(): Id;

    public function hardDelete(User $user): void;
}
