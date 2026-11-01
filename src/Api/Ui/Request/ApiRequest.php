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

    /**
     * Fields that arrived with a JSON type the form cannot take, by name, with
     * the message to show for each.
     *
     * @var array<string, string>
     */
    private array $invalidTypes = [];

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

    /**
     * The value of a text field, or null when it is missing.
     *
     * The request properties are typed (`?string`) and the files declare
     * strict_types, so handing them the decoded JSON as-is turned a field sent
     * with the wrong type (`"name": 123`, `"email": ["x"]`) into a TypeError
     * raised before any validation ran: a 500 for an invalid form. Such a
     * field is now recorded as invalid and reaches the property as null.
     *
     * Numbers are not turned into text on purpose: `"price": 1.77` would
     * become "1.77", which a field read with comma decimals takes as 177.
     *
     * @param array<array-key, mixed> $data
     */
    protected function stringField(array $data, string $field): ?string
    {
        $value = $data[$field] ?? null;

        if ($value === null || is_string($value)) {
            return $value;
        }

        $this->invalidTypes[$field] = 'El campo debe ser una cadena de texto';

        return null;
    }

    /**
     * The value of a whole-number field, or null when it is missing. A string
     * of digits ("21") is accepted, since form clients often send numbers
     * quoted; anything else is recorded as invalid (see stringField()).
     *
     * @param array<array-key, mixed> $data
     */
    protected function intField(array $data, string $field): ?int
    {
        $value = $data[$field] ?? null;

        if ($value === null || is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^\d{1,9}$/', $value) === 1) {
            return (int) $value;
        }

        $this->invalidTypes[$field] = 'El campo debe ser un número entero';

        return null;
    }

    /**
     * Turns the fields recorded by stringField() / intField() into errors.
     * Any other error on the same field (the NotBlank a null triggers) is
     * dropped: "es requerido" would be wrong, the field was sent.
     */
    protected function addInvalidTypeErrors(): void
    {
        foreach ($this->invalidTypes as $field => $message) {
            $this->errors = array_values(array_filter(
                $this->errors,
                static fn (FormErrorDto $error): bool => $error->parameter !== $field
            ));

            $this->addError($field, $message);
        }
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
