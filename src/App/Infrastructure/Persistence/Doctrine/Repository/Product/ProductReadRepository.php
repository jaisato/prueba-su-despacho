<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Product;

use App\Domain\Exception\Model\Product\ProductNotFound;
use App\Domain\Model\Product\Product;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;
use App\Infrastructure\Persistence\Doctrine\ValueObject\NameType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class ProductReadRepository implements \App\Domain\Repository\Doctrine\Product\ProductReadRepository
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function ofIdOrFail(Id $id): Product
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('product')
            ->from(Product::class, 'product')
            ->where(
                'product.id = :id'
            )
            ->setParameter('id', $id);

        try {
            return $qb
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw ProductNotFound::withId($id);
        }
    }

    public function all(array $filters = [], ?Limit $limit = null, ?OrderBy $orderBy = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('product')
            ->from(Product::class, 'product');

        $this->applyFilters($qb, $filters);

        if ($limit !== null && $limit->hasLimit()) {
            $qb->setMaxResults($limit->limit());
        }

        if ($limit !== null && $limit->hasOffset()) {
            $qb->setFirstResult($limit->offset());
        }

        if ($orderBy !== null && ! $orderBy->isEmpty()) {
            foreach ($orderBy->fields() as $field) {
                $qb->addOrderBy(
                    sprintf('product.%s', $field->name()),
                    $field->direction()
                );
            }
        } else {
            $qb->addOrderBy('product.updatedOn', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function countAll(array $filters = []): Quantity
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb
            ->select('COUNT(product.id)')
            ->from(Product::class, 'product');

        $this->applyFilters($qb, $filters);

        return Quantity::fromInt(
            (int) $qb->getQuery()->getSingleScalarResult()
        );
    }

    /**
     * One place for the filters, shared by all() and countAll().
     *
     * all() used to chain them with where(), which replaces the previous
     * condition instead of adding to it (so `name` silently dropped `id`), and
     * countAll() applied a different set (no `id`, `not_id` even when empty).
     * The total in the pagination block could therefore disagree with the rows
     * actually returned.
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        if (array_key_exists('id', $filters)) {
            $qb
                ->andWhere('product.id = :id')
                ->setParameter('id', $filters['id']);
        }

        if (array_key_exists('not_id', $filters) && ! empty($filters['not_id'])) {
            $qb
                ->andWhere('product.id != :not_id')
                ->setParameter('not_id', $filters['not_id']);
        }

        if (array_key_exists('name', $filters)) {
            // The search text is matched literally: `%` and `_` in it used to
            // act as LIKE wildcards, so `?name=%` listed every product and
            // `?name=_` anything with a name. `!` is the escape character
            // because, unlike the backslash, it means the same on every
            // platform (SQLite has no default escape at all).
            $qb
                ->andWhere("product.name LIKE :name ESCAPE '!'")
                ->setParameter(
                    'name',
                    '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], (string) $filters['name']) . '%',
                    NameType::TYPE_NAME
                );
        }
    }
}
