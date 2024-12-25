<?php

namespace App\Service\Product;

use App\Entity\DTO\ProductUpdateDTO;
use App\Entity\Product;
use App\Repository\ColorRepository;
use App\Repository\ProductRepository;
use App\Repository\StoreRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductImportService
{
    private Slugify $slugify;
    private ColorRepository $colorRepository;
    private ProductRepository $productRepository;
    private StoreRepository $storeRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ProductRepository $productRepository,
        ColorRepository $colorRepository,
        StoreRepository $storeRepository,
        LoggerInterface $logger
    ) {
        $this->slugify = new Slugify();
        $this->colorRepository = $colorRepository;
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function importMultipleProducts(array $products): void
    {
        if (!is_array(current($products))) {
            $products = [$products];
        }

        $this->logger->info('Import started. Products to import: '.count($products), $products);
        $importedProducts = 0;

        foreach ($products as $productData) {
            try {
                $productDTO = $this->validateImportData($productData);
                $this->importOneProduct($productDTO);

                ++$importedProducts;
            } catch (\Exception $e) {
                $this->logger->warning('Failed to import product', [
                    'error' => $e->getMessage(),
                    'data' => $productData,
                ]);
            }
        }

        $this->logger->info('Import finished. Imported products: '.$importedProducts);
    }

    /**
     * @throws \Exception
     */
    public function importOneProduct(ProductUpdateDTO $productDTO): Product
    {
        $product = $this->prepareProductEntity($productDTO);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * @throws \Exception
     */
    private function validateImportData(array $productData): ProductUpdateDTO
    {
        $productDTO = new ProductUpdateDTO(
            externalId: $productData['id'] ?? $productData['external_id'] ?? null,
            name: $productData['name'] ?? null,
            basePrice: $productData['cost'] ?? $productData['base_price'] ?? $productData['basePrice'] ?? null,
            salePrice: $productData['sale_price'] ?? $productData['salePrice'] ?? null,
            description: $productData['description'] ?? null,
            colorExternalId: $productData['color'] ?? null,
            storesExternalIds: $productData['stores'] ?? $productData['inStockInStores'] ?? null,
            weight: $productData['measurements']['weight'] ?? $productData['weight'] ?? null,
            height: $productData['measurements']['height'] ?? $productData['height'] ?? null,
            width: $productData['measurements']['width'] ?? $productData['width'] ?? null,
            length: $productData['measurements']['length'] ?? $productData['length'] ?? null,
            tax: $productData['tax'] ?? null,
            version: $productData['version'] ?? null
        );

        $errors = $this->validator->validate($productDTO);
        if ($errors->count() > 0) {
            throw new UnprocessableEntityHttpException('', new ValidationFailedException('', $errors));
        }

        return $productDTO;
    }

    private function prepareProductEntity(ProductUpdateDTO $productDTO): Product
    {
        $product = $this->productRepository->findOneByExternalId($productDTO->externalId);

        if (!$product) {
            $product = new Product();
        }

        $product->setName($productDTO->name);
        $product->setSlug($this->slugify->slugify($productDTO->name).'_'.$productDTO->externalId);
        $product->setDescription($productDTO->description);
        $product->setLength($productDTO->length);
        $product->setWidth($productDTO->width);
        $product->setHeight($productDTO->height);
        $product->setWeight($productDTO->weight);
        $product->setExternalId($productDTO->externalId);
        $product->setBasePrice($productDTO->basePrice);
        $product->setSalePrice($productDTO->salePrice);
        $product->setTax($productDTO->tax);
        $product->setVersion($productDTO->version);

        if (!empty($productDTO->colorExternalId)) {
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
