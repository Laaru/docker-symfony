<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route(
    '/api/catalog',
    name: 'api_catalog_',
    defaults: ['show_exception_as_json' => true]
)]
class CatalogController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    #[Route(
        '/',
        name: 'list',
        defaults: ['show_exception_as_json' => true],
        methods: ['GET']
    )]
    #[OA\Tag(name: 'catalog')]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'sort',
        description: 'Sort field ("updatedAt" or "basePrice")',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', default: 'updatedAt')
    )]
    #[OA\Parameter(
        name: 'order',
        description: 'Sort direction: "asc" or "desc"',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', default: 'desc')
    )]
    #[OA\Parameter(
        name: 'priceMin',
        description: 'Min price for filter',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'number', format: 'integer')
    )]
    #[OA\Parameter(
        name: 'priceMax',
        description: 'Max price for filter',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'number', format: 'integer')
    )]
    #[OA\Parameter(
        name: 'color',
        description: 'Color id for filter',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'store',
        description: 'Store id for filter',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns paginated list of products',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Product::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    properties: [
                        new OA\Property(property: 'total', type: 'integer'),
                        new OA\Property(property: 'page', type: 'integer'),
                        new OA\Property(property: 'pages', type: 'integer'),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad Request',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'object')
                ),
            ],
            type: 'object'
        )
    )]
    public function listProducts(
        Request $request,
        TagAwareCacheInterface $cache,
        LoggerInterface $logger
    ): JsonResponse {
        $page = (int) $request->query->get('page', 1);
        $sort = $request->query->get('sort', 'updatedAt');
        $order = $request->query->get('order', 'desc');
        $priceMin = $request->query->get('priceMin');
        $priceMax = $request->query->get('priceMax');
        $colorId = $request->query->get('color');
        $storeId = $request->query->get('store');

        $cacheKey = sprintf(
            'products_page_%d_sort_%s_order_%s_price_%s_%s_color_%s_store_%s',
            $page,
            $sort,
            $order,
            $priceMin ?? 'null',
            $priceMax ?? 'null',
            $colorId ?? 'null',
            $storeId ?? 'null'
        );

        $productsData = $cache->get($cacheKey, function (ItemInterface $item) use ($page, $sort, $order, $priceMin, $priceMax, $colorId, $storeId) {
            $item->tag(['products', 'colors', 'stores']);

            $queryBuilder = $this->productRepository->createQueryBuilder('p');
            if (null !== $priceMin) {
                $queryBuilder->andWhere('p.basePrice >= :priceMin')->setParameter('priceMin', $priceMin);
            }
            if (null !== $priceMax) {
                $queryBuilder->andWhere('p.basePrice <= :priceMax')->setParameter('priceMax', $priceMax);
            }
            if (null !== $colorId) {
                $queryBuilder->andWhere('p.color = :color')->setParameter('color', $colorId);
            }
            if (null !== $storeId) {
                $queryBuilder
                    ->join('p.stores', 's')
                    ->andWhere('s.id = :store')->setParameter('store', $storeId);
            }

            $allowedSortFields = ['basePrice', 'updatedAt'];
            if (!in_array($sort, $allowedSortFields, true)) {
                $sort = 'updatedAt';
            }
            $queryBuilder->orderBy('p.'.$sort, 'asc' === $order ? 'ASC' : 'DESC');


            $limit = 30;
            $queryBuilder
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $paginator = new Paginator($queryBuilder);
            $totalItems = count($paginator);
            $totalPages = ceil($totalItems / $limit);

            $products = iterator_to_array($paginator->getIterator());

            return [
                'products' => $this->productRepository->normalizeCollection($products, 'list'),
                'pagination' => [
                    'total' => $totalItems,
                    'page' => $page,
                    'pages' => $totalPages,
                ],
            ];
        });

        return $this->json([
            'data' => $productsData['products'],
            'pagination' => $productsData['pagination'],
        ]);
    }
}
