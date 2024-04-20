<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Repository;

use App\Domain\ValueObject\Repository\OrderBy\Field;
use InvalidArgumentException;

use function array_map;
use function in_array;
use function is_string;
use function sprintf;
use function substr;

final class OrderBy
{
    public const DIRECTION_ASC  = 'ASC';
    public const DIRECTION_DESC = 'DESC';

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

    /**
     * @param array $fields
     *
     * @return OrderBy
     */
    public static function fromFieldsWithDirectionsAsValues(array $fields): self
    {
        $fieldsParsed = [];

        foreach ($fields as $fieldName => $direction) {
            if (! is_string($fieldName)) {
                $fieldsParsed[] = $direction;

                continue;
            }

            $fieldsParsed[] = new Field($fieldName, $direction);
        }

        return new self($fieldsParsed);
    }

    public static function fromFieldsWithDirectionsAsValuesOrNull(?array $fields): ?self
    {
        if ($fields === null && empty($fields)) {
            return null;
        }

        return self::fromFieldsWithDirectionsAsValues($fields);
    }

    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function fieldsContain(string $needle): bool
    {
        return in_array(
            $needle,
            array_map(static fn (Field $field) => $field->name(), $this->fields),
            true
        );
    }

    public function toArray(): array
    {
        $result = [];

        foreach ($this->fields as $field) {
            $result[$field->name()] = $field->direction();
        }

        return $result;
    }

    public static function fromArray(array $orderBy): self
    {
        $fields = [];

        foreach ($orderBy as $fieldName => $order) {
            $fields[] = new Field($fieldName, $order);
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

    public function rawSql(string $table): string
    {
        $s = '';
        foreach ($this->fields() as $field) {
            $s .= sprintf(' %s.%s %s, ', $table, $field->name(), $field->direction());
        }

        $s = substr($s, 0, -2); // Delete trailing ,

        return $s;
    }
}
