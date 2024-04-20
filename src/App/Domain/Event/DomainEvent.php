<?php

declare(strict_types=1);

namespace App\Domain\Event;

use JsonSerializable;

interface DomainEvent extends JsonSerializable
{
    public function ocurredOn(): int;

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array;
}
