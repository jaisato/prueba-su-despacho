<?php

namespace App\Infrastructure\Persistence\Doctrine\ValueObject;

use App\Domain\ValueObject\Name;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class NameType extends StringType
{
    /** @var string */
    public const TYPE_NAME = 'vo_name';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof Name) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asString(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return Name::fromString(parent::convertToPHPValue($value, $platform));
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
