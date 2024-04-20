<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\ValueObject\Security\Token;

use App\Domain\ValueObject\Security\Token\TokenHash;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class TokenHashType extends StringType
{
    /** @var string */
    public const TYPE_NAME = 'vo_token_hash';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof TokenHash) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asString(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return TokenHash::fromHash(parent::convertToPHPValue($value, $platform));
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
