<?php

declare(strict_types=1);

namespace App\Domain\Exception\Model\Product;

use App\Domain\ValueObject\Id;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function sprintf;

final class ProductNotFound extends NotFoundHttpException
{
    public static function withId(Id $id): self
    {
        return new self(
            sprintf(
                'Product with id %s was not found',
                $id->asString()
            )
        );
    }
}
