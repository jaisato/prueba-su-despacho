<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory\User;

use App\Domain\Collection\ValueObject\RolCollection;
use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\Exception\ValueObject\NameIsNotValid;
use App\Domain\Exception\ValueObject\RolIsNotValid;
use App\Domain\Model\User\UserAdministrator;
use App\Domain\ValueObject\DateTime;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Rol;
use App\Domain\ValueObject\Security\PasswordHash;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static UserAdministrator|Proxy createOne(array $attributes = [])
 * @method static UserAdministrator[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static UserAdministrator|Proxy find($criteria)
 * @method static UserAdministrator|Proxy findOrCreate(array $attributes)
 * @method static UserAdministrator|Proxy first(string $sortedField = 'id')
 * @method static UserAdministrator|Proxy last(string $sortedField = 'id')
 * @method static UserAdministrator|Proxy random(array $attributes = [])
 * @method static UserAdministrator|Proxy randomOrCreate(array $attributes = [])
 * @method static UserAdministrator[]|Proxy[] all()
 * @method static UserAdministrator[]|Proxy[] findBy(array $attributes)
 * @method static UserAdministrator[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static UserAdministrator[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method UserAdministrator|Proxy create($attributes = [])
 */
final class UserAdministratorFactory extends ModelFactory
{
    /**
     * @throws RolIsNotValid
     * @throws NameIsNotValid
     * @throws EmailAddressIsNotValid
     */
    protected function getDefaults(): array
    {
        return [
            'id' => Id::generate(),
            'name' => Name::fromString(self::faker()->name()),
            'emailAddress' => EmailAddress::fromString(
                self::faker()->word() .
                '@' .
                self::faker()->word .
                '.' .
                self::faker()->word
            ),
            'password' => PasswordHash::fromString('123456789'),
            'roles' => RolCollection::fromElements([Rol::admin()]),
            'lastPasswordUpdate' => DateTime::now(),
        ];
    }

    protected function initialize(): self
    {
        return $this->afterInstantiate(
            static function (UserAdministrator $user): void {
            }
        );
    }

    protected static function getClass(): string
    {
        return UserAdministrator::class;
    }
}
