<?php

namespace App\Factory;

use App\Entity\DTO\ProductUpdateDTO;

class ProductUpdateDTOFactory
{
    /**
     * @param array<mixed> $productData
     */
    public function createFromArray(array $productData): ProductUpdateDTO
    {
        return new ProductUpdateDTO(
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
            tax: $productData['tax'] ?? null
        );
    }
}
