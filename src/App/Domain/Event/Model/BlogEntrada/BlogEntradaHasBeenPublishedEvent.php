<?php

declare(strict_types=1);

namespace App\Domain\Event\Model\BlogEntrada;

use App\Domain\Event\DelayedDomainEvent;
use App\Domain\ValueObject\DateTime;

class BlogEntradaHasBeenPublishedEvent implements DelayedDomainEvent
{
    private int $ocurredOn;

    public function __construct(
        private string $idBlogEntrada,
        private DateTime $delayedOn
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
            'idBlogEntrada' => $this->idBlogEntrada,
            'ocurredOn' => $this->ocurredOn,
            'delayedOn' => $this->delayedOn,
        ];
    }

    /**
     * @return array<string>
     */
    public function commandArguments(): array
    {
        return [
            $this->idBlogEntrada,
        ];
    }

    public function delayedOn(): DateTime
    {
        return $this->delayedOn;
    }
}
