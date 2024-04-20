<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject;

use App\Domain\ValueObject\Name;

use function sprintf;

final class NameIsNotValid extends ValueObjectException
{
    public static function becauseStringLengthIsLargerThanLimit(): self
    {
        return new self(
            sprintf(
                'String is too large. Limit is "%s"',
                Name::MAX_LENGTH
            )
        );
    }

    public static function becauseStringIsEmpty(): self
    {
        return new self('String is empty');
    }
}
