<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Repository;

use App\Domain\Exception\ValueObject\Repository\LimitIsNotValid;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_diff;
use function array_keys;
use function count;

final class Limit
{
    private int $limit;

    private int $offset;

    private const VALID_KEYS = [
        'limit',
        'offset',
    ];

    private function __construct(int $limit, int $offset)
    {
        $this->limit  = $limit;
        $this->offset = $offset;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * @throws LimitIsNotValid
     */
    public static function fromArray(array $limit): self
    {
        if (count(array_diff(self::VALID_KEYS, array_keys($limit)))) {
            throw LimitIsNotValid::becauseKeysAreNotValid();
        }

        return new self(
            $limit['limit'],
            $limit['offset']
        );
    }

    /**
     * @throws LimitIsNotValid
     */
    public static function fromArrayOrNull(?array $limit): ?self
    {
        if ($limit === null) {
            return null;
        }

        return self::fromArray($limit);
    }

    public function asArray()
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }

    /**
     * @return Limit
     *
     * @throws LimitIsNotValid
     */
    public static function fromLimitAndOffset(int $limit, int $offset): self
    {
        self::validate($limit, $offset);

        return new self($limit, $offset);
    }

    /**
     * @throws LimitIsNotValid
     */
    public static function fromLimitAndOffsetNullable(?int $limit, ?int $offset): ?self
    {
        if ($limit === null || $offset === null) {
            return null;
        }

        return self::fromLimitAndOffset($limit, $offset);
    }

    public function hasOffset(): bool
    {
        return $this->offset !== null;
    }

    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    /**
     * @throws LimitIsNotValid
     */
    private static function validate(int $limit, int $offset): void
    {
        try {
            Assert::notSame($limit, 0);
        } catch (InvalidArgumentException $e) {
            throw LimitIsNotValid::becauseLimitIsZero();
        }

        try {
            Assert::greaterThan($limit, 0);
        } catch (InvalidArgumentException $e) {
            throw LimitIsNotValid::becauseLimitIsNegative($limit);
        }

        try {
            Assert::greaterThanEq($offset, 0);
        } catch (InvalidArgumentException $e) {
            throw LimitIsNotValid::becauseOffsetIsNegative($offset);
        }
    }
}
