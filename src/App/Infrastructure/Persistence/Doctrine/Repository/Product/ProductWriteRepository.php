<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Product;

use App\Domain\Model\Product\Product;
use App\Domain\ValueObject\Id;
use Doctrine\ORM\EntityManagerInterface;

class ProductWriteRepository implements \App\Domain\Repository\Doctrine\Product\ProductWriteRepository
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(Product $product): void
    {
        $this->entityManager->persist($product);
    }

    public function nextIdentity(): Id
    {
        return Id::generate();
    }
}