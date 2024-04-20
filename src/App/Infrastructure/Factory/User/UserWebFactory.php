<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory\User;

use App\Domain\Collection\ValueObject\RolCollection;
use App\Domain\Exception\ValueObject\DateIsNotValid;
use App\Domain\Exception\ValueObject\DateTimeIsNotValid;
use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\Exception\ValueObject\NameIsNotValid;
use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Rol;
use App\Domain\ValueObject\Security\PasswordHash;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static UserWeb|Proxy createOne(array $attributes = [])
 * @method static UserWeb[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static UserWeb|Proxy find($criteria)
 * @method static UserWeb|Proxy findOrCreate(array $attributes)
 * @method static UserWeb|Proxy first(string $sortedField = 'id')
 * @method static UserWeb|Proxy last(string $sortedField = 'id')
 * @method static UserWeb|Proxy random(array $attributes = [])
 * @method static UserWeb|Proxy randomOrCreate(array $attributes = [])
 * @method static UserWeb[]|Proxy[] all()
 * @method static UserWeb[]|Proxy[] findBy(array $attributes)
 * @method static UserWeb[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static UserWeb[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method UserWeb|Proxy create($attributes = [])
 */
final class UserWebFactory extends ModelFactory
{
    /**
     * @throws NameIsNotValid
     * @throws DateTimeIsNotValid
     * @throws EmailAddressIsNotValid
     * @throws DateIsNotValid
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
            'roles' => RolCollection::fromElements([Rol::web()]),
        ];
    }

    protected function initialize(): self
    {
         return $this->afterInstantiate(
             static function (UserWeb $userWeb): void {
             }
         );
    }

    protected static function getClass(): string
    {
        return UserWeb::class;
    }
}
