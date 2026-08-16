<?php
declare(strict_types=1);

namespace App\Domain\ValueObject\Security;

use App\Domain\Exception\ValueObject\Security\ClearTextPasswordIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordHashIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordsDoNotMatch;
use Webmozart\Assert\Assert;

use function hash_equals;
use function random_int;
use function strlen;

final class ClearTextPassword
{
    public const MIN_PASSWORD_LENGTH = 6;

    /**
     * Tope de longitud de la contraseña.
     *
     * Argon2id es deliberadamente costoso en tiempo y memoria, así que su coste
     * crece con la entrada. Sin un límite, una petición de registro con una
     * contraseña de varios megabytes obliga al servidor a derivarla entera: es
     * una vía barata para agotar CPU y memoria desde un endpoint sin
     * autenticar.
     */
    public const MAX_PASSWORD_LENGTH = 4096;

    private const RANDOM_PASSWORD_LENGTH = 16;
    private const RANDOM_PASSWORD_CHARACTER_LIST = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';

    private string $password;

    private function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * @throws ClearTextPasswordIsNotValid
     */
    public static function fromString(string $password): self
    {
        $password = trim($password);

        self::validate($password);

        return new self($password);
    }

    /**
     * Genera una contraseña aleatoria.
     *
     * Usaba `RandomLib::getMediumStrengthGenerator()`. La propia librería
     * describe el generador "medium" como no apto para material criptográfico
     * -mezcla fuentes que no son un CSPRNG- y lo reserva para usos donde la
     * previsibilidad no importa; una contraseña no es uno de ellos. Además
     * `ircmaxell/random-lib` está sin mantenimiento y entraba con la restricción
     * `"*"`, así que cualquier `composer update` podía traer cualquier versión.
     *
     * `random_int()` es el CSPRNG del propio PHP desde la 7.0 y lanza si no hay
     * una fuente de entropía suficiente, en lugar de degradarse en silencio.
     */
    public static function generate(): self
    {
        $alphabet = self::RANDOM_PASSWORD_CHARACTER_LIST;
        $maxIndex = strlen($alphabet) - 1;
        $password = '';

        for ($i = 0; $i < self::RANDOM_PASSWORD_LENGTH; $i++) {
            $password .= $alphabet[random_int(0, $maxIndex)];
        }

        return new self($password);
    }

    /**
     * @throws PasswordHashIsNotValid
     */
    public function makeHash(): PasswordHash
    {
        $hash = password_hash(
            $this->password,
            PasswordHash::PASSWORD_ALGORITHM
        );

        if (false === $hash) {
            throw PasswordHashIsNotValid::becauseItIsNotARealHash('');
        }

        return PasswordHash::fromHash(
            $hash
        );
    }

    public function matches(PasswordHash $hash): bool
    {
        return password_verify(
            $this->password,
            $hash->asString()
        );
    }

    public function asString(): string
    {
        return $this->password;
    }

    /**
     * @throws ClearTextPasswordIsNotValid
     */
    private static function validate(string $password): void
    {
        try {
            Assert::stringNotEmpty($password);
        } catch (\InvalidArgumentException $e) {
            throw ClearTextPasswordIsNotValid::becauseStringIsEmpty();
        }

        try {
            Assert::minLength($password, self::MIN_PASSWORD_LENGTH);
        } catch (\InvalidArgumentException $e) {
            throw ClearTextPasswordIsNotValid::becauseDoesNotHaveMinimalLength($password);
        }

        try {
            Assert::maxLength($password, self::MAX_PASSWORD_LENGTH);
        } catch (\InvalidArgumentException $e) {
            throw ClearTextPasswordIsNotValid::becauseExceedsMaximumLength();
        }
    }

    /**
     * Comprueba que dos contraseñas escritas por el usuario coinciden.
     *
     * Antes esto derivaba un hash Argon2id de la segunda y verificaba la primera
     * contra él. Argon2id es caro a propósito -tiempo y memoria-, así que se
     * pagaba un hash completo más una verificación sólo para comparar dos
     * cadenas que ya se tienen en claro delante. En el formulario de registro
     * eso multiplica el coste de cada petición sin autenticar, que es
     * justamente lo que un atacante quiere.
     *
     * `hash_equals()` compara en tiempo constante, que es todo lo que hace
     * falta: aquí no hay ningún secreto almacenado del que fugar información,
     * sólo dos valores que el propio usuario acaba de enviar.
     *
     * @throws PasswordsDoNotMatch
     * @throws ClearTextPasswordIsNotValid
     */
    public static function passwordsMatch(
        string $passwordOne,
        string $passwordTwo
    ): void {
        // Se siguen construyendo los dos objetos para que una contraseña que no
        // cumple las reglas se rechace como tal y no como "no coinciden".
        $one = self::fromString($passwordOne);
        $two = self::fromString($passwordTwo);

        if (! hash_equals($one->asString(), $two->asString())) {
            throw PasswordsDoNotMatch::withDifferentValues();
        }
    }
}
