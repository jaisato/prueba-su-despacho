<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\ValueObject\Security\Token;

use App\Domain\ValueObject\Security\Token\ClearTextToken;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ClearTextTokenType extends StringType
{
    /** @var string */
    public const TYPE_NAME = 'vo_clear_text_token';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof ClearTextToken) {
            return $value;
        }

        return parent::convertToDatabaseValue($value->asString(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return ClearTextToken::fromString(parent::convertToPHPValue($value, $platform));
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
