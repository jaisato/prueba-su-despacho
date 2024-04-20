<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;

use function filter_var;
use function trim;

use const FILTER_VALIDATE_EMAIL;

final class EmailAddress
{
    public const FAKER_METHOD = 'EmailAddress::fromString(self::faker()->email())';

    private string $emailAddress;

    private function __construct(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    public static function fromString(string $emailAddress): self
    {
        $emailAddress = trim($emailAddress);

        self::validate($emailAddress);

        return new self(
            $emailAddress
        );
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    public static function fromStringOrNull(?string $emailAddress): ?self
    {
        if ($emailAddress === null) {
            return null;
        }

        $emailAddress = trim($emailAddress);

        self::validate($emailAddress);

        return new self(
            $emailAddress
        );
    }

    public function asString(): string
    {
        return $this->emailAddress;
    }

    public function equalsTo(EmailAddress $anotherEmailAddress): bool
    {
        return $this->emailAddress === $anotherEmailAddress->emailAddress;
    }

    /**
     * @throws EmailAddressIsNotValid
     */
    private static function validate(string $emailAddress): void
    {
        if ($emailAddress === '') {
            throw EmailAddressIsNotValid::becauseItsEmpty();
        }

        if (! filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw EmailAddressIsNotValid::becauseItIsNotARealAddress($emailAddress);
        }
    }
}
