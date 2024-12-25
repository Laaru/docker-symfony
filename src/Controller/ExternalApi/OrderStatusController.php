<?php

namespace App\Controller\ExternalApi;

use App\Entity\DTO\OrderStatusUpdateDTO;
use App\Repository\OrderRepository;
use App\Repository\OrderStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/external_api',
    name: 'api_util_',
    defaults: ['show_exception_as_json' => true]
)]
class OrderStatusController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderStatusRepository $orderStatusRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/order_status', name: 'change_order_status', methods: ['POST'])]
    #[OA\Tag(name: 'external_api')]
    #[OA\RequestBody(content: new Model(type: OrderStatusUpdateDTO::class))]
    #[OA\Parameter(
        name: 'auth-token',
        description: 'Custom auth token for authentication',
        in: 'header',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    public function changeOrderStatus(
        #[MapRequestPayload]
        OrderStatusUpdateDTO $orderStatusUpdateDTO
    ): JsonResponse {
        $order = $this->orderRepository->find($orderStatusUpdateDTO->orderId);
        if (!$order) {
            throw new HttpException(400, 'Order not found');
        }

        $status = $this->orderStatusRepository->findOneByExternalId($orderStatusUpdateDTO->statusId);
        if (!$status) {
            throw new HttpException(400, 'Order status not found');
        }

        $order->setOrderStatus($status);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json(data: $this->orderRepository->normalize($order, 'order'));
    }
}
