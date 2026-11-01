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
        $request = new static();

        $request->nombre         = $request->stringField($data, 'nombre');
        $request->email          = $request->stringField($data, 'email');
        $request->password       = $request->stringField($data, 'password');
        $request->passwordRepeat = $request->stringField($data, 'passwordRepeat');

        $request->validate($validator);
        $request->addInvalidTypeErrors();

        return $request;
    }
}
