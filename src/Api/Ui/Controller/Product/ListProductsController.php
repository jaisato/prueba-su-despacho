<?php

declare(strict_types=1);

namespace Api\Ui\Controller\Product;

use Api\Application\Query\Product\GetProductsQuery;
use Api\Domain\Dto\Common\ProductsPaginatedDto;
use App\Domain\CommandBus\CommandBusRead;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

use function is_string;

#[AsController]
final class ListProductsController extends AbstractController
{
    public function __construct(private readonly CommandBusRead $commandBusRead)
    {
    }

    #[Route(
        path: '/productos/{pagina}/{resultadosPorPagina}',
        name: 'api_products_list',
        defaults: [
            '_api_resource_class' => ProductsPaginatedDto::class,
            '_api_operation_name' => 'products_list',
        ],
        // Positive, bounded integers only. Without requirements a
        // non-numeric segment reached the int parameters as a TypeError, and
        // 0 or a huge page number reached Limit / the offset arithmetic
        // (overflowing to float) - every one of them a 500 instead of a 404.
        requirements: [
            'pagina' => '[1-9]\\d{0,5}',
            'resultadosPorPagina' => '[1-9]\\d{0,5}',
        ],
        methods: ['GET'],
    )]
    public function buscar(
        Request $request,
        int $pagina,
        int $resultadosPorPagina
    ): ProductsPaginatedDto {
        // Only the documented query parameters reach the repository, and only
        // as strings. The whole query string used to be handed over as the
        // filter array, so a caller could also filter by `id` / `not_id`
        // (undocumented), and `?orden[]=x` or `?name[]=x` arrived as arrays:
        // a TypeError on GetProductsQuery::$orden, an "Array to string
        // conversion" inside the LIKE.
        $filters = [];
        $name    = $request->query->all()['name'] ?? null;
        if (is_string($name) && $name !== '') {
            $filters['name'] = $name;
        }

        $orden = $request->query->all()['orden'] ?? null;

        return $this->commandBusRead->handle(
            new GetProductsQuery(
                $pagina,
                $resultadosPorPagina,
                $filters,
                is_string($orden) ? $orden : null,
            )
        );
    }
}
