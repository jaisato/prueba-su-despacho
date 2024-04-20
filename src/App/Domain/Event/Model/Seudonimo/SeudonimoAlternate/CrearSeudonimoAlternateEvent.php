<?php

declare(strict_types=1);

namespace App\Domain\Event\Model\Seudonimo\SeudonimoAlternate;

use App\Domain\Event\DomainEvent;
use App\Domain\ValueObject\DateTime;

class CrearSeudonimoAlternateEvent implements DomainEvent
{
    private int $ocurredOn;

    public function __construct(
        private string $id
    ) {
        $this->ocurredOn = DateTime::now()->asTimestamp();
    }

    public function ocurredOn(): int
    {
        return $this->ocurredOn;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'ocurredOn' => $this->ocurredOn,
        ];
    }

    /**
     * @return array<string>
     */
    public function commandArguments(): array
    {
        return [
            $this->id,
        ];
    }
}
