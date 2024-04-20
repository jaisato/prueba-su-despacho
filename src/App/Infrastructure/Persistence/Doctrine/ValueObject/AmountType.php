<?php

namespace App\Infrastructure\Persistence\Doctrine\ValueObject;

use App\Domain\ValueObject\Amount;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class AmountType extends StringType
{
    /** @var string */
    public const TYPE_NAME = 'vo_amount';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof Amount) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->serialize(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return Amount::fromSerialized(parent::convertToPHPValue($value, $platform));
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
