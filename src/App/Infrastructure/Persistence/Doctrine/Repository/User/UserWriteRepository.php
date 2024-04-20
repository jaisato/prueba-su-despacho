<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\User;

use App\Domain\Model\User\User;
use App\Domain\Repository\Doctrine\User\UserWriteRepository as UserWriteRepositoryInterface;
use App\Domain\ValueObject\Id;
use Doctrine\ORM\EntityManagerInterface;

class UserWriteRepository implements UserWriteRepositoryInterface
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
    }

    public function nextIdentity(): Id
    {
        return Id::generate();
    }

    public function hardDelete(User $user): void
    {
        $this->entityManager->remove($user);
    }
}
