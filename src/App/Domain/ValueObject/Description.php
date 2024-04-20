<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\DescriptionIsNotValid;

use function trim;

final class Description
{
    public const FAKER_METHOD = 'Description::fromString(self::faker()->realText(1024))';

    private string $description;

    private function __construct(string $description)
    {
        $this->description = $description;
    }

    /**
     * @throws DescriptionIsNotValid
     */
    public static function fromString(string $description): self
    {
        $description = trim($description);

        self::validate($description);

        return new self(
            $description
        );
    }

    /**
     * @throws DescriptionIsNotValid
     */
    public static function fromStringOrNull(?string $description): ?self
    {
        if ($description === null) {
            return null;
        }

        $description = trim($description);

        self::validate($description);

        return new self(
            $description
        );
    }

    public function asString(): string
    {
        return $this->description;
    }

    public function equalsTo(Description $anotherDescription): bool
    {
        return $this->description === $anotherDescription->description;
    }

    /**
     * @throws DescriptionIsNotValid
     */
    private static function validate(string $description): void
    {
        if ($description === '') {
            throw DescriptionIsNotValid::becauseStringIsEmpty();
        }
    }
}
