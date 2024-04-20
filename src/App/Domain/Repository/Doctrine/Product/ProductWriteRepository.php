<?php

declare(strict_types=1);

namespace App\Domain\Repository\Doctrine\Product;

use App\Domain\Model\Product\Product;
use App\Domain\ValueObject\Id;

interface ProductWriteRepository
{
    public function save(Product $product): void;

    public function nextIdentity(): Id;
}
