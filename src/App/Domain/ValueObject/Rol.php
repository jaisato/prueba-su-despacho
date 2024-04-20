<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\ValueObject\RolIsNotValid;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function in_array;
use function trim;

class Rol
{
    public const ADMIN                    = 'ROLE_ADMINISTRATOR';
    public const WEB                      = 'ROLE_WEB';

    public const VALID_VALUES = [
        self::ADMIN,
        self::WEB,
    ];

    private const DESCRIPTION_MAP = [
        self::ADMIN => 'Administrador',
        self::WEB => 'Web',
    ];

    public const ROLES_GESTIONAR_USUARIOS_ADMINISTRADORES = [
        self::ADMIN,
    ];

    public const ROLES_GESTIONAR_USUARIOS_WEB = [
        self::ADMIN,
    ];

    public const ROLES_HIERARCHY = [
        self::ADMIN => [
            self::ADMIN,
            self::WEB,
        ],
        self::WEB => [],
    ];

    public const ROLE_ADMINISTRATOR_MAP = [
        self::ADMIN => [
            'value' => self::ADMIN,
            'text' => self::DESCRIPTION_MAP[self::ADMIN],
        ],
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws RolIsNotValid
     */
    public static function fromString(string $value): self
    {
        $value = trim($value);

        try {
            Assert::inArray($value, self::VALID_VALUES);
        } catch (InvalidArgumentException $e) {
            throw RolIsNotValid::becauseRolIsNotValid($value);
        }

        return new self($value);
    }

    /**
     * @throws RolIsNotValid
     */
    public static function fromStringOrNull(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::fromString($value);
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function web(): self
    {
        return new self(self::WEB);
    }

    public function equalsTo(Rol $anotherRol): bool
    {
        return $this->value === $anotherRol->value;
    }

    public function asString(): string
    {
        return $this->value;
    }

    public function description(): string
    {
        return self::DESCRIPTION_MAP[$this->value];
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return self::DESCRIPTION_MAP;
    }

    public static function rolesAdminsitrator(): array
    {
        return self::ROLE_ADMINISTRATOR_MAP;
    }

    /**
     * @return array<string>
     */
    public function getVisibleRoles(): array
    {
        return self::ROLES_HIERARCHY[$this->value];
    }

    public function puedeGestionarUsuariosAdministradores(): bool
    {
        return in_array($this->value, self::ROLES_GESTIONAR_USUARIOS_ADMINISTRADORES);
    }

    public function puedeGestionarUsuariosWeb(): bool
    {
        return in_array($this->value, self::ROLES_GESTIONAR_USUARIOS_WEB);
    }
}
