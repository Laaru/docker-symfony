<?php

namespace App\Service\Delivery;

class DeliveryService
{
    public const array STUBS = [
        [
            'id' => 1,
            'name' => 'Курьер',
            'slug' => 'courier',
        ],
        [
            'id' => 2,
            'name' => 'Самовывоз',
            'slug' => 'selfdelivery',
        ],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getDeliveryOptions(): array
    {
        return $this::STUBS;
    }

    public function getDeliverySlugById(int $id): string
    {
        foreach ($this::STUBS as $delivery) {
            if ($delivery['id'] === $id) {
                return $delivery['slug'];
            }
        }

        return '';
    }
}
