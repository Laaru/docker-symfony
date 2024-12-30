<?php

namespace App\Controller\Api;

use App\Entity\Basket;
use App\Entity\DTO\BasketItemRemoveDTO;
use App\Entity\DTO\BasketItemUpdateDTO;
use App\Entity\User;
use App\Service\Basket\BasketItemService;
use App\Service\Basket\BasketService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/api/basket',
    name: 'api_basket_',
    defaults: ['show_exception_as_json' => true]
)]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BasketController extends AbstractController
{
    public function __construct(
        private readonly BasketService $basketService,
        private readonly BasketItemService $basketItemService
    ) {}

    #[Route('/', name: 'view', methods: ['GET'])]
    #[OA\Tag(name: 'basket')]
    #[OA\Response(
        response: 200,
        description: 'Returns user basket',
        content: new OA\JsonContent(
            ref: new Model(type: Basket::class, groups: ['basket'])
        )
    )]
    public function view(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $basket = $this->basketService->getUserBasket($user);

        return $this->json(
            data: $basket,
            context: ['groups' => ['basket']]
        );
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    #[OA\Tag(name: 'basket')]
    #[OA\RequestBody(content: new Model(type: BasketItemUpdateDTO::class))]
    #[OA\Response(
        response: 200,
        description: 'Adds item to basket',
        content: new OA\JsonContent(
            ref: new Model(type: Basket::class, groups: ['basket'])
        )
    )]
    public function add(
        #[MapRequestPayload]
        BasketItemUpdateDTO $basketItemUpdateDTO
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $basket = $this->basketService->getUserBasket($user);
        $this->basketItemService->addItem($basket, $basketItemUpdateDTO);

        return $this->json(
            data: $basket,
            context: ['groups' => ['basket']]
        );
    }

    #[Route('/update', name: 'update', methods: ['PUT'])]
    #[OA\Tag(name: 'basket')]
    #[OA\RequestBody(content: new Model(type: BasketItemUpdateDTO::class))]
    #[OA\Response(
        response: 200,
        description: 'Updates item in basket',
        content: new OA\JsonContent(
            ref: new Model(type: Basket::class, groups: ['basket'])
        )
    )]
    public function update(
        #[MapRequestPayload]
        BasketItemUpdateDTO $basketItemUpdateDTO
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $basket = $this->basketService->getUserBasket($user);
        $this->basketItemService->updateItem($basket, $basketItemUpdateDTO);

        return $this->json(
            data: $basket,
            context: ['groups' => ['basket']]
        );
    }

    #[Route('/remove', name: 'remove', methods: ['DELETE'])]
    #[OA\Tag(name: 'basket')]
    #[OA\RequestBody(content: new Model(type: BasketItemUpdateDTO::class))]
    #[OA\Response(
        response: 200,
        description: 'Removes items from basket',
        content: new OA\JsonContent(
            ref: new Model(type: Basket::class, groups: ['basket'])
        )
    )]
    public function remove(
        #[MapRequestPayload]
        BasketItemRemoveDTO $basketItemRemoveDTO
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $basket = $this->basketService->getUserBasket($user);
        $this->basketItemService->removeItem($basket, $basketItemRemoveDTO);

        return $this->json(
            data: $basket,
            context: ['groups' => ['basket']]
        );
    }
}
