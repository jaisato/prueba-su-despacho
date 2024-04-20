<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject;

final class DescriptionIsNotValid extends ValueObjectException
{
    public static function becauseStringIsEmpty(): self
    {
        return new self('String is empty');
    }
}
