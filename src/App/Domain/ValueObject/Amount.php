<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\AmountIsNotValid;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\RoundingMode;
use Brick\Money\Context\CustomContext;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Money;

use function number_format;
use function serialize;
use function str_replace;
use function unserialize;

final class Amount
{
    public const FAKER_METHOD = 'Amount::zero()';

    private const CURRENCY = 'EUR';

    private const SCALE = 2;

    private Money $value;

    private function __construct(Money $value)
    {
        $this->value = $value;
    }

    /**
     * @throws AmountIsNotValid
     */
    public static function fromStringWithDecimals(string $price): self
    {
        try {
            return new self(
                Money::of(
                    $price,
                    self::CURRENCY,
                    new CustomContext(
                        self::SCALE
                    ),
                    RoundingMode::HALF_UP
                )
            );
        } catch (NumberFormatException $e) {
            throw AmountIsNotValid::becauseItsFormatIsNotValid($price);
        }
    }

    public static function zero(): self
    {
        return new self(
            Money::of(
                0,
                self::CURRENCY,
                new CustomContext(
                    self::SCALE
                ),
                RoundingMode::HALF_UP
            )
        );
    }

    /**
     * @param float $price
     *
     * @return self
     *
     * @throws NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public static function fromFloatWithDecimals(float $price): self
    {
        return new self(
            Money::of(
                $price,
                self::CURRENCY,
                new CustomContext(
                    self::SCALE
                ),
                RoundingMode::HALF_UP
            )
        );
    }

    public function isZero(): bool
    {
        return $this->value->isZero();
    }

    /**
     * @throws AmountIsNotValid
     */
    public static function fromStringWithCommaAsDecimals(string $price): self
    {
        $price = str_replace('.', '', $price);
        $price = str_replace(',', '.', $price);

        return self::fromStringWithDecimals($price);
    }

    public static function fromSerialized(string $serialized): self
    {
        return new self(
            Money::of(
                unserialize(
                    $serialized,
                    // The second argument to unserialize() is an options array,
                    // and the class allowlist lives under the 'allowed_classes'
                    // key. This passed a bare list instead - [0 => BigDecimal],
                    // which has no 'allowed_classes' key at all. PHP does not
                    // warn about the unknown option; it simply falls back to the
                    // default, and that default is `true`: every class allowed.
                    // So the restriction that was clearly intended here was never
                    // in force, and any class in the application could be
                    // instantiated - __wakeup() and __destruct() included.
                    //
                    // That matters because this is the read side of the
                    // vo_amount Doctrine type: it runs on every Amount column
                    // loaded from the database, so anything that can write to
                    // one of those columns gets a PHP object injection.
                    [
                        'allowed_classes' => [
                            BigDecimal::class,
                        ],
                    ]
                ),
                self::CURRENCY,
                new CustomContext(
                    self::SCALE
                )
            )
        );
    }

    public function serialize(): string
    {
        return serialize($this->value->getAmount());
    }

    public function asString(): string
    {
        return number_format(
            $this->value->getAmount()->toFloat(),
            2,
            ',',
            '.'
        );
    }

    public function asFloat(): float
    {
        return $this->value->getAmount()->toFloat();
    }

    public function asStringWithoutSeparators(): string
    {
        return number_format(
            $this->value->getAmount()->toFloat(),
            2,
            '',
            ''
        );
    }

    public function asStringWithDotAsDecimalSeparator(): string
    {
        return number_format(
            $this->value->getAmount()->toFloat(),
            2,
            '.',
            ''
        );
    }

    public function asStringWithCommaAsDecimalSeparator(): string
    {
        return number_format(
            $this->value->getAmount()->toFloat(),
            2,
            ',',
            ''
        );
    }

    public function asStringWithCommaAsDecimalSeparatorAndThousandSeparator(): string
    {
        return str_replace(',00', '', number_format(
            $this->value->getAmount()->toFloat() + 0,
            2,
            ',',
            '.'
        ));
    }

    public function equalsTo(Amount $anotherPrice): bool
    {
        try {
            return $this->value->isEqualTo($anotherPrice->value);
        } catch (MoneyMismatchException $e) {
            return false;
        }
    }

    public function add(Amount $amount): self
    {
        return new self(
            $this->value->plus(
                $amount->value,
                RoundingMode::HALF_UP
            )
        );
    }

    public function subtract(Amount $amount): self
    {
        return new self(
            $this->value->minus(
                $amount->value,
                RoundingMode::HALF_UP
            )
        );
    }

    public function multiply(int $quantity): self
    {
        return new self(
            $this->value->multipliedBy(
                $quantity,
                RoundingMode::HALF_UP
            )
        );
    }
}
