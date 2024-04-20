<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject;

use function sprintf;

final class QuantityIsNotValid extends ValueObjectException
{
    public static function becauseItIsNegative(int $value): self
    {
        return new self(
            sprintf(
                'Quantity "%s" is negative',
                $value
            )
        );
    }

    public static function becauseItIsZero(): self
    {
        return new self(
            'Quantity is zero'
        );
    }
}
