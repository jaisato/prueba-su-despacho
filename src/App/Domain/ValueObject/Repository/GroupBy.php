<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Repository;

use App\Domain\ValueObject\Repository\GroupBy\Field;
use InvalidArgumentException;

use function array_map;

final class GroupBy
{
    /** @var Field[] */
    private array $fields;

    private int $counter = 0;

    /**
     * @param Field[] $fields
     *
     * @throws InvalidArgumentException
     */
    private function __construct(array $fields = [])
    {
        $this->fields = array_map(
            function ($field) {
                $this->counter++;

                return $field;
            },
            $fields
        );
    }

    public static function fromArray(array $orderBy): self
    {
        $fields = [];

        foreach ($orderBy as $fieldName) {
            $fields[] = new Field($fieldName);
        }

        return new self($fields);
    }

    public static function fromArrayOrNull(?array $orderBy): ?self
    {
        if ($orderBy === null) {
            return null;
        }

        return self::fromArray($orderBy);
    }

    public function isEmpty(): bool
    {
        return $this->counter === 0;
    }

    public function fields(): array
    {
        return $this->fields;
    }
}
