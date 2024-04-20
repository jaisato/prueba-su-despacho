<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\ValueObject\Security;

use App\Domain\ValueObject\Security\PasswordHash;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class PasswordHashType extends StringType
{
    /** @var string */
    public const TYPE_NAME = 'vo_password_hash';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof PasswordHash) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asString(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return PasswordHash::fromHash(parent::convertToPHPValue($value, $platform));
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
