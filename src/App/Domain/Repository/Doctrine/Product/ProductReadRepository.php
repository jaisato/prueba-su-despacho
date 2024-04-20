<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\Product;

use App\Domain\Exception\Model\Product\ProductNotFound;
use App\Domain\Model\Product\Product;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;

interface ProductReadRepository
{
    /** @throws ProductNotFound */
    public function ofIdOrFail(Id $id): Product;

    public function all(array $filters = [], ?Limit $limit = null, ?OrderBy $orderBy = null): array;

    public function countAll(array $filters = []): Quantity;
}
