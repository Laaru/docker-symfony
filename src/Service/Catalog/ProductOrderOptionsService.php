<?php

namespace App\Service\Catalog;

class ProductOrderOptionsService
{
    /**
     * @return array<string, mixed>
     */
    public function getOrderOptions(): array
    {
        return [
            'fields' => ['basePrice', 'updatedAt'],
            'directions' => ['asc', 'desc'],
        ];
    }
}
