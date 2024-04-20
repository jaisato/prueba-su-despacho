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
        $request = new static(
            $data['nombre'] ?? null,
            $data['email'] ?? null,
            $data['password'] ?? null,
            $data['passwordRepeat'] ?? null
        );

        $request->validate($validator);

        return $request;
    }
}
