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

use function array_key_exists;

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
            '_api_item_operation_name' => 'products_list',
        ],
        methods: ['GET'],
    )]
    public function buscar(
        Request $request,
        int $pagina,
        int $resultadosPorPagina
    ): ProductsPaginatedDto {
        $params = $request->query->all();

        return $this->commandBusRead->handle(
            new GetProductsQuery(
                $pagina,
                $resultadosPorPagina,
                $params,
                array_key_exists('orden', $params) ? $params['orden'] : null,
            )
        );
    }
}
