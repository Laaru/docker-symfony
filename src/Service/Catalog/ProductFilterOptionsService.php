<?php

namespace App\Service\Catalog;

use App\Repository\ProductRepository;

class ProductFilterOptionsService
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    /**
     * @return array<mixed>
     */
    public function getFilterOptions(): array
    {
        return [
            'priceMin' => $this->productRepository->getMinPrice(),
            'priceMax' => $this->productRepository->getMaxPrice(),
            'colors' => $this->productRepository->getAvailableColors(),
            'stores' => $this->productRepository->getAvailableStores(),
        ];
    }
}
