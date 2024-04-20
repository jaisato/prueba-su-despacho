<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\NameIsNotValid;

use function mb_strlen;
use function trim;

final class Name
{
    public const FAKER_METHOD = 'Name::fromString(self::faker()->name())';

    public const MAX_LENGTH = 255;

    private string $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @throws NameIsNotValid
     */
    public static function fromString(string $name): self
    {
        $name = trim($name);

        self::validate($name);

        return new self(
            $name
        );
    }

    /**
     * @throws NameIsNotValid
     */
    public static function fromStringOrNull(?string $name): ?self
    {
        if ($name === null) {
            return null;
        }

        $name = trim($name);

        self::validate($name);

        return new self(
            $name
        );
    }

    public function asString(): string
    {
        return $this->name;
    }

    public function equalsTo(Name $anotherName): bool
    {
        return $this->name === $anotherName->name;
    }

    /**
     * @throws NameIsNotValid
     */
    private static function validate(string $name): void
    {
        if ($name === '') {
            throw NameIsNotValid::becauseStringIsEmpty();
        }

        if (mb_strlen($name) > self::MAX_LENGTH) {
            throw NameIsNotValid::becauseStringLengthIsLargerThanLimit();
        }
    }
}
