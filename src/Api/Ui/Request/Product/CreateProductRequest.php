<?php

declare(strict_types=1);

namespace Api\Ui\Request\Product;

use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateProductRequest extends ProductRequest
{
    /**
     * @return string[]
     */
    public function validationGroups(): array
    {
        return ['create'];
    }

    public static function fromArray(array $data, ValidatorInterface $validator): self
    {
        $request = new static(
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['price'] ?? null,
            $data['iva'] ?? null
        );

        $request->validate($validator);

        return $request;
    }
}
