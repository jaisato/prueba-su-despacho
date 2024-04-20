<?php

declare(strict_types=1);

namespace Api\Ui\Request;

use Api\Domain\Collection\Common\FormErrorDtoCollection;
use Api\Domain\Dto\Common\FormErrorDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;
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
