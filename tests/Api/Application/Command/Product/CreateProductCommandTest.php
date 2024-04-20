<?php

declare(strict_types=1);

namespace Tests\Api\Application\Command\Product;

use Api\Application\Command\Product\CreateProductCommand;
use App\Domain\CommandBus\CommandBusWrite;
use App\Domain\Model\Product\Product;
use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Iva;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Factory\Product\ProductFactory;
use App\Infrastructure\Factory\User\UserWebFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\ModelFactory;

use function assert;

class CreateProductCommandTest extends KernelTestCase
{
    /**
     * @group product
     */
    public function testSuccess(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());

        $commandBus = self::getContainer()->get(CommandBusWrite::class);
        $faker      = ModelFactory::faker();

        $data = [
            'name' => Name::fromString($faker->name()),
            'description' => Description::fromString($faker->paragraph),
            'iva' => Iva::fromInt(21),
            'basePrice' => Amount::fromFloatWithDecimals(1.75),
            'emailAddress' => EmailAddress::fromString('mr.rabbit@gmail.com'),
        ];

        $product = UserWebFactory::randomOrCreate(['emailAddress' => $data['emailAddress']])->object();

        assert($product instanceof  UserWeb);

        $commandBus->handle(
            new CreateProductCommand(
                $product->id()->asString(),
                $data['name']->asString(),
                $data['description']->asString(),
                $data['basePrice']->asStringWithCommaAsDecimalSeparator(),
                $data['iva']->asInt()
            )
        );

        $product = ProductFactory::find(['name' => $data['name']])->object();

        assert($product instanceof  Product);

        $this->assertTrue($product->name()->equalsTo($data['name']));
        $this->assertTrue($product->description()->equalsTo($data['description']));
        $this->assertTrue($product->basePrice()->equalsTo($data['basePrice']));
        $this->assertTrue($product->iva()->equalsTo($data['iva']));
    }
}
