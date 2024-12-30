<?php

namespace App\Service\Order;

use App\Entity\Basket;
use App\Entity\DTO\OrderCreateDTO;
use App\Entity\Order;
use App\Entity\User;
use App\Event\OrderCreatedEvent;
use App\Repository\BasketRepository;
use App\Repository\OrderStatusRepository;
use App\Service\Delivery\DeliveryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class OrderCreateService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BasketRepository $basketRepository,
        private readonly OrderStatusRepository $orderStatusRepository,
        private readonly OrderRestrictionService $orderRestrictionService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DeliveryService $deliveryService,
    ) {}

    public function create(OrderCreateDTO $orderCreateDTO, User $user): Order
    {
        /** @var Basket $basket */
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);
        $this->orderRestrictionService->checkRestriction($basket);

        $order = new Order();
        $order->setUserRelation($user);
        $order->setPhone($orderCreateDTO->phone);
        $order->setOrderStatus($this->orderStatusRepository->getInitialStatus());
        $order->setDeliveryId($orderCreateDTO->deliveryId);
        $order->setPaymentId($orderCreateDTO->paymentId);
        $order->setDeliveryAddress($orderCreateDTO->deliveryAddress);
        $order->setDeliveryAddressKladrId($orderCreateDTO->deliveryAddressKladrId);
        foreach ($basket->getItems() as $basketItem) {
            $product = $basketItem->getProduct();
            if (!$product) {
                continue;
            }
            $order->createAndAddItem(
                $product,
                $basketItem->getQuantity(),
                $product->getSalePrice() ?: $product->getBasePrice()
            );
        }
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new OrderCreatedEvent(
                $user->getEmail(),
                $order->getPhone(),
                $order->getId(),
                $this->deliveryService->getDeliverySlugById($order->getDeliveryId()),
                $order->getDeliveryAddressKladrId(),
                $order->getDeliveryAddress(),
                $order->getItems(),
            )
        );

        return $order;
    }
}
