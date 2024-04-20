<?php

namespace App\Domain\Exception\ValueObject;

final class EmailAddressIsNotValid extends ValueObjectException
{
    public static function becauseItIsNotARealAddress(string $value): self
    {
        return new self(
            sprintf(
                'String "%s" is not a valid email address.',
                $value
            )
        );
    }

    public static function becauseItsEmpty(): self
    {
        return new self('Email address is empty');
    }
}
