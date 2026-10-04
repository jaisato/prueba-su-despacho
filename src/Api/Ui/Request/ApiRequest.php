<?php

declare(strict_types=1);

namespace Api\Ui\Request;

use Api\Domain\Collection\Common\FormErrorDtoCollection;
use Api\Domain\Dto\Common\FormErrorDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_filter;
use function array_values;
use function count;
use function is_int;
use function is_string;
use function preg_match;
use function preg_replace;

abstract class ApiRequest implements \Api\Ui\Http\ApiRequest
{
    /** @var FormErrorDto[] */
    public array $errors = [];

    public function validate(ValidatorInterface $validator): void
    {
        $this->setErrors(
            $validator->validate(
                $this,
                null,
                $this->validationGroups()
            )
        );
    }

    public function isValid(): bool
    {
        return count($this->errors) === 0;
    }

    public function setErrors(ConstraintViolationListInterface $errors): void
    {
        // Remove array keys
        $re = '/(\[\d*\])/';

        foreach ($errors as $error) {
            $propertyName = preg_replace($re, '', $error->getPropertyPath());
            $this->addError($propertyName, $error->getMessage());
        }
    }

    public function addError(string $propertyName, string $error): void
    {
        $this->errors[] = FormErrorDto::create($propertyName, $error);
    }

    /**
     * Reports the fields that arrived with the wrong JSON type, replacing
     * whatever the constraints said about them: such a field reaches the
     * constructor as null, and "es requerido" would describe a value the caller
     * did send.
     *
     * @param array<string, string> $typeErrors field => message
     */
    public function addTypeErrors(array $typeErrors): void
    {
        if ($typeErrors === []) {
            return;
        }

        $this->errors = array_values(array_filter(
            $this->errors,
            static fn (FormErrorDto $error): bool => ! isset($typeErrors[$error->parameter])
        ));

        foreach ($typeErrors as $field => $message) {
            $this->addError($field, $message);
        }
    }

    /**
     * A field the request expects as text.
     *
     * The request properties are typed and every file declares strict_types,
     * so handing the constructor whatever json_decode() produced turned
     * `"name": 123` or `"price": 1.75` into a TypeError - a 500, outside the
     * controller's try/catch - for what is just an invalid form. A JSON integer
     * is accepted as its digits; any other type is left out and reported.
     *
     * @param array<mixed>          $data
     * @param array<string, string> $typeErrors
     */
    protected static function textField(array $data, string $field, array &$typeErrors): ?string
    {
        $value = $data[$field] ?? null;

        if ($value === null || is_string($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        $typeErrors[$field] = 'El valor tiene que ser un texto';

        return null;
    }

    /**
     * A field the request expects as an integer: a JSON integer or a string of
     * digits; anything else is left out and reported.
     *
     * @param array<mixed>          $data
     * @param array<string, string> $typeErrors
     */
    protected static function integerField(array $data, string $field, array &$typeErrors): ?int
    {
        $value = $data[$field] ?? null;

        if ($value === null || is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^\d{1,9}$/', $value) === 1) {
            return (int) $value;
        }

        $typeErrors[$field] = 'El valor tiene que ser un número entero';

        return null;
    }

    public function getErrors(): FormErrorDtoCollection
    {
        return FormErrorDtoCollection::fromElements($this->errors);
    }

    /**
     * @return string[]
     */
    public function validationGroups(): array
    {
        return [];
    }

    public static function fromRequest(Request $request, ValidatorInterface $validator): self
    {
        return new static();
    }
}
