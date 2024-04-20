<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collection\ValueObject;

use App\Domain\Collection\ValueObject\RolCollection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class RolCollectionType extends JsonType
{
    public const TYPE_NAME = 'vo_rol_collection';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (! $value instanceof RolCollection) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asArray(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return RolCollection::fromArray(parent::convertToPHPValue($value, $platform));
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
