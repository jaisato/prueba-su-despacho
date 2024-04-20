<?php

declare(strict_types=1);

namespace App\Domain\Collection\ValueObject;

use App\Domain\Exception\ValueObject\RolIsNotValid;
use App\Domain\ValueObject\Rol;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_map;
use function assert;
use function count;
use function implode;

final class RolCollection implements IteratorAggregate, Countable
{
    /** @var Rol[] */
    private array $elements = [];

    private int $count = 0;

    private function __construct()
    {
    }

    /**
     * @param Rol[] $elements
     *
     * @throws InvalidArgumentException
     */
    public static function fromElements(array $elements): self
    {
        Assert::allIsInstanceOf($elements, Rol::class);

        $collection = new self();

        $collection->elements = $elements;
        $collection->count    = count($elements);

        return $collection;
    }

    /**
     * @throws RolIsNotValid
     */
    public static function visiblesEnBaseARoles(RolCollection $roles): self
    {
        $rolesVisibles = [];

        foreach ($roles as $rol) {
            assert($rol instanceof Rol);
            foreach ($rol->getVisibleRoles() as $visibleRol) {
                if (array_key_exists($visibleRol, $rolesVisibles)) {
                    continue;
                }

                $rolesVisibles[$visibleRol] = Rol::fromString($visibleRol);
            }
        }

        return self::fromElements($rolesVisibles);
    }

    public function addElement($element): void
    {
        Assert::isInstanceOf($element, Rol::class);

        $this->elements[] = $element;
        $this->count++;
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public function count(): int
    {
        return $this->count;
    }

    public function first(): Rol
    {
        return $this->elements[0];
    }

    public function getIterator(): Traversable
    {
        yield from $this->elements;
    }

    public function asString(): string
    {
        return implode(', ', array_map(static function (Rol $element) {
            return $element->asString();
        }, $this->elements));
    }

    /**
     * @return array<string>
     */
    public function asArray(): array
    {
        return array_map(static function (Rol $element) {
            return $element->asString();
        }, $this->elements);
    }

    /**
     * @return array<array<string, string>>
     */
    public function asAssociativeArray(): array
    {
        $elements = [];

        foreach ($this->elements as $element) {
            $elements[] = ['rol' => $element->asString()];
        }

        return $elements;
    }

    /**
     * @param array<string> $elements
     *
     * @throws RolIsNotValid
     */
    public static function fromArray(array $elements): self
    {
        $collection = new self();

        foreach ($elements as $element) {
            $collection->addElement(Rol::fromString($element));
        }

        return $collection;
    }

    /**
     * @param array<array<string, string>> $elements
     *
     * @throws RolIsNotValid
     */
    public static function fromAssociativeArray(array $elements): self
    {
        $collection = new self();

        foreach ($elements as $element) {
            $collection->addElement(Rol::fromString($element['rol']));
        }

        return $collection;
    }

    public function contains(Rol $rol): bool
    {
        foreach ($this->elements as $element) {
            if ($element->equalsTo($rol)) {
                return true;
            }
        }

        return false;
    }
}
