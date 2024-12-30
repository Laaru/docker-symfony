<?php

namespace App\Controller\Api;

use App\Entity\DTO\CatalogRequestDTO;
use App\Entity\Product;
use App\Service\Catalog\CatalogProductService;
use App\Service\Catalog\ProductFilterOptionsService;
use App\Service\Catalog\ProductOrderOptionsService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/catalog',
    name: 'api_catalog_',
    defaults: ['show_exception_as_json' => true]
)]
class CatalogController extends AbstractController
{
    public function __construct(
        private readonly CatalogProductService $catalogProductService,
        private readonly ProductFilterOptionsService $productFilterOptionsService,
        private readonly ProductOrderOptionsService $productOrderOptionsService,
    ) {}

    #[Route(
        '/',
        name: 'list',
        methods: ['GET']
    )]
    #[OA\Tag(name: 'catalog')]
    #[OA\RequestBody(content: new Model(type: CatalogRequestDTO::class))]
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
    public function listProducts(
        #[MapQueryString]
        CatalogRequestDTO $catalogRequestDTO
    ): JsonResponse {
        $catalogProductList = $this->catalogProductService->getPaginatedProducts($catalogRequestDTO);
        $catalogFilterOptions = $this->productFilterOptionsService->getFilterOptions();
        $catalogOrderOptions = $this->productOrderOptionsService->getOrderOptions();

        return $this->json(
            data: [
                'catalog' => $catalogProductList,
                'filterOptions' => $catalogFilterOptions,
                'orderOptions' => $catalogOrderOptions,
            ]
        );
    }
}
