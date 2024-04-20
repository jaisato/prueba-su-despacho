<?php

declare(strict_types=1);

namespace App\Domain\Event\Model\Libro;

use App\Domain\Event\DomainEvent;
use App\Domain\ValueObject\DateTime;

class ElasticsearchLibroIndexEvent implements DomainEvent
{
    private int $ocurredOn;

    public function __construct(
        private string $id,
    ) {
        $this->ocurredOn = DateTime::now()->asTimestamp();
    }

    public function ocurredOn(): int
    {
        return $this->ocurredOn;
    }

    /**
     * @return string[]
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * @return string[]
     */
    public function commandArguments(): array
    {
        return [
            $this->id,
        ];
    }
}
