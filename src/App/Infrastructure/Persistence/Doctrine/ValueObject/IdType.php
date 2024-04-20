<?php

namespace App\Infrastructure\Persistence\Doctrine\ValueObject;

use App\Domain\ValueObject\Id;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Ramsey\Uuid\Doctrine\UuidType;

final class IdType extends UuidType
{
    /** @var string */
    public const TYPE_NAME = 'vo_id';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $id = parent::convertToPHPValue($value, $platform);

        return $id !== null ? Id::fromString($id->toString()) : null;
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