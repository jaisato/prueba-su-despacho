<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory\Product;

use App\Domain\Exception\ValueObject\AmountIsNotValid;
use App\Domain\Exception\ValueObject\DescriptionIsNotValid;
use App\Domain\Exception\ValueObject\IvaIsNotValid;
use App\Domain\Exception\ValueObject\NameIsNotValid;
use App\Domain\Model\Product\Product;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Iva;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Factory\User\UserWebFactory;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static Product|Proxy createOne(array $attributes = [])
 * @method static Product[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static Product|Proxy find($criteria)
 * @method static Product|Proxy findOrCreate(array $attributes)
 * @method static Product|Proxy first(string $sortedField = 'id')
 * @method static Product|Proxy last(string $sortedField = 'id')
 * @method static Product|Proxy random(array $attributes = [])
 * @method static Product|Proxy randomOrCreate(array $attributes = [])
 * @method static Product[]|Proxy[] all()
 * @method static Product[]|Proxy[] findBy(array $attributes)
 * @method static Product[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Product[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method Product|Proxy create($attributes = [])
 */
final class ProductFactory extends ModelFactory
{
    /**
     * @return array
     * @throws NameIsNotValid
     * @throws AmountIsNotValid
     * @throws DescriptionIsNotValid
     * @throws IvaIsNotValid
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     */
    protected function getDefaults(): array
    {
        return [
            'id' => Id::generate(),
            'name' => Name::fromString(self::faker()->name()),
            'description' => Description::fromString(self::faker()->paragraph),
            'iva' => Iva::fromInt(21),
            'basePrize' => Amount::fromStringWithCommaAsDecimals('1,75'),
            'prizeWithIva' => Amount::fromFloatWithDecimals(1.75 * 1.21),
            'user' => UserWebFactory::random(),
        ];
    }

    protected function initialize(): self
    {
        return $this->afterInstantiate(
            static function (Product $product): void {
            }
        );
    }

    protected static function getClass(): string
    {
        return Product::class;
    }
}
