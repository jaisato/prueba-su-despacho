<?php

declare(strict_types=1);

namespace Tests\App\Domain\Model\Product;

use App\Domain\Exception\ValueObject\AmountIsNotValid;
use App\Domain\Model\Product\Product;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Iva;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testPriceWithIvaIsRoundedFromTheExactAmount(): void
    {
        // Calculado en float, un importe de 13 cifras ya no conserva los
        // céntimos: daba 1493827147049.40.
        $product = $this->product(Amount::fromStringWithDecimals('1234567890123.45'), Iva::fromInt(21));

        self::assertSame('1493827147049.37', $product->priceWithIva()->asStringWithDotAsDecimalSeparator());
    }

    public function testANegativeBasePriceIsRejected(): void
    {
        $this->expectException(AmountIsNotValid::class);

        $this->product(Amount::fromStringWithCommaAsDecimals('-5,00'), Iva::fromInt(21));
    }

    private function product(Amount $basePrice, Iva $iva): Product
    {
        return Product::create(
            Id::fromString('0b0a3c4e-6a59-4f7e-9d0c-2a3f8c1e5b7d'),
            Name::fromString('Producto'),
            Description::fromString('Descripción del producto'),
            $basePrice,
            $iva
        );
    }
}
