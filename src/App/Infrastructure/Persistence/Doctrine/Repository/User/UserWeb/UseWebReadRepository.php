<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\User\UserWeb;

use App\Domain\Exception\Model\User\UserWeb\UserWebAlreadyExists;
use App\Domain\Exception\Model\User\UserWeb\UserWebNotFound;
use App\Domain\Exception\ValueObject\RolIsNotValid;
use App\Domain\Model\User\UserWeb;
use App\Domain\Repository\Doctrine\User\UserWeb\UserWebReadRepository as UserWebReadRepositoryInterface;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;
use App\Domain\ValueObject\Rol;
use App\Domain\ValueObject\Security\Token\ClearTextToken;
use App\Infrastructure\Persistence\Doctrine\QueryBuilder\User\UserQueryBuilder;
use App\Infrastructure\Persistence\Doctrine\ValueObject\EmailAddressType;
use App\Infrastructure\Persistence\Doctrine\ValueObject\IdType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UseWebReadRepository implements UserWebReadRepositoryInterface
{
    private readonly EntityManagerInterface $entityManager;
    private readonly UserQueryBuilder $queryBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserQueryBuilder $queryBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->queryBuilder  = $queryBuilder;
    }

    public function all(
        array $filters = [],
        ?Limit $limit = null,
        ?OrderBy $orderBy = null,
    ): array {
        $filters['role'] = Rol::web()->asString();
        $qb              = $this->queryBuilder->__invoke($filters, $limit, $orderBy);

        return $qb->getQuery()->getResult();
    }

    public function countAll(
        array $filters = [],
        ?Limit $limit = null,
    ): Quantity {
        $filters['role'] = Rol::web()->asString();
        $qb              = $this->queryBuilder->__invoke($filters, $limit);
        $qb->select('COUNT(user.id)');

        return Quantity::fromInt(
            (int) $qb->getQuery()->getSingleScalarResult()
        );
    }

    public function ofIdOrFail(Id $id): UserWeb
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user')
            ->from(UserWeb::class, 'user')
            ->where(
                'user.id = :id'
            )
            ->setParameter('id', $id, IdType::TYPE_NAME);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserWebNotFound::withId($id);
        }
    }

    public function ofEmailAddressAndFail(EmailAddress $emailAddress): void
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('COUNT(u)')
            ->from(UserWeb::class, 'u')
            ->where(
                'u.emailAddress = :email_address'
            )
            ->setParameter('email_address', $emailAddress, EmailAddressType::TYPE_NAME);

        $result = (int) $qb->getQuery()->getSingleScalarResult();

        if ($result === 0) {
            return;
        }

        throw UserWebAlreadyExists::withEmailAddress($emailAddress);
    }

    public function ofEmailAddressOrFail(EmailAddress $emailAddress): UserWeb
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user')
            ->from(UserWeb::class, 'user')
            ->where(
                'user.emailAddress = :correo_electronico'
            )
            ->setParameter('correo_electronico', $emailAddress, EmailAddressType::TYPE_NAME);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserWebNotFound::withEmailAddress($emailAddress);
        }
    }

    public function ofIdAndActiveOrFail(Id $id): UserWeb
    {
        $qb = $this->queryBuilder->__invoke(
            [
                'id' => $id,
            ]
        );

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserWebNotFound::withId($id);
        }
    }

    public function allByEmail(
        string $termn = '',
    ): array {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user')
            ->from(UserWeb::class, 'user')
            ->where(
                'user.emailAddress LIKE :correo_electronico'
            )
            ->setParameter('correo_electronico', '%' . $termn . '%');

        try {
            return $qb
                ->getQuery()
                ->getResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return [];
        }
    }

    /** @throws UserWebNotFound */
    public function ofActivationTokenSelectorOrFail(ClearTextToken $selector): UserWeb
    {
        $qb = $this->queryBuilder->__invoke(
            [
                'activationTokenSelector' => $selector,
            ]
        );

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserWebNotFound::withToken();
        }
    }

    /**
     * @throws RolIsNotValid
     */
    public function ofRecoveryTokenSelectorOrFail(ClearTextToken $selector): UserWeb
    {
        $qb = $this->queryBuilder->__invoke(
            [
                'recoveryTokenSelector' => $selector,
            ]
        );

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserWebNotFound::withToken();
        }
    }
}
