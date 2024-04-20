<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\DescriptionIsNotValid;
use App\Domain\ValueObject\Description;
use PHPUnit\Framework\TestCase;

class DescriptionTest extends TestCase
{
    public function testSuccess(): void
    {
        $expectedValue = 'testing description';
        $description   = Description::fromString($expectedValue);

        $this->assertEquals(
            $expectedValue,
            $description->asString()
        );
    }

    public function testErrorEmpty(): void
    {
        $this->expectException(DescriptionIsNotValid::class);

        Description::fromString('');
    }
}
