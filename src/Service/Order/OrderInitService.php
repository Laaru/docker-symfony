<?php

namespace App\Service\Order;

use App\Entity\Basket;
use App\Entity\DTO\OrderInitDto;
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

    public function init(User $user): OrderInitDto
    {
        /** @var Basket $basket */
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);
        $this->orderRestrictionService->checkRestriction($basket);

        return new OrderInitDto(
            $basket,
            $this->deliveryService->getDeliveryOptions(),
            $this->paymentService->getPaymentOptions()
        );
    }
}
