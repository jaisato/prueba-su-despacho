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
use function preg_match;
use function serialize;
use function str_replace;
use function trim;
use function unserialize;

final class Amount
{
    public const FAKER_METHOD = 'Amount::zero()';

    private const CURRENCY = 'EUR';

    private const SCALE = 2;

    /** "6,50", "1234,5", "1.234,56": unambiguous Spanish notation, up to cents. */
    private const COMMA_AS_DECIMALS_FORMAT = '/^(?:\d{1,3}(?:\.\d{3}){1,3}|\d{1,12})(?:,\d{1,2})?$/';

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
                    RoundingMode::HalfUp
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
                RoundingMode::HalfUp
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
                // brick/money 0.14 (and brick/math 0.18 underneath) no longer
                // accept a float here; older versions cast it to string
                // internally, so do the same before handing it over.
                (string) $price,
                self::CURRENCY,
                new CustomContext(
                    self::SCALE
                ),
                RoundingMode::HalfUp
            )
        );
    }

    public function isZero(): bool
    {
        return $this->value->isZero();
    }

    /**
     * Spanish notation: comma for the decimals, optionally dots grouping the
     * thousands ("6,50", "1.234,56", "1234,5").
     *
     * The dots used to be stripped without looking at where they were, so a
     * price written the other way round - "6.50", which is the very example the
     * format error used to suggest - was read as 650 € instead of being
     * rejected, and a minus sign was accepted as a negative price. Anything that
     * is not unambiguous in this notation is now refused. At most two decimals
     * (cents; a third one used to be rounded away silently) and twelve integer
     * digits, which also keeps the serialised value well inside its column.
     *
     * @throws AmountIsNotValid
     */
    public static function fromStringWithCommaAsDecimals(string $price): self
    {
        $price = trim($price);

        if (preg_match(self::COMMA_AS_DECIMALS_FORMAT, $price) !== 1) {
            throw AmountIsNotValid::becauseItsFormatWithCommaAsDecimalsIsNotValid($price);
        }

        $price = str_replace('.', '', $price);
        $price = str_replace(',', '.', $price);

        return self::fromStringWithDecimals($price);
    }

    /**
     * This amount with a percentage added on top (a tax rate, for instance),
     * rounded half up to cents.
     *
     * Computed with decimals rather than floats: the float round trip used
     * before lost the cents of large amounts and, past 1e15, produced strings
     * such as "1.21E+20" that Money cannot parse.
     */
    public function withPercentageAdded(int $percentage): self
    {
        return new self(
            $this->value->multipliedBy(
                BigDecimal::of(100 + $percentage)->dividedBy(100, 2),
                RoundingMode::HalfUp
            )
        );
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
                RoundingMode::HalfUp
            )
        );
    }

    public function subtract(Amount $amount): self
    {
        return new self(
            $this->value->minus(
                $amount->value,
                RoundingMode::HalfUp
            )
        );
    }

    public function multiply(int $quantity): self
    {
        return new self(
            $this->value->multipliedBy(
                $quantity,
                RoundingMode::HalfUp
            )
        );
    }
}
