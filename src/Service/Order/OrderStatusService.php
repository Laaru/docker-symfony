<?php

namespace App\Service\Order;

use App\Entity\DTO\OrderStatusUpdateDTO;
use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Repository\OrderRepository;
use App\Repository\OrderStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class OrderStatusService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderStatusRepository $orderStatusRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function changeStatus(OrderStatusUpdateDTO $orderStatusUpdateDTO): Order
    {

        /** @var null|Order $order */
        $order = $this->orderRepository->find($orderStatusUpdateDTO->orderId);
        if (!$order) {
            throw new HttpException(400, 'Order not found');
        }

        /** @var null|OrderStatus $status */
        $status = $this->orderStatusRepository->findOneByExternalId($orderStatusUpdateDTO->statusId);
        if (!$status) {
            throw new HttpException(400, 'Order status not found');
        }

        $order->setOrderStatus($status);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }
}
