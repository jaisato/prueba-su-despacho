<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\ValueObject\EmailAddress;
use PHPUnit\Framework\TestCase;

class EmailAddressTest extends TestCase
{
    public function testSuccess(): void
    {
        $expectedValue = 'testing@gmail.com';
        $emailAddress  = EmailAddress::fromString($expectedValue);

        $this->assertEquals(
            $expectedValue,
            $emailAddress->asString()
        );
    }

    public function testErrorEmpty(): void
    {
        $this->expectException(EmailAddressIsNotValid::class);

        EmailAddress::fromString('');
    }

// Temporal comment
//    public function testErrorValidAddress()
//    {
//        $this->expectException(EmailAddressIsNotValid::class);
//
//        EmailAddress::fromString('@john.doe@example.com');
//    }
}
