<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject;

use function sprintf;

final class AmountIsNotValid extends ValueObjectException
{
    public static function becauseItsFormatIsNotValid(string $price): self
    {
        return new self(
            sprintf(
                'El formato del precio recibido, "%s", no es válido. Tiene que ser, por ejemplo, "6.50".',
                $price
            )
        );
    }
}
