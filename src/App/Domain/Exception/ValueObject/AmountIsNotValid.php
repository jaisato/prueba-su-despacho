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

    /**
     * Para los importes que se escriben con coma decimal ("1.234,50").
     *
     * El mensaje de arriba propone "6.50", que es justo lo que ese formato
     * lee como seiscientos cincuenta.
     */
    public static function becauseItsCommaFormatIsNotValid(string $price): self
    {
        return new self(
            sprintf(
                'El formato del precio recibido, "%s", no es válido. Tiene que ser, por ejemplo, "6,50" o "1.234,50".',
                $price
            )
        );
    }

    public static function becauseItIsNegative(string $price): self
    {
        return new self(
            sprintf('El precio no puede ser negativo: "%s".', $price)
        );
    }
}
