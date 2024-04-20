<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Domain\Event\DelayedDomainEvent;
use App\Domain\ValueObject\DateTime;
use App\Infrastructure\Service\Utilities;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class MessageDispatcher implements \App\Domain\Queue\MessageDispatcher
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(object $message): void
    {
        $this->messageBus->dispatch(
            $message,
            self::envelopes($message)
        );
    }

    /**
     * @return StampInterface[]
     */
    private static function envelopes(object $message): array
    {
        if (! $message instanceof DelayedDomainEvent) {
            return [];
        }

        return [
            new DelayStamp(
                Utilities::milisecondsBetweenTwoDateTime(
                    $message->delayedOn(),
                    DateTime::now()
                )
            ),
        ];
    }
}
