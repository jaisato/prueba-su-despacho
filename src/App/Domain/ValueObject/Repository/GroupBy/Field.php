<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Repository\GroupBy;

final class Field
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function field(): string
    {
        return $this->field;
    }
}
