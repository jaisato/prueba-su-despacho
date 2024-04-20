<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\NameIsNotValid;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testSuccess(): void
    {
        $expectedValue = 'Testing';
        $name          = Name::fromString($expectedValue);

        $this->assertEquals(
            $expectedValue,
            $name->asString()
        );
    }

    public function testErrorEmpty(): void
    {
        $this->expectException(NameIsNotValid::class);

        Name::fromString('');
    }

    public function testErrorLength(): void
    {
        $this->expectException(NameIsNotValid::class);

        Name::fromString('OJkfMUKN1exK3imI5jvUu1TZu0VNpzcIJfRbwoSYJtuXc4B3ioUSUTrHkzEK17dnsXfvqyAVWXvuVM8WNdcvW4lLT4MIJhQhalm98pWTQaD8m93VTqOJbtHTJG6ZSJvE4xI4PvAqQB9LWUeQNjVWckOxVy4mh8HZ3p89x9BFXBtK7iZOkxl8xAjNMllRYaJ4L4ElsJJYUtL673rXNyiFOAuaZry8hPHZC40kC99ZKEpbTRsyfWz1UMCqhF4FMUAY');
    }
}
