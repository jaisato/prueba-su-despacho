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
        $typeErrors = [];
        $request    = new static(
            self::textField($data, 'name', $typeErrors),
            self::textField($data, 'description', $typeErrors),
            self::textField($data, 'price', $typeErrors),
            self::integerField($data, 'iva', $typeErrors)
        );

        $request->validate($validator);
        $request->addTypeErrors($typeErrors);

        return $request;
    }
}
