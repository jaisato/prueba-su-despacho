<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\User;

use App\Domain\Exception\Model\User\UserNotFound;
use App\Domain\Model\User\User;
use App\Domain\Repository\Doctrine\User\UserReadRepository as UserReadRepositoryInterface;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;
use App\Infrastructure\Persistence\Doctrine\QueryBuilder\User\UserQueryBuilder;
use App\Infrastructure\Persistence\Doctrine\ValueObject\EmailAddressType;
use App\Infrastructure\Persistence\Doctrine\ValueObject\IdType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UserReadRepository implements UserReadRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserQueryBuilder $userQueryBuilder
    ) {
    }

    public function ofIdOrFail(Id $id): User
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user')
            ->from(User::class, 'user')
            ->where(
                'user.id = :id'
            )
            ->setParameter('id', $id, IdType::TYPE_NAME);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserNotFound::withId($id);
        }
    }

    public function ofEmailOrFail(EmailAddress $emailAddress): User
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user')
            ->from(User::class, 'user')
            ->where(
                'user.emailAddress = :emailAddress'
            )
            ->setParameter('emailAddress', $emailAddress, EmailAddressType::TYPE_NAME);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserNotFound::withEmail($emailAddress);
        }
    }

    public function all(
        array $filters = [],
        ?Limit $limit = null,
        ?OrderBy $orderBy = null,
    ): array {
        $qb = $this->userQueryBuilder->__invoke($filters, $limit, $orderBy);

        return $qb->getQuery()->getResult();
    }

    public function countAll(
        array $filters = []
    ): Quantity {
        $qb = $this->userQueryBuilder->__invoke($filters);
        $qb->select('COUNT(user.id)');

        return Quantity::fromInt(
            (int) $qb->getQuery()->getSingleScalarResult()
        );
    }
}
