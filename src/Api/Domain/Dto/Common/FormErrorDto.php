<?php

declare(strict_types=1);

namespace Api\Domain\Dto\Common;

final class FormErrorDto
{
    public function __construct(
        public string $parameter,
        public string $message,
    ) {
    }

    public static function create(string $parameter, string $message): self
    {
        return new self(
            $parameter,
            $message,
        );
    }
}
