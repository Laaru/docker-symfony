<?php

namespace App\Service\Order;

use App\Entity\User;
use App\Repository\BasketRepository;
use App\Service\Delivery\DeliveryService;
use App\Service\Payment\PaymentService;

class OrderInitService
{
    public function __construct(
        private readonly BasketRepository $basketRepository,
        private readonly OrderRestrictionService $orderRestrictionService,
        private readonly DeliveryService $deliveryService,
        private readonly PaymentService $paymentService
    ) {}

    public function init(User $user): array
    {
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);
        $this->orderRestrictionService->checkRestriction($basket);

        return [
            'basket' => $this->basketRepository->normalize($basket, 'basket'),
            'deliveryOptions' => $this->deliveryService->getDeliveryOptions(),
            'paymentOptions' => $this->paymentService->getPaymentOptions(),
        ];
    }
}
