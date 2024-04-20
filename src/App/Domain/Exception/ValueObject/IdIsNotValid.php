<?php

namespace App\Domain\Exception\ValueObject;

final class IdIsNotValid extends ValueObjectException
{
    public static function becauseStringIsInvalid(string $idString): self
    {
        return new self(
            sprintf(
                'Id %s is not valid',
                $idString
            )
        );
    }

    public static function becauseStringIsEmpty(): self
    {
        return new self('Id is empty');
    }
}
