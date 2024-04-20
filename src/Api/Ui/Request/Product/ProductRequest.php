<?php

declare(strict_types=1);

namespace Api\Ui\Request\Product;

use Api\Ui\Request\ApiRequest;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ProductRequest extends ApiRequest
{
    /** @Assert\NotBlank(message="El nombre es requerido", groups={"create"}) */
    public ?string $name;

    /** @Assert\NotBlank(message="La descripción es requerida", groups={"create"}) */
    public ?string $description;

    /** @Assert\NotBlank(message="El precio es requerido", groups={"create"}) */
    public ?string $price;

    /** @Assert\NotBlank(message="El IVA es requerido", groups={"create"}) */
    public ?int $iva;

    final public function __construct(
        ?string $name = null,
        ?string $description = null,
        ?string $price = null,
        ?int $iva = null
    ) {
        $this->name        = $name;
        $this->description = $description;
        $this->price       = $price;
        $this->iva         = $iva;
    }
}
