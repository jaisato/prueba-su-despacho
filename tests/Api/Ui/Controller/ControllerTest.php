<?php

declare(strict_types=1);

namespace Tests\Api\Ui\Controller;

use App\Domain\Model\User\User;
use App\Infrastructure\Security\User\SfUserAdministrator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

use function sprintf;

abstract class ControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected RouterInterface $router;
    protected EntityManagerInterface $entityManager;

    protected function initClient(): void
    {
        $this->client        = static::createClient();
        $this->router        = static::getContainer()->get(RouterInterface::class);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }
}
