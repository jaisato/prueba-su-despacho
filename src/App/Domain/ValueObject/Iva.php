<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\IvaIsNotValid;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Iva
{
    private const VALUE_4 = 4;

    private const VALUE_10 = 10;

    private const VALUE_21 = 21;

    private static array $validValues = [
        self::VALUE_4,
        self::VALUE_10,
        self::VALUE_21,
    ];

    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @throws IvaIsNotValid
     */
    public static function fromInt(int $value): self
    {
        self::validate($value);

        return new self($value);
    }

    public function isHigherThanZero(): bool
    {
        return $this->value > 0;
    }

    public function equalsTo(Iva $anotherQuantity): bool
    {
        return $this->value === $anotherQuantity->value;
    }

    public function asInt(): int
    {
        return $this->value;
    }

    public function asString(): string
    {
        return (string) $this->value . '%';
    }

    /**
     * @throws IvaIsNotValid
     */
    private static function validate(int $value): void
    {
        try {
            Assert::inArray($value, self::$validValues);
        } catch (InvalidArgumentException $e) {
            throw new IvaIsNotValid("IVA with value {$value} is not valid");
        }
    }
}
