<?php

declare(strict_types=1);

namespace Api\Ui\Request\Product;

use Api\Ui\Request\ApiRequest;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The constraints are PHP attributes. They used to be docblock annotations,
 * which this application never read (framework.annotations is off, so the
 * validator only looks at attributes): none of them ran, so a missing field
 * reached the command as null and came back as the generic "try again later"
 * error instead of naming the field.
 */
abstract class ProductRequest extends ApiRequest
{
    #[Assert\NotBlank(message: 'El nombre es requerido', groups: ['create'])]
    public ?string $name;

    #[Assert\NotBlank(message: 'La descripción es requerida', groups: ['create'])]
    public ?string $description;

    #[Assert\NotBlank(message: 'El precio es requerido', groups: ['create'])]
    public ?string $price;

    #[Assert\NotBlank(message: 'El IVA es requerido', groups: ['create'])]
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
