<?php

namespace App\Controller\Api;

use App\Entity\DTO\ProductUpdateDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Product\ProductImportService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/product',
    name: 'api_product_',
    defaults: ['show_exception_as_json' => true],
)]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws \Exception
     */
    #[Route(
        '/',
        name: 'create',
        methods: ['POST']
    )]
    #[OA\Tag(name: 'product')]
    #[OA\Post(description: 'This route is accessible only to authenticated users with the ROLE_MANAGER role and above.')]
    #[OA\Response(
        response: 200,
        description: 'Creates product',
        content: new OA\JsonContent(
            ref: new Model(type: Product::class, groups: ['detail'])
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Parameter error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    default: [
                        [
                            'field' => 'basePrice',
                            'message' => 'This value should not be blank.',
                        ],
                    ]
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\RequestBody(content: new Model(type: ProductUpdateDTO::class))]
    #[OA\Parameter(
        name: 'Authorization',
        description: 'Bearer token for authentication',
        in: 'header',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    public function createProduct(
        #[MapRequestPayload]
        ProductUpdateDTO $productUpdateDTO,
        ProductImportService $productImportService
    ): Response {
        $product = $this->productRepository->findOneByExternalId($productUpdateDTO->externalId);

        if ($product) {
            throw new ConflictHttpException('Product with externalId '.$productUpdateDTO->externalId.' already exists. Use PUT method instead.');
        }

        $product = $productImportService->importOneProduct($productUpdateDTO);

        return $this->json(data: $this->productRepository->normalize($product));
    }

    #[Route(
        '/{id<\d+>?1}',
        name: 'read',
        methods: ['GET'],
    )]
    #[OA\Tag(name: 'product')]
    #[OA\Response(
        response: 200,
        description: 'Returns product by id',
        content: new OA\JsonContent(
            ref: new Model(type: Product::class, groups: ['detail'])
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not found error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    default: [
                        [
                            'type' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException',
                            'message' => 'No product found for id 178',
                        ],
                    ]
                ),
            ],
            type: 'object'
        )
    )]
    public function readProduct(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        return $this->json(data: $this->productRepository->normalize($product));
    }

    #[Route(
        '/{id<\d+>?1}',
        name: 'update',
        methods: ['PUT']
    )]
    #[OA\Put(description: 'This route is accessible only to authenticated users with the ROLE_MANAGER role and above.')]
    #[OA\Tag(name: 'product')]
    #[OA\Response(
        response: 200,
        description: 'Updates product',
        content: new OA\JsonContent(
            ref: new Model(type: Product::class, groups: ['detail'])
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not found error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    default: [
                        [
                            'type' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException',
                            'message' => 'No product found for id 178',
                        ],
                    ]
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Parameter error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    default: [
                        [
                            'field' => 'name',
                            'message' => 'This value should not be blank.',
                        ],
                    ]
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\RequestBody(content: new Model(type: ProductUpdateDTO::class))]
    #[OA\Parameter(
        name: 'Authorization',
        description: 'Bearer token for authentication',
        in: 'header',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    public function updateProduct(
        #[MapRequestPayload]
        ProductUpdateDTO $productUpdateDTO,
        int $id,
        ProductImportService $productImportService
    ): Response {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        $product = $productImportService->importOneProduct($productUpdateDTO);

        return $this->json(data: $this->productRepository->normalize($product));
    }

    #[Route(
        '/{id<\d+>?1}',
        name: 'delete',
        methods: ['DELETE']
    )]
    #[OA\Delete(description: 'This route is accessible only to authenticated users with the ROLE_MANAGER role and above.')]
    #[OA\Tag(name: 'product')]
    #[OA\Response(
        response: 200,
        description: 'Deletes product',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    default: 'product removed: test product'
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not found error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    default: [
                        [
                            'type' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException',
                            'message' => 'No product found for id 178',
                        ],
                    ]
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Parameter(
        name: 'Authorization',
        description: 'Bearer token for authentication',
        in: 'header',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    public function deleteProduct(int $id): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $this->json(data: [
            'message' => 'product removed: '.$product->getName(),
        ]);
    }
}
