<?php

declare(strict_types=1);

namespace Api\Application\Command\Product;

use App\Domain\Dto\Product\DetalleProduct;
use App\Domain\Exception\Model\User\UserWeb\UserWebNotFound;
use App\Domain\Exception\ValueObject\AmountIsNotValid;
use App\Domain\Exception\ValueObject\DescriptionIsNotValid;
use App\Domain\Exception\ValueObject\IdIsNotValid;
use App\Domain\Exception\ValueObject\IvaIsNotValid;
use App\Domain\Exception\ValueObject\NameIsNotValid;
use App\Domain\Model\Product\Product;
use App\Domain\Repository\Doctrine\Product\ProductWriteRepository;
use App\Domain\Repository\Doctrine\User\UserWeb\UserWebReadRepository;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\Description;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Iva;
use App\Domain\ValueObject\Name;

class CreateProductCommandHandler
{
    public function __construct(
        private readonly UserWebReadRepository $userReadRepository,
        private readonly ProductWriteRepository $productWriteRepository
    ) {
    }

    /**
     * @param CreateProductCommand $command
     *
     * @return DetalleProduct
     *
     * @throws NameIsNotValid
     * @throws AmountIsNotValid
     * @throws DescriptionIsNotValid
     * @throws IdIsNotValid
     * @throws IvaIsNotValid
     * @throws UserWebNotFound
     */
    public function __invoke(CreateProductCommand $command): DetalleProduct
    {
        $user = $this->userReadRepository->ofIdOrFail(Id::fromString($command->userWebId));

        $product = Product::create(
            $this->productWriteRepository->nextIdentity(),
            Name::fromString($command->name),
            Description::fromString($command->description),
            Amount::fromStringWithCommaAsDecimals($command->price),
            Iva::fromInt($command->iva)
        );

        $product->setUser($user);

        $this->productWriteRepository->save($product);

        return DetalleProduct::fromModel($product);
    }
}
