<?php

declare(strict_types=1);

namespace Api\Domain\Collection\Common;

use App\Domain\Dto\Product\DetalleProduct;
use App\Domain\Model\Product\Product;
use Countable;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

use function assert;
use function count;

final class ProductDtoCollection implements IteratorAggregate, Countable
{
    /** @var array<DetalleProduct> */
    public array $elements = [];

    public int $count = 0;

    private function __construct()
    {
    }

    /**
     * @param array<DetalleProduct> $elements
     *
     * @throws InvalidArgumentException
     */
    public static function fromElements(array $elements): self
    {
        $collection = new self();

        $collection->elements = $elements;
        $collection->count    = count($elements);

        return $collection;
    }

    /**
     * @param Collection|array $products
     *
     * @return self
     */
    public static function fromModelResults(
        Collection|array    $products
    ): self {
        $elementDtos = [];

        foreach ($products as $product) {
            assert($product instanceof Product);

            $elementDtos[] = DetalleProduct::fromModel($product);
        }

        return self::fromElements($elementDtos);
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public function count(): int
    {
        return $this->count;
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
