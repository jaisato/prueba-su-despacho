<?php

namespace App\Infrastructure\Persistence\Doctrine\ValueObject;

use App\Domain\ValueObject\Iva;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

final class IvaType extends IntegerType
{
    /** @var string */
    public const TYPE_NAME = 'vo_iva';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof Iva) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asInt(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return Iva::fromInt(parent::convertToPHPValue($value, $platform));
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
