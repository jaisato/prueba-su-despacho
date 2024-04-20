<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\RolIsNotValid;
use App\Domain\ValueObject\Rol;
use PHPUnit\Framework\TestCase;

class RolTest extends TestCase
{
    public function testSuccess(): void
    {
        foreach (Rol::VALID_VALUES as $value) {
            $this->assertEquals(
                $value,
                Rol::fromString($value)->asString()
            );
        }
    }

    public function testErrorStringEmpty(): void
    {
        $this->expectException(RolIsNotValid::class);

        Rol::fromString('');
    }

    public function testErrorWrongFormat(): void
    {
        $this->expectException(RolIsNotValid::class);
        Rol::fromString('testing');
    }
}
