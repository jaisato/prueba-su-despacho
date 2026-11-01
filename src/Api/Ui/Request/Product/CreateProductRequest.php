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
        $request = new static();

        $request->name        = $request->stringField($data, 'name');
        $request->description = $request->stringField($data, 'description');
        $request->price       = $request->stringField($data, 'price');
        $request->iva         = $request->intField($data, 'iva');

        $request->validate($validator);
        $request->addInvalidTypeErrors();

        return $request;
    }
}
