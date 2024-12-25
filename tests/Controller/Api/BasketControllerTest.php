<?php

namespace App\Tests\Controller\Api;

use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BasketControllerTest extends WebTestCase
{
    public function testBasketView(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin');
        $client->loginUser($testUser);

        $client->request(
            method: 'GET',
            uri: '/api/basket/'
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testBasketItemUpdate(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin');
        $client->loginUser($testUser);

        $productRepository = static::getContainer()->get(ProductRepository::class);
        $product = $productRepository->getRandomProduct();

        $client->request(
            method: 'PUT',
            uri: '/api/basket/update',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'productId' => $product->getId(),
                'quantity' => mt_rand(1, 5),
            ])
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testBasketItemAdd(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin');
        $client->loginUser($testUser);

        $productRepository = static::getContainer()->get(ProductRepository::class);
        $product = $productRepository->getRandomProduct();

        $client->request(
            method: 'POST',
            uri: '/api/basket/add',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'productId' => $product->getId(),
                'quantity' => mt_rand(1, 5),
            ])
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testBasketItemRemove(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin');
        $client->loginUser($testUser);

        $basketRepository = static::getContainer()->get(BasketRepository::class);
        $basket = $basketRepository->findOneBy(['userRelation' => $testUser]);
        $basketItems = $basket->getItems();
        $firstBasketItem = $basketItems->current();
        $productId = $firstBasketItem->getProduct()->getId();

        $client->request(
            method: 'DELETE',
            uri: '/api/basket/remove',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'productId' => $productId,
            ])
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);

        $foundItems = array_filter($responseData['items'], function ($item) use ($productId) {
            return $item['product']['id'] === $productId;
        });
        $this->assertCount(0, $foundItems, "Product with ID $productId should not be in the basket items.");
    }
}
