<?php

namespace App\Controller\Api;

use App\Entity\Basket;
use App\Entity\DTO\OrderCreateDTO;
use App\Entity\Order;
use App\Entity\User;
use App\Service\Order\OrderCreateService;
use App\Service\Order\OrderInitService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/api/order',
    name: 'api_order_',
    defaults: ['show_exception_as_json' => true]
)]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderCreateService $orderCreateService,
        private readonly OrderInitService $orderInitService
    ) {}

    #[Route('/init', name: 'init', methods: ['GET'])]
    #[OA\Tag(name: 'order')]
    #[OA\Response(
        response: 200,
        description: 'Returns checkout init form',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'basket',
                    ref: new Model(type: Basket::class, groups: ['basket']),
                ),
                new OA\Property(
                    property: 'deliveryOptions',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    default: [
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
                    ]
                ),
                new OA\Property(
                    property: 'paymentOptions',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    default: [
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
                    ]
                ),
            ],
            type: 'object'
        ),
    )]
    public function initCheckout(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(
            data: $this->orderInitService->init($user),
            context: ['groups' => ['order']]
        );
    }

    #[Route('/make', name: 'make', methods: ['POST'])]
    #[OA\Tag(name: 'order')]
    #[OA\RequestBody(content: new Model(type: OrderCreateDTO::class))]
    #[OA\Response(
        response: 200,
        description: 'Adds item to basket',
        content: new OA\JsonContent(
            ref: new Model(type: Order::class, groups: ['order'])
        )
    )]
    public function makeOrder(
        #[MapRequestPayload]
        OrderCreateDTO $orderCreateDTO
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $order = $this->orderCreateService->create($orderCreateDTO, $user);

        return $this->json(
            data: $order,
            context: ['groups' => ['order']]
        );
    }
}
