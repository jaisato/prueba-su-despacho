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
}
