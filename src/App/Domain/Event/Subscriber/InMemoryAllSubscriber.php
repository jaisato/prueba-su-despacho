<?php

declare(strict_types=1);

namespace App\Domain\Event\Subscriber;

use App\Domain\Event\DomainEvent;
use App\Domain\Event\DomainEventSubscriber;

final class InMemoryAllSubscriber implements DomainEventSubscriber
{
    /** @var DomainEvent[] */
    private array $events = [];

    public function __construct()
    {
    }

    public function handle(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function isSubscribedTo(DomainEvent $event): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function events(): array
    {
        return $this->events;
    }
}
