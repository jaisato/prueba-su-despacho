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

use function is_string;

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
        // countAll($filters) was called twice with the same argument and both
        // results assigned, so the "total" and the "filtered total" were always
        // the same number and the second query was pure cost.
        $productosTotal         = $this->productReadRepository->countAll($filters);
        $productosTotalFiltrado = $productosTotal;

        if ($productosTotalFiltrado->asInt() === 0) {
            return null;
        }

        // Same mapping as before, into its own variable: $orden used to be
        // overwritten with the value object, and the raw string was still
        // needed further down for the pagination links.
        if ($orden) {
            $direction = $orden === 'fechaCreacion_desc' ? 'DESC' : 'ASC';
        } else {
            $direction = 'DESC';
        }

        // The id breaks ties: rows created in the same second have no order of
        // their own, so LIMIT/OFFSET could show one product on two pages and
        // skip another.
        $orderBy = OrderBy::fromArray(['createdOn' => $direction, 'id' => $direction]);

        $limit = Limit::fromLimitAndOffset(
            $resultadosPorPagina,
            ($resultadosPorPagina * $pagina) - $resultadosPorPagina
        );

        $products = $this->productReadRepository->all(
            $filters,
            $limit,
            $orderBy
        );

        $url       = 'api_products_list';
        // The OrderBy value object used to be passed here, having overwritten
        // $orden. The URL generator has no way to render it, so every
        // pagination link silently dropped the ordering the caller asked for.
        $urlParams = [
            'pagina' => $pagina,
            'resultadosPorPagina' =>  $resultadosPorPagina,
        ];

        if ($orden !== null && $orden !== '') {
            $urlParams['orden'] = $orden;
        }

        // Same for the name filter: the links to the other pages dropped it,
        // so page 2 of a filtered listing was page 2 of every product.
        if (isset($filters['name']) && is_string($filters['name'])) {
            $urlParams['name'] = $filters['name'];
        }

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
