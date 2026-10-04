<?php

declare(strict_types=1);

namespace Api\Ui\Request\User;

use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SignUpUserRequest extends UserRequest
{
    /**
     * @return string[]
     */
    public function validationGroups(): array
    {
        return ['enviar'];
    }

    public static function fromArray(array $data, ValidatorInterface $validator): self
    {
        $typeErrors = [];
        $request    = new static(
            self::textField($data, 'nombre', $typeErrors),
            self::textField($data, 'email', $typeErrors),
            self::textField($data, 'password', $typeErrors),
            self::textField($data, 'passwordRepeat', $typeErrors)
        );

        $request->validate($validator);
        $request->addTypeErrors($typeErrors);

        return $request;
    }
}
