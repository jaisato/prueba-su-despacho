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

    public function testError(): void
    {
        $this->expectException(AmountIsNotValid::class);

        Amount::fromStringWithDecimals(
            '10,5'
        );
    }

    /**
     * @dataProvider commaFormatted
     */
    public function testCommaDecimals(string $input, string $expected): void
    {
        self::assertSame(
            $expected,
            Amount::fromStringWithCommaAsDecimals($input)->asStringWithDotAsDecimalSeparator()
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function commaFormatted(): array
    {
        return [
            'entero' => ['1234', '1234.00'],
            'decimales' => ['1,77', '1.77'],
            'millares y decimales' => ['1.234,50', '1234.50'],
            'varios millares' => ['1.234.567', '1234567.00'],
        ];
    }

    /**
     * Un punto que no separa millares no se puede quitar sin más: "1.77" se
     * guardaba como 177.
     *
     * @dataProvider notCommaFormatted
     */
    public function testCommaDecimalsRejectsAmbiguousDots(string $input): void
    {
        $this->expectException(AmountIsNotValid::class);

        Amount::fromStringWithCommaAsDecimals($input);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function notCommaFormatted(): array
    {
        return [
            'punto decimal' => ['1.77'],
            'punto con una cifra' => ['10.5'],
            'grupo incompleto' => ['12.34.567'],
            'vacío' => [''],
            'texto' => ['diez'],
        ];
    }

    /**
     * @dataProvider percentages
     */
    public function testIncreasedByPercentageRoundsTheExactResult(string $base, int $percentage, string $expected): void
    {
        self::assertSame(
            $expected,
            Amount::fromStringWithDecimals($base)->increasedByPercentage($percentage)->asStringWithDotAsDecimalSeparator()
        );
    }

    /**
     * @return array<string, array{string, int, string}>
     */
    public static function percentages(): array
    {
        return [
            'sin redondeo' => ['100.00', 21, '121.00'],
            'medio céntimo, 10 %' => ['10.05', 10, '11.06'],
            'medio céntimo, 21 %' => ['2.50', 21, '3.03'],
            'cero' => ['0', 4, '0.00'],
            'más allá del double' => ['1234567890123.45', 21, '1493827147049.37'],
        ];
    }
}
