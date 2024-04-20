<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Repository;

use Exception;

use function sprintf;

final class LimitIsNotValid extends Exception
{
    public static function becauseLimitIsNegative(int $limit): self
    {
        return new self(
            sprintf(
                'Limit "%s" is a negative number',
                $limit
            )
        );
    }

    public static function becauseLimitIsZero(): self
    {
        return new self(
            'Limit is zero'
        );
    }

    public static function becauseOffsetIsNegative(int $offset): self
    {
        return new self(
            sprintf(
                'Offset "%s" is a negative number',
                $offset
            )
        );
    }

    public static function becauseKeysAreNotValid(): self
    {
        return new self(
            'Limit keys are not valid'
        );
    }
}
