<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\ValueObject\DateTime;

interface DelayedDomainEvent extends DomainEvent
{
    public function delayedOn(): DateTime;
}
