<?php

namespace App\Service\Payment;

class PaymentService
{
    public const array STUBS = [
        [
            'id' => 1,
            'name' => 'Оплата 1',
            'slug' => 'payment-1',
        ],
        [
            'id' => 2,
            'name' => 'Оплата 2',
            'slug' => 'payment-2',
        ],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPaymentOptions(): array
    {
        return $this::STUBS;
    }
}
