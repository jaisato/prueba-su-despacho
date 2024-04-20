<?php

namespace App\Infrastructure\Persistence\Doctrine\ValueObject;

use App\Domain\ValueObject\EmailAddress;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class EmailAddressType extends StringType
{
    /** @var string */
    public const TYPE_NAME = 'vo_email_address';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof EmailAddress) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asString(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return EmailAddress::fromString(parent::convertToPHPValue($value, $platform));
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }
}