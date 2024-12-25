<?php

namespace App\Tests\Controller\Api;

use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
{
    public function testOrderInit(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin');
        $client->loginUser($testUser);

        $basketRepository = static::getContainer()->get(BasketRepository::class);
        $basket = $basketRepository->findOneBy(['userRelation' => $testUser]);
        if ($basket->isEmpty() || $basket->getTotalItemsCount() > 20) {
            $basketItems = $basket->getItems();
            foreach ($basketItems as $basketItem) {
                $basket->removeItem($basketItem);
            }

            $productRepository = static::getContainer()->get(ProductRepository::class);
            $product = $productRepository->getRandomProduct();
            $basket->createAndAddItem($product, mt_rand(1, 5));

            $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

            $entityManager->persist($basket);
            $entityManager->flush();
        }

        $client->request(
            method: 'GET',
            uri: '/api/order/init'
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('basket', $responseData);
        $this->assertArrayHasKey('deliveryOptions', $responseData);
        $this->assertArrayHasKey('paymentOptions', $responseData);
    }

    public function testOrderMake(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin');
        $client->loginUser($testUser);

        $basketRepository = static::getContainer()->get(BasketRepository::class);
        $basket = $basketRepository->findOneBy(['userRelation' => $testUser]);
        if ($basket->isEmpty() || $basket->getTotalItemsCount() > 20) {
            $basketItems = $basket->getItems();
            foreach ($basketItems as $basketItem) {
                $basket->removeItem($basketItem);
            }

            $productRepository = static::getContainer()->get(ProductRepository::class);
            $product = $productRepository->getRandomProduct();
            $basket->createAndAddItem($product, mt_rand(1, 5));

            $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

            $entityManager->persist($basket);
            $entityManager->flush();
        }

        $client->request(
            method: 'POST',
            uri: '/api/order/make',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'phone' => '88005553535',
                'deliveryId' => mt_rand(1, 2),
                'paymentId' => mt_rand(1, 2),
                'deliveryAddress' => '847 Spencer Alley Apt. 040',
                'deliveryAddressKladrId' => '92873983',
            ])
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('orderStatus', $responseData);
        $this->assertArrayHasKey('items', $responseData);
    }
}
