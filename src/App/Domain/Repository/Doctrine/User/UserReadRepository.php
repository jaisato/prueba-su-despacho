<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\User;

use App\Domain\Exception\Model\User\UserNotFound;
use App\Domain\Model\User\User;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;

interface UserReadRepository
{
    /** @throws UserNotFound */
    public function ofIdOrFail(Id $id): User;

    public function all(array $filters = [], ?Limit $limit = null, ?OrderBy $orderBy = null): array;

    public function countAll(array $filters = []): Quantity;

    /** @throws UserNotFound */
    public function ofEmailOrFail(EmailAddress $email): User;
}
