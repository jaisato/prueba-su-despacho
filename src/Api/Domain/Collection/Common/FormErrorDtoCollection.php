<?php

declare(strict_types=1);

namespace Api\Domain\Collection\Common;

use Api\Domain\Dto\Common\FormErrorDto;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

use function count;

final class FormErrorDtoCollection implements IteratorAggregate, Countable
{
    /** @var FormErrorDto[] */
    private array $elements = [];

    private int $count = 0;

    private function __construct()
    {
    }

    /**
     * @param FormErrorDto[] $elements
     *
     * @throws InvalidArgumentException
     */
    public static function fromElements(array $elements): self
    {
        Assert::allIsInstanceOf($elements, FormErrorDto::class);

        $collection = new self();

        $collection->elements = $elements;
        $collection->count    = count($elements);

        return $collection;
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public function count(): int
    {
        return $this->count;
    }

    public function first(): FormErrorDto
    {
        return $this->elements[0];
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function getIterator(): Traversable
    {
        yield from $this->elements;
    }
}
