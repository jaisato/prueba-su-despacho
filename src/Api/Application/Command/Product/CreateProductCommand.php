<?php

declare(strict_types=1);

namespace Api\Application\Command\Product;

class CreateProductCommand
{
    public function __construct(
        public readonly string $userWebId,
        public readonly string $name,
        public readonly string $description,
        public readonly string $price,
        public readonly int $iva
    ) {
    }
}
