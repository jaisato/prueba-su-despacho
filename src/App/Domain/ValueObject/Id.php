<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\IdIsNotValid;
use Ramsey\Uuid\Uuid;

final class Id
{
    public const FAKER_METHOD = 'Id::generate()';

    private string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function generate(): self
    {
        return new self(
            Uuid::uuid4()->toString()
        );
    }

    /**
     * @throws IdIsNotValid
     */
    public static function fromString(string $stringId): self
    {
        $stringId = trim($stringId);

        if ('' === $stringId) {
            throw IdIsNotValid::becauseStringIsEmpty();
        }

        if (!Uuid::isValid($stringId)) {
            throw IdIsNotValid::becauseStringIsInvalid($stringId);
        }

        return new self($stringId);
    }

    /**
     * @throws IdIsNotValid
     */
    public static function fromStringOrNull(?string $id): ?self
    {
        if ($id === null) {
            return null;
        }

        return self::fromString($id);
    }

    public function asString(): string
    {
        return $this->id;
    }

    public function equalsTo(Id $anotherId): bool
    {
        return $this->id === $anotherId->id;
    }

    public function __toString(): string
    {
        return $this->asString();
    }
}
