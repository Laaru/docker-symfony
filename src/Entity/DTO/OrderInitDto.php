<?php

namespace App\Entity\DTO;

use App\Entity\Basket;
use Symfony\Component\Serializer\Attribute\Groups;

class OrderInitDto
{
    public function __construct(
        #[Groups(['order'])]
        private readonly Basket $basket,
        #[Groups(['order'])]
        private readonly array $deliveryOptions,
        #[Groups(['order'])]
        private readonly array $paymentOptions
    ) {}

    public function getBasket(): Basket
    {
        return $this->basket;
    }

    public function getDeliveryOptions(): array
    {
        return $this->deliveryOptions;
    }

    public function getPaymentOptions(): array
    {
        return $this->paymentOptions;
    }
}
