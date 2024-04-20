<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\QueryBuilder\User;

use App\Domain\Exception\ValueObject\RolIsNotValid;
use App\Domain\Model\User\User;
use App\Domain\ValueObject\DateTime;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;
use App\Domain\ValueObject\Rol;
use App\Infrastructure\Persistence\Doctrine\ValueObject\DateTimeType;
use App\Infrastructure\Persistence\Doctrine\ValueObject\Security\Token\ClearTextTokenType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

use function array_key_exists;
use function json_encode;
use function sprintf;

class UserQueryBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws RolIsNotValid
     */
    public function __invoke(
        array $filters = [],
        ?Limit $limit = null,
        ?OrderBy $orderBy = null,
    ): QueryBuilder {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('user')
            ->from(User::class, 'user');

        if (array_key_exists('id', $filters)) {
            $qb
                ->andWhere('user.id = :id')
                ->setParameter('id', $filters['id']);
        }

        if (array_key_exists('name', $filters)) {
            $qb
                ->andWhere('user.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (array_key_exists('emailAddress', $filters)) {
            $qb
                ->andWhere('user.emailAddress LIKE :emailAddress')
                ->setParameter('emailAddress', '%' . $filters['emailAddress'] . '%');
        }

        if (array_key_exists('role', $filters)) {
            $role = Rol::fromString($filters['role']);

            $qb->andWhere('JSON_CONTAINS(user.roles, :role) = 1')
                ->setParameter('role', json_encode($role->asString()));
        }


        if (array_key_exists('activationTokenSelector', $filters)) {
            $qb
                ->andWhere('user.activationToken.selector = :activationTokenSelector ')
                ->setParameter(
                    'activationTokenSelector',
                    $filters['activationTokenSelector'],
                    ClearTextTokenType::TYPE_NAME
                );
        }

        if (array_key_exists('recoveryTokenSelector', $filters)) {
            $qb
                ->andWhere('user.recoveryToken.selector = :recoveryTokenSelector ')
                ->andWhere('user.recoveryToken.validUntil > :now')
                ->setParameter(
                    'recoveryTokenSelector',
                    $filters['recoveryTokenSelector'],
                    ClearTextTokenType::TYPE_NAME
                )
                ->setParameter(
                    'now',
                    DateTime::now(),
                    DateTimeType::TYPE_NAME
                );
        }

        if ($limit !== null && $limit->hasLimit()) {
            $qb->setMaxResults($limit->limit());
        }

        if ($limit !== null && $limit->hasOffset()) {
            $qb->setFirstResult($limit->offset());
        }

        if ($orderBy !== null && ! $orderBy->isEmpty()) {
            foreach ($orderBy->fields() as $field) {
                $qb->addOrderBy(
                    sprintf('user.%s', $field->name()),
                    $field->direction()
                );
            }
        } else {
            $qb->addOrderBy('user.updatedOn', 'DESC');
        }

        return $qb;
    }
}
