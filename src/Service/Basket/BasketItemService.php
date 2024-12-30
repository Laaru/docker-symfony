<?php

namespace App\Service\Basket;

use App\Entity\Basket;
use App\Entity\DTO\BasketItemRemoveDTO;
use App\Entity\DTO\BasketItemUpdateDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class BasketItemService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function addItem(Basket $basket, BasketItemUpdateDTO $dto): void
    {
        /** @var Product $product */
        $product = $this->productRepository->find($dto->productId);

        if (!$product) {
            throw new \InvalidArgumentException('Product not found');
        }

        $basket->createAndAddItem($product, $dto->quantity);
        $this->entityManager->persist($basket);
        $this->entityManager->flush();
    }

    public function updateItem(Basket $basket, BasketItemUpdateDTO $dto): void
    {
        $product = $this->productRepository->find($dto->productId);

        if (!$product) {
            throw new \InvalidArgumentException('Product not found', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $basketItem = $basket->getItemByProductId($dto->productId);
        if (!$basketItem) {
            throw new \InvalidArgumentException('Product not in basket', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $basket->setItemQuantity($basketItem, $dto->quantity);
        $this->entityManager->persist($basket);
        $this->entityManager->flush();
    }

    public function removeItem(Basket $basket, BasketItemRemoveDTO $dto): void
    {
        /** @var Product $product */
        $product = $this->productRepository->find($dto->productId);

        if (!$product) {
            throw new \InvalidArgumentException('Product not found', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $basket->removeItemByProductId($product->getId());
        $this->entityManager->persist($basket);
        $this->entityManager->flush();
    }
}
