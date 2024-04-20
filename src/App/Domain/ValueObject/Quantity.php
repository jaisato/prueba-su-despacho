<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\QuantityIsNotValid;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function number_format;

final class Quantity
{
    public const FAKER_METHOD = 'Quantity::fromString(self::faker()->randomNumber())';

    private const MIN_VALUE = 0;

    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @throws QuantityIsNotValid
     */
    public static function fromInt(int $value): self
    {
        self::validate($value);

        return new self($value);
    }

    /**
     * @throws QuantityIsNotValid
     */
    public static function fromIntOrNull(?int $value): ?self
    {
        if ($value === null) {
            return null;
        }

        self::validate($value);

        return new self($value);
    }

    public static function zero(): Quantity
    {
        return new self(self::MIN_VALUE);
    }

    public function add(Quantity $anotherQuantity): self
    {
        return new self(
            $this->value + $anotherQuantity->value
        );
    }

    public function subtract(Quantity $anotherQuantity): self
    {
        return new self(
            $this->value - $anotherQuantity->value
        );
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public function isHigherThanZero(): bool
    {
        return $this->value > 0;
    }

    public function multiplyBy(Quantity $anotherQuantity): self
    {
        return new self(
            $this->value * $anotherQuantity->value
        );
    }

    public function equalsTo(Quantity $anotherQuantity): bool
    {
        return $this->value === $anotherQuantity->value;
    }

    public function none(): bool
    {
        return $this->value === self::MIN_VALUE;
    }

    public function asInt(): int
    {
        return $this->value;
    }

    public function asString(): string
    {
        return (string) $this->value;
    }

    public function asStringWithThousandSeparator(): string
    {
        return number_format(
            $this->value,
            0,
            ',',
            '.'
        );
    }

    /**
     * @throws QuantityIsNotValid
     */
    private static function validate(int $value): void
    {
        try {
            Assert::greaterThanEq($value, self::MIN_VALUE);
        } catch (InvalidArgumentException $e) {
            throw QuantityIsNotValid::becauseItIsNegative($value);
        }
    }
}
