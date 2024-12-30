<?php

namespace App\Tests;

use App\Entity\Basket;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\BasketRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractTestCase extends WebTestCase
{
    public function getTestUserByEmail(string $email): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => $email]);
        if (!$testUser instanceof User) {
            throw new \RuntimeException('User not found or invalid user type.');
        }

        return $testUser;
    }

    public function getOneProductByExternalId(int $id): Product
    {
        /** @var ProductRepository $productRepository */
        $productRepository = static::getContainer()->get(ProductRepository::class);
        /** @var Product $product */
        $product = $productRepository->findOneByExternalId($id);

        if (!$product) {
            throw new \RuntimeException('Product not found');
        }

        return $product;
    }

    public function getRandomProduct(): Product
    {
        /** @var ProductRepository $productRepository */
        $productRepository = static::getContainer()->get(ProductRepository::class);
        /** @var Product $product */
        $product = $productRepository->getRandomProduct();

        if (!$product) {
            throw new \RuntimeException('Product not found');
        }

        return $product;
    }

    public function getLastOrder(): Order
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = static::getContainer()->get(OrderRepository::class);
        /** @var Order $lastOrder */
        $lastOrder = $orderRepository->getLastOrder();

        if (!$lastOrder) {
            throw new \RuntimeException('Order not found');
        }

        return $lastOrder;
    }

    public function prepareValidBasketForUser(User $testUser): void
    {
        /** @var BasketRepository $basketRepository */
        $basketRepository = static::getContainer()->get(BasketRepository::class);
        /** @var Basket $basket */
        $basket = $basketRepository->findOneBy(['userRelation' => $testUser]);
        if (!$basket) {
            $basket = new Basket();
            $basket->setUserRelation($testUser);
        }
        if ($basket->isEmpty() || $basket->getTotalItemsCount() > 20) {
            $basketItems = $basket->getItems();
            foreach ($basketItems as $basketItem) {
                $basket->removeItem($basketItem);
            }

            $product = $this->getRandomProduct();
            $basket->createAndAddItem($product, mt_rand(1, 5));

            /** @var EntityManager $entityManager */
            $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

            $entityManager->persist($basket);
            $entityManager->flush();
        }
    }

    public function getFirstBasketItemProductId(User $testUser): int
    {
        /** @var BasketRepository $basketRepository */
        $basketRepository = static::getContainer()->get(BasketRepository::class);
        /** @var Basket $basket */
        $basket = $basketRepository->findOneBy(['userRelation' => $testUser]);
        $basketItems = $basket->getItems();
        $firstBasketItem = $basketItems->current();
        if (!$firstBasketItem) {
            throw new \RuntimeException('Basket item not found');
        }

        $product = $firstBasketItem->getProduct();

        if (!$product) {
            throw new \RuntimeException('Product not found');
        }

        return $product->getId();
    }
}
