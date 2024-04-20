<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\QuantityIsNotValid;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

class QuantityTest extends TestCase
{
    public function testSuccess(): void
    {
        $expectedValue = 1;
        $name          = Quantity::fromInt(1);

        $this->assertEquals(
            $expectedValue,
            $name->asString()
        );
    }

    public function testErrorWrongFormat(): void
    {
        $this->expectException(QuantityIsNotValid::class);

        Quantity::fromInt(-1);
    }
}
