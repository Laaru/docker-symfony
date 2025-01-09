<?php

namespace App\Service\Product;

use App\Entity\Color;
use App\Entity\DTO\Collection\ProductDTOCollection;
use App\Entity\DTO\ProductUpdateDTO;
use App\Entity\Product;
use App\Repository\ColorRepository;
use App\Repository\ProductRepository;
use App\Repository\StoreRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductImportService
{
    private Slugify $slugify;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly ProductRepository $productRepository,
        private readonly ColorRepository $colorRepository,
        private readonly StoreRepository $storeRepository,
        private readonly LoggerInterface $logger
    ) {
        $this->slugify = new Slugify();
    }

    public function importMultipleProducts(ProductDTOCollection $products): void
    {
        $this->logger->info('Import started. Products to import: ' . $products->count());
        $importedProducts = 0;

        /** @var ProductUpdateDTO $productUpdateDTO */
        foreach ($products as $productUpdateDTO) {
            try {
                $this->validateProductUpdateDTO($productUpdateDTO);
                $this->importOneProduct($productUpdateDTO);

                ++$importedProducts;
            } catch (\Throwable $e) {
                $this->logger->warning('Failed to import product', [
                    'error' => $e->getMessage(),
                    'data' => $productUpdateDTO,
                ]);
            }
        }

        $this->entityManager->clear();

        $this->logger->info('Import finished. Imported products: ' . $importedProducts);
    }

    /**
     * @throws \Exception
     */
    public function importOneProduct(ProductUpdateDTO $productDTO, ?int $productId = null): Product
    {
        $product = $this->prepareProductEntity($productDTO, $productId);
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * @throws \Exception
     */
    private function validateProductUpdateDTO(ProductUpdateDTO $productUpdateDTO): void
    {
        $errors = $this->validator->validate($productUpdateDTO);
        if ($errors->count() > 0) {
            throw new ValidationFailedException('', $errors);
        }
    }

    private function prepareProductEntity(ProductUpdateDTO $productDTO, ?int $productId = null): Product
    {
        if ($productId) {
            /** @var Product $product */
            $product = $this->productRepository->find($productId);
        } else {
            /** @var Product $product */
            $product = $this->productRepository->findOneByExternalId($productDTO->externalId);
        }

        if (!$product) {
            $product = new Product();
        }

        $product->setName($productDTO->name);
        $product->setSlug($this->slugify->slugify($productDTO->name) . '_' . $productDTO->externalId);
        $product->setDescription($productDTO->description);
        $product->setLength($productDTO->length);
        $product->setWidth($productDTO->width);
        $product->setHeight($productDTO->height);
        $product->setWeight($productDTO->weight);
        $product->setExternalId($productDTO->externalId);
        $product->setBasePrice($productDTO->basePrice);
        $product->setSalePrice($productDTO->salePrice);
        $product->setTax($productDTO->tax);

        if (!empty($productDTO->colorExternalId)) {
            /** @var Color $color */
            $color = $this->colorRepository->findOneByExternalId($productDTO->colorExternalId);
            if ($color) {
                $product->setColor($color);
            } else {
                $this->logger->warning("Color with externalId {$productDTO->colorExternalId} not found");
            }
        }

        if (!empty($productDTO->storesExternalIds)) {
            $newStores = $this->storeRepository->findManyByExternalIds($productDTO->storesExternalIds);

            $missingStores = array_diff(
                $productDTO->storesExternalIds,
                array_map(fn ($store) => $store->getExternalId(), $newStores)
            );
            if (!empty($missingStores)) {
                foreach ($missingStores as $missingStoreId) {
                    $this->logger->warning("Store with externalId $missingStoreId not found");
                }
            }

            $currentStores = $product->getStores();
            foreach ($currentStores as $currentStore) {
                if (!in_array($currentStore, $newStores, true)) {
                    $product->removeInStockInStore($currentStore);
                }
            }

            foreach ($newStores as $store) {
                $product->addInStockInStore($store);
            }
        }

        return $product;
    }
}
