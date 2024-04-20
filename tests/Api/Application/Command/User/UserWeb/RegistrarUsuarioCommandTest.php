<?php

declare(strict_types=1);

namespace Tests\Api\Application\Command\User\UserWeb;

use Api\Application\Command\User\UserWeb\RegistrarUsuarioCommand;
use App\Domain\CommandBus\CommandBusWrite;
use App\Domain\Exception\Model\User\UserWeb\UserWebAlreadyExists;
use App\Domain\Exception\ValueObject\Security\PasswordsDoNotMatch;
use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Factory\User\UserWebFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\ModelFactory;

use function assert;

class RegistrarUsuarioCommandTest extends KernelTestCase
{
    /**
     * @group userWeb
     * @group userWebRegistrarUsuarioApi
     */
    public function testSuccess(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());

        $commandBus = self::getContainer()->get(CommandBusWrite::class);
        $faker      = ModelFactory::faker();

        $data = [
            'name' => Name::fromString($faker->name()),
            'emailAddress' => EmailAddress::fromString($faker->unique()->email()),
            'password' => '1234567890',
        ];

        $commandBus->handle(
            new RegistrarUsuarioCommand(
                $data['emailAddress']->asString(),
                $data['name']->asString(),
                $data['password'],
                $data['password']
            )
        );

        $userWeb = UserWebFactory::find(['emailAddress' => $data['emailAddress']])->object();

        assert($userWeb instanceof  UserWeb);

        $this->assertTrue($userWeb->name()->equalsTo($data['name']));
        $this->assertTrue($userWeb->emailAddress()->equalsTo($data['emailAddress']));
    }

    /**
     * @group userWeb
     * @group userWebRegistrarUsuarioApi
     */
    public function testErrorPasswordsDoNotMatch(): void
    {
        $this->expectException(PasswordsDoNotMatch::class);

        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());

        $commandBus = self::getContainer()->get(CommandBusWrite::class);
        $faker      = ModelFactory::faker();

        $data = [
            'name' => Name::fromString($faker->name()),
            'emailAddress' => EmailAddress::fromString($faker->unique()->email()),
            'password' => '1234567890',
        ];

        $commandBus->handle(
            new RegistrarUsuarioCommand(
                $data['emailAddress']->asString(),
                $data['name']->asString(),
                $data['password'],
                '0987654321'
            )
        );
    }

    /**
     * @group userWeb
     * @group userWebRegistrarUsuarioApi
     */
    public function testErrorUserWebAlreadyExists(): void
    {
        $this->expectException(UserWebAlreadyExists::class);

        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());

        $commandBus = self::getContainer()->get(CommandBusWrite::class);

        $userWeb = UserWebFactory::randomOrCreate(['emailAddress' => EmailAddress::fromString('jasato@jasato.com')])->object();
        assert($userWeb instanceof  UserWeb);

        $commandBus->handle(
            new RegistrarUsuarioCommand(
                $userWeb->emailAddress()->asString(),
                $userWeb->name()->asString(),
                '0987654321',
                '0987654321'
            )
        );
    }
}
