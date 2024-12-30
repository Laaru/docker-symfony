<?php

namespace App\Controller\ExternalApi;

use App\Entity\DTO\OrderStatusUpdateDTO;
use App\Service\Order\OrderStatusService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/external_api',
    name: 'api_util_',
    defaults: ['show_exception_as_json' => true]
)]
class OrderStatusController extends AbstractController
{
    public function __construct(
        private readonly OrderStatusService $orderStatusService
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

        $order = $this->orderStatusService->changeStatus($orderStatusUpdateDTO);

        return $this->json(
            data: $order,
            context: ['groups' => ['order']]
        );
    }
}
