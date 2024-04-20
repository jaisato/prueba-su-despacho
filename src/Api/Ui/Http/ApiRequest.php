<?php

declare(strict_types=1);

namespace Api\Ui\Http;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

interface ApiRequest
{
    public static function fromArray(array $data, ValidatorInterface $validator): ApiRequest;

    public function validate(ValidatorInterface $validator): void;

    public function isValid(): bool;

    public function setErrors(ConstraintViolationListInterface $errors): void;

    public function addError(string $propertyName, string $error): void;

    /**
     * @return string[]
     */
    public function validationGroups(): array;
}
