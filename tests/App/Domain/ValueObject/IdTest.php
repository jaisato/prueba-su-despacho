<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\IdIsNotValid;
use App\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class IdTest extends TestCase
{
    /**
     * @throws IdIsNotValid
     */
    public function testSuccess(): void
    {
        $expectedValue = Uuid::uuid4()->toString();
        $id            = Id::fromString($expectedValue);

        $this->assertEquals(
            $expectedValue,
            $id->asString()
        );
    }

    public function testError(): void
    {
        $this->expectException(IdIsNotValid::class);

        Id::fromString('');
    }

    public function testErrorWrongFormat(): void
    {
        $this->expectException(IdIsNotValid::class);
        Id::fromString('testing');
    }
}
