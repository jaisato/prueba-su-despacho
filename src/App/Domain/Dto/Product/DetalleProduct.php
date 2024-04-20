<?php

declare(strict_types=1);

namespace App\Domain\Dto\Product;

use App\Domain\Model\Product\Product;
use App\Domain\Model\User\User;
use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Iva;
use App\Domain\ValueObject\Name;

class DetalleProduct implements \JsonSerializable
{
    public function __construct(
        public Id          $id,
        public Name        $name,
        public Description $description,
        public Amount      $basePrice,
        public Amount      $priceWithIva,
        public Iva       $iva,
        public UserWeb        $user,
    ) {
    }

    /**
     * @param Product $element
     * @return self
     */
    public static function fromModel(Product $element): self
    {
        return new self(
            $element->id(),
            $element->name(),
            $element->description(),
            $element->basePrice(),
            $element->priceWithIva(),
            $element->iva(),
            $element->user()
        );
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return $this->description;
    }

    public function basePrice()
    {
        return $this->basePrice;
    }

    public function priceWithIva()
    {
        return $this->priceWithIva;
    }

    public function iva()
    {
        return $this->iva;
    }

    public function user()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id()->asString(),
            'name' => $this->name()->asString(),
            'description' => $this->description()->asString(),
            'base_price' => $this->basePrice()->asStringWithCommaAsDecimalSeparatorAndThousandSeparator(),
            'price' => $this->priceWithIva()->asStringWithCommaAsDecimalSeparatorAndThousandSeparator(),
            'iva' => $this->iva()->asString(),
            'user' => $this->user()->name()->asString(),
        ];
    }
}
