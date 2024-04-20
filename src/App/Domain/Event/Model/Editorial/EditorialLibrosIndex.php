<?php

declare(strict_types=1);

namespace App\Domain\Event\Model\Editorial;

use App\Domain\ValueObject\DateTime;

class EditorialLibrosIndex implements EditorialEvent
{
    private int $ocurredOn;

    public function __construct(
        private string $idEditorial,
    ) {
        $this->ocurredOn = DateTime::now()->asTimestamp();
    }

    public function ocurredOn(): int
    {
        return $this->ocurredOn;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'idEditorial' => $this->idEditorial,
        ];
    }

    /**
     * @return array
     */
    public function commandArguments(): array
    {
        return [
            $this->idEditorial,
        ];
    }
}
