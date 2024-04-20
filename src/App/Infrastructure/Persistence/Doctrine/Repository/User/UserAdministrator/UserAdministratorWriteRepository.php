<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\User\UserAdministrator;

use App\Domain\Model\User\UserAdministrator;
use App\Domain\Repository\Doctrine\User\UserAdministrator\UserAdministratorWriteRepository as UserAdministratorWriteRepositoryInterface;
use App\Domain\ValueObject\Id;
use Doctrine\ORM\EntityManagerInterface;

class UserAdministratorWriteRepository implements UserAdministratorWriteRepositoryInterface
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

    public function save(UserAdministrator $userAdministrator): void
    {
        $this->entityManager->persist($userAdministrator);
    }
}
