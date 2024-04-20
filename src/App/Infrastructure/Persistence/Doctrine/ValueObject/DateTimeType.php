<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\ValueObject;

use App\Domain\ValueObject\DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;

final class DateTimeType extends DateTimeImmutableType
{
    public const TYPE_NAME = 'vo_date_time';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (! $value instanceof DateTime) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asDateTime(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return DateTime::createFromDateTime(parent::convertToPHPValue($value, $platform));
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
