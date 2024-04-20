<?php

declare(strict_types=1);

namespace App\Domain\Event\Model\Editorial;

use App\Domain\Event\DomainEvent;

interface EditorialEvent extends DomainEvent
{
    /*** @return string[] */
    public function commandArguments(): array;
}
