<?php

declare(strict_types=1);

namespace Tests\App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\DateTimeIsNotValid;
use App\Domain\ValueObject\DateTime;
use PHPUnit\Framework\TestCase;

use function date;

class DateTimeTest extends TestCase
{
    public function testSuccessFromDateTime(): void
    {
        $now = new \DateTime('now');

        $date = DateTime::createFromDateTime(
            $now
        );

        $this->assertEquals(
            $now->format('d'),
            $date->format('d')
        );

        $this->assertEquals(
            $now->format('m'),
            $date->format('m')
        );

        $this->assertEquals(
            $now->format('Y'),
            $date->format('Y')
        );

        $this->assertEquals(
            $now->format('h'),
            $date->format('h')
        );

        $this->assertEquals(
            $now->format('i'),
            $date->format('i')
        );

        $this->assertEquals(
            $now->format('s'),
            $date->format('s')
        );
    }

    public function testSuccessFromString(): void
    {
        $now = new \DateTime('now');

        $date = DateTime::createFromString(
            date('Y-m-d H:i:s'),
        );

        $this->assertEquals(
            $now->format('d'),
            $date->format('d')
        );

        $this->assertEquals(
            $now->format('m'),
            $date->format('m')
        );

        $this->assertEquals(
            $now->format('Y'),
            $date->format('Y')
        );

        $this->assertEquals(
            $now->format('h'),
            $date->format('h')
        );

        $this->assertEquals(
            $now->format('i'),
            $date->format('i')
        );

        $this->assertEquals(
            $now->format('s'),
            $date->format('s')
        );
    }

    public function testError(): void
    {
        $this->expectException(DateTimeIsNotValid::class);

        DateTime::createFromString(
            'testing'
        );

        DateTime::createFromString(
            false
        );

        DateTime::createFromDateAndTimeComponents(
            true,
            true,
            true,
            true,
            true,
            true
        );
    }
}
