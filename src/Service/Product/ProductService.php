<?php

namespace App\Service\Product;

use App\Entity\DTO\ProductUpdateDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private ProductImportService $productImportService,
    ) {}

    public function createProduct(ProductUpdateDTO $productUpdateDTO): Product
    {
        $product = $this->getProductByExternalId($productUpdateDTO->externalId);
        if ($product) {
            throw new ConflictHttpException("Product with externalId $productUpdateDTO->externalId already exists.");
        }

        return $this->productImportService->importOneProduct($productUpdateDTO);
    }

    public function readProduct(int $id): Product
    {
        $product = $this->getProductById($id);
        if (!$product) {
            throw new NotFoundHttpException("No product found for id $id");
        }

        return $product;
    }

    public function updateProduct(int $id, ProductUpdateDTO $productUpdateDTO): Product
    {
        $product = $this->getProductById($id);
        if (!$product) {
            throw new NotFoundHttpException("No product found for id $id");
        }

        return $this->productImportService->importOneProduct($productUpdateDTO);
    }

    public function deleteProduct(int $id): void
    {
        $product = $this->getProductById($id);
        if (!$product) {
            throw new NotFoundHttpException("No product found for id $id");
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    private function getProductByExternalId(int $externalId): ?Product
    {
        /** @var null|Product $product */
        $product = $this->productRepository->findOneByExternalId($externalId);

        return $product;
    }

    private function getProductById(int $id): ?Product
    {
        /** @var null|Product $product */
        $product = $this->productRepository->find($id);

        return $product;
    }
}
