<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\AmountIsNotValid;
use App\Domain\ValueObject\Amount;
use PHPUnit\Framework\TestCase;

class AmountTest extends TestCase
{
    public function testSuccess(): void
    {
        $amount = '10';

        $amount = Amount::fromStringWithDecimals(
            $amount
        );

        $this->assertEquals(
            '10',
            $amount->asStringWithCommaAsDecimalSeparatorAndThousandSeparator()
        );
    }

    /**
     * @dataProvider validCommaAmounts
     */
    public function testCommaAsDecimals(string $price, string $expected): void
    {
        self::assertSame(
            $expected,
            Amount::fromStringWithCommaAsDecimals($price)->asStringWithDotAsDecimalSeparator()
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validCommaAmounts(): iterable
    {
        yield 'cents' => ['6,50', '6.50'];
        yield 'one decimal' => ['1234,5', '1234.50'];
        yield 'integer' => ['10', '10.00'];
        yield 'thousands' => ['1.234,56', '1234.56'];
        yield 'millions' => ['1.234.567', '1234567.00'];
        yield 'surrounding spaces' => [' 3,20 ', '3.20'];
    }

    /**
     * "6.50" was read as 650 (every dot was dropped as a thousands separator)
     * and "-5" as a negative price.
     *
     * @dataProvider invalidCommaAmounts
     */
    public function testCommaAsDecimalsRejectsAmbiguousOrInvalidInput(string $price): void
    {
        $this->expectException(AmountIsNotValid::class);

        Amount::fromStringWithCommaAsDecimals($price);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCommaAmounts(): iterable
    {
        yield 'dot as decimal separator' => ['6.50'];
        yield 'misplaced thousands dot' => ['12.34,5'];
        yield 'negative' => ['-5'];
        yield 'three decimals' => ['1,999'];
        yield 'empty' => [''];
        yield 'letters' => ['abc'];
        yield 'two commas' => ['1,2,3'];
        yield 'too many digits' => ['1234567890123'];
    }

    /**
     * @dataProvider percentages
     */
    public function testWithPercentageAdded(string $base, int $percentage, string $expected): void
    {
        self::assertSame(
            $expected,
            Amount::fromStringWithDecimals($base)->withPercentageAdded($percentage)->asStringWithDotAsDecimalSeparator()
        );
    }

    /**
     * @return iterable<string, array{string, int, string}>
     */
    public static function percentages(): iterable
    {
        yield '21 % rounds half up' => ['1.75', 21, '2.12'];
        yield '10 %' => ['0.05', 10, '0.06'];
        yield '4 %' => ['100', 4, '104.00'];
    }

    /**
     * The float round trip used before turned a large base price into
     * "1.21E+20", which Money cannot parse.
     */
    public function testWithPercentageAddedIsExactForLargeAmounts(): void
    {
        $amount = Amount::fromStringWithDecimals('100000000000000000000')->withPercentageAdded(21);

        self::assertTrue($amount->equalsTo(Amount::fromStringWithDecimals('121000000000000000000')));
    }

    public function testError(): void
    {
        $this->expectException(AmountIsNotValid::class);

        Amount::fromStringWithDecimals(
            '10,5'
        );
    }
}
