<?php

namespace App\Controller\Api;

use App\Entity\Basket;
use App\Entity\DTO\BasketItemRemoveDTO;
use App\Entity\DTO\BasketItemUpdateDTO;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        private readonly BasketRepository $basketRepository,
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager
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
        $user = $this->getUser();
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);
        if (!$basket) {
            $basket = new Basket();
            $basket->setUserRelation($user);
            $this->entityManager->persist($basket);
            $this->entityManager->flush();
        }

        return $this->json($this->basketRepository->normalize($basket, 'basket'));
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
        $user = $this->getUser();
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);

        if (!$basket) {
            $basket = new Basket();
            $basket->setUserRelation($user);
            $this->entityManager->persist($basket);
        }

        $product = $this->productRepository->find($basketItemUpdateDTO->productId);
        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $basket->createAndAddItem($product, $basketItemUpdateDTO->quantity);
        $this->entityManager->persist($basket);
        $this->entityManager->flush();

        return $this->json($this->basketRepository->normalize($basket, 'basket'));
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
        $user = $this->getUser();
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);

        if (!$basket) {
            $basket = new Basket();
            $basket->setUserRelation($user);
            $this->entityManager->persist($basket);
        }

        $product = $this->productRepository->find($basketItemUpdateDTO->productId);
        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $basket->updateItem($product->getId(), $basketItemUpdateDTO->quantity);
        $this->entityManager->persist($basket);
        $this->entityManager->flush();

        return $this->json($this->basketRepository->normalize($basket, 'basket'));
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
        $user = $this->getUser();
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);

        if (!$basket || $basket->isEmpty()) {
            throw new HttpException(400, 'Basket is empty');
        }

        $product = $this->productRepository->find($basketItemRemoveDTO->productId);
        if (!$product) {
            throw new HttpException(400, 'Product not found');
        }

        $basket->removeItemByProductId($product->getId());
        $this->entityManager->persist($basket);
        $this->entityManager->flush();

        return $this->json($this->basketRepository->normalize($basket, 'basket'));
    }
}
