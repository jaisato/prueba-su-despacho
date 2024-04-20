<?php

namespace App\Domain\Model\Product;

use App\Domain\Model\User\User;
use App\Domain\Model\User\UserWeb;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\DateTime;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Iva;
use App\Domain\ValueObject\Name;

class Product
{

    /**
     * @var DateTime
     */
    private DateTime $createdOn;

    /**
     * @var DateTime
     */
    private DateTime $updatedOn;

    /**
     * @var Amount
     */
    private Amount $priceWithIva;

    /**
     * @var UserWeb
     */
    private UserWeb $user;

    /**
     * @param Id $id
     * @param Name $name
     * @param Description $description
     * @param Amount $basePrice
     * @param Iva $iva
     */
    public function __construct(
        private Id $id,
        private Name $name,
        private Description $description,
        private Amount $basePrice,
        private Iva $iva
    )
    {
        $this->setPriceWithIva($basePrice, $iva);

        $this->createdOn = DateTime::now();
        $this->updatedOn = DateTime::now();
    }

    private function setPriceWithIva(Amount $basePrice, Iva $iva)
    {
        $this->priceWithIva = Amount::fromFloatWithDecimals($basePrice->asFloat() * (100 + $iva->asInt()) / 100);
    }

    /**
     * @param Id $id
     * @param Name $name
     * @param Description $description
     * @param Amount $price
     * @param Iva $iva
     *
     * @return self
     */
    public static function create(
        Id $id,
        Name $name,
        Description $description,
        Amount $price,
        Iva $iva
    ): self {
        return new self(
            $id,
            $name,
            $description,
            $price,
            $iva
        );
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function basePrice(): Amount
    {
        return $this->basePrice;
    }

    public function iva(): Iva
    {
        return $this->iva;
    }

    public function priceWithIva(): Amount
    {
        return $this->priceWithIva;
    }

    public function createdOn(): DateTime
    {
        return $this->createdOn;
    }

    public function updatedOn(): DateTime
    {
        return $this->updatedOn;
    }

    public function setUser(UserWeb $user)
    {
        $this->user = $user;
    }

    public function user(): UserWeb
    {
        return $this->user;
    }
}