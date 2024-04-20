<?php

declare(strict_types=1);

namespace Api\Application\Query\Product;

use Api\Domain\Collection\Common\ProductDtoCollection;
use Api\Domain\Dto\Common\ElementosPaginadosDto;
use Api\Domain\Dto\Common\PaginacionDto;
use Api\Domain\Dto\Common\ProductsPaginatedDto;
use Api\Domain\Service\Paginacion\PaginacionService;
use App\Domain\Exception\ValueObject\Repository\LimitIsNotValid;
use App\Domain\Repository\Doctrine\Product\ProductReadRepository;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\Repository\Limit;
use App\Domain\ValueObject\Repository\OrderBy;

final class GetProductsQueryHandler
{
    public const MAX_PRODUCTS_PER_PAGE = 50;

    public function __construct(
        private readonly ProductReadRepository $productReadRepository,
        private readonly PaginacionService     $paginacionService
    ) {
    }

    public function __invoke(GetProductsQuery $query): ProductsPaginatedDto
    {
        $resultadosPorPagina = $query->resultadosPorPagina;
        if ($query->resultadosPorPagina > self::MAX_PRODUCTS_PER_PAGE) {
            $resultadosPorPagina = self::MAX_PRODUCTS_PER_PAGE;
        }

        $elementosPaginados = $this->elementosPaginados(
            $query->filters,
            $query->pagina,
            $resultadosPorPagina,
            $query->orden
        );

        if ($elementosPaginados === null) {
            return ProductsPaginatedDto::createEmpty();
        }

        return ProductsPaginatedDto::fromResults(
            $elementosPaginados->paginacion,
            ProductDtoCollection::fromElements($elementosPaginados->elementos)
        );
    }

    /**
     * @param array $filters
     * @param int $pagina
     * @param int $resultadosPorPagina
     * @param string|null $orden
     *
     * @return ElementosPaginadosDto|null
     *
     * @throws LimitIsNotValid
     */
    private function elementosPaginados(
        array $filters,
        int $pagina,
        int $resultadosPorPagina,
        ?string $orden
    ): ?ElementosPaginadosDto
    {
        $productosTotal         = $this->productReadRepository->countAll($filters);
        $productosTotalFiltrado = $this->productReadRepository->countAll($filters);

        if ($productosTotalFiltrado->asInt() === 0) {
            return null;
        }

        if ($orden) {
            $orden = OrderBy::fromArray(
                [
                    'createdOn' => $orden === 'fechaCreacion_desc' ? 'DESC' : 'ASC',
                ]
            );
        } else {
            $orden = OrderBy::fromArray(['createdOn' => 'DESC']);
        }

        $limit = Limit::fromLimitAndOffset(
            $resultadosPorPagina,
            ($resultadosPorPagina * $pagina) - $resultadosPorPagina
        );

        $products = $this->productReadRepository->all(
            $filters,
            $limit,
            $orden
        );

        $url       = 'api_products_list';
        $urlParams = [
            'pagina' => $pagina,
            'resultadosPorPagina' =>  $resultadosPorPagina,
            'orden' => $orden,
        ];

        $productsCollection = ProductDtoCollection::fromModelResults(
            $products
        )->toArray();

        $paginacion = $this->getPaginacion(
            $productosTotalFiltrado,
            $productosTotal,
            $pagina,
            $resultadosPorPagina,
            $url,
            $urlParams
        );

        return ElementosPaginadosDto::create($paginacion, $productsCollection);
    }

    private function getPaginacion(
        Quantity $comentariosTotalFiltrado,
        Quantity $comentariosTotal,
        int $paginaActual,
        int $resultadosPorPagina,
        string $url,
        array $urlParams
    ): ?PaginacionDto {
        return $this->paginacionService->crearNumerada(
            $comentariosTotalFiltrado,
            $comentariosTotal,
            $resultadosPorPagina,
            $paginaActual,
            $url,
            $urlParams
        );
    }
}
