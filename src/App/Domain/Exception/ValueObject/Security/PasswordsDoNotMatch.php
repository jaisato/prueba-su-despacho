<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Security;

use Exception;

final class PasswordsDoNotMatch extends Exception
{
    public static function withDifferentValues(): self
    {
        return new self('La contraseña insertada no coincide con la contraseña actual');
    }

    public static function withRepeatDifferentValues(): self
    {
        return new self('La contraseña y su verificacion no coinciden');
    }
}
