<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\User\UserWeb;

use App\Domain\Model\User\UserWeb;
use App\Domain\Repository\Doctrine\User\UserWeb\UserWebWriteRepository as UserWebWriteRepositoryInterface;
use App\Domain\ValueObject\Id;
use Doctrine\ORM\EntityManagerInterface;

class UserWebWriteRepository implements UserWebWriteRepositoryInterface
{

    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    public function nextIdentity(): Id
    {
        return Id::generate();
    }

    public function save(UserWeb $UserWeb): void
    {
        $this->entityManager->persist($UserWeb);
    }
}
