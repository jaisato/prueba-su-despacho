<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue\Consumer;

use App\Domain\Event\DomainEvent;
use App\Infrastructure\CommandBus\CommandBusCli;
use App\Infrastructure\Event\EventCommandFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DomainEventConsumer implements MessageHandlerInterface
{
    private EventCommandFactory $eventCommandFactory;

    private CommandBusCli $commandBus;

    public function __construct(
        EventCommandFactory $eventCommandFactory,
        CommandBusCli $commandBus
    ) {
        $this->eventCommandFactory = $eventCommandFactory;
        $this->commandBus          = $commandBus;
    }

    public function __invoke(DomainEvent $event): void
    {
        $this->commandBus->handle(
            $this->command($event)
        );
    }

    private function command(DomainEvent $event): object
    {
        $commandClassName = $this->eventCommandFactory->get($event);

        return new $commandClassName(...$event->commandArguments());
    }
}
