<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\User\UserAdministrator;

use App\Domain\Exception\Model\User\UserAdministrator\UserAdministratorAlreadyExists;
use App\Domain\Exception\Model\User\UserAdministrator\UserAdministratorNotFound;
use App\Domain\Model\User\UserAdministrator;
use App\Domain\Repository\Doctrine\User\UserAdministrator\UserAdministratorReadRepository as UserAdministratorReadRepositoryInterface;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Rol;
use App\Infrastructure\Persistence\Doctrine\ValueObject\EmailAddressType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

use function array_key_exists;
use function json_encode;

class UserAdministratorReadRepository implements UserAdministratorReadRepositoryInterface
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function ofIdAndActiveOrFail(Id $id): UserAdministrator
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user')
            ->from(UserAdministrator::class, 'user')
            ->where(
                'user.id = :id'
            )
            ->andWhere('user.deleted = :not_deleted')
            ->setParameter('id', $id)
            ->setParameter('not_deleted', Deleted::no(), DeletedType::TYPE_NAME);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserAdministratorNotFound::withId($id);
        }
    }

    public function ofEmailAddressAndFail(EmailAddress $emailAddress): void
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('COUNT(u)')
            ->from(UserAdministrator::class, 'u')
            ->where(
                'u.emailAddress = :email_address'
            )
            ->setParameter('email_address', $emailAddress, EmailAddressType::TYPE_NAME);

        $result = (int) $qb->getQuery()->getSingleScalarResult();

        if ($result === 0) {
            return;
        }

        throw UserAdministratorAlreadyExists::withEmailAddress($emailAddress);
    }

    public function ofEmailAddressOrFail(EmailAddress $emailAddress): UserAdministrator
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user_administrator')
            ->from(UserAdministrator::class, 'user_administrator')
            ->where(
                'user_administrator.emailAddress = :correo_electronico'
            )
            ->setParameter('correo_electronico', $emailAddress, EmailAddressType::TYPE_NAME);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserAdministratorNotFound::withEmailAddress($emailAddress);
        }
    }

    public function ofEmailAddressAndRolOrFail(EmailAddress $emailAddress, Rol $rol): UserAdministrator
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('user')
            ->from(UserAdministrator::class, 'user')
            ->where('user.emailAddress = :email_address')
            ->andWhere('JSON_CONTAINS(user.roles, :rol) = 1')
            ->setParameter('email_address', $emailAddress, EmailAddressType::TYPE_NAME)
            ->setParameter('rol', json_encode($rol->asString()));

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw UserAdministratorNotFound::withEmailAddressAndRol($emailAddress, $rol);
        }
    }

    public function countAll(
        array $filters = []
    ): Quantity {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('COUNT(user.id)')
            ->from(UserAdministrator::class, 'user');

        if (array_key_exists('not_id', $filters)) {
            $qb
                ->andWhere('user.id != :not_id')
                ->setParameter('not_id', $filters['not_id']);
        }

        if (array_key_exists('email_address', $filters)){
            $qb->andWhere('user.emailAddress = :email_address')
                ->setParameter(
                    'email_address',
                    $filters['email_address'],
                    EmailAddressType::TYPE_NAME
                );
        }

        return Quantity::fromInt(
            (int) $qb->getQuery()->getSingleScalarResult()
        );
    }
}
