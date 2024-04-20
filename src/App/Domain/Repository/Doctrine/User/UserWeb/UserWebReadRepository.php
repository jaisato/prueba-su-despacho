<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\User\UserWeb;

use App\Domain\Exception\Model\User\UserWeb\UserWebNotFound;
use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;
use App\Domain\ValueObject\Security\Token\ClearTextToken;

interface UserWebReadRepository
{
    public function all(array $filters = [], ?Limit $limit = null, ?OrderBy $orderBy = null): array;

    public function countAll(array $filters = []): Quantity;

    /** @throws UserWebNotFound */
    public function ofIdOrFail(Id $id): UserWeb;

    public function ofEmailAddressAndFail(EmailAddress $emailAddress): void;

    public function ofEmailAddressOrFail(EmailAddress $emailAddress): UserWeb;


    public function ofIdAndActiveOrFail(Id $id): UserWeb;

    /** @throws UserWebNotFound */
    public function ofActivationTokenSelectorOrFail(ClearTextToken $selector): UserWeb;

    /** @throws UserWebNotFound */
    public function ofRecoveryTokenSelectorOrFail(ClearTextToken $selector): UserWeb;

    public function allByEmail(string $termn = ''): array;
}
