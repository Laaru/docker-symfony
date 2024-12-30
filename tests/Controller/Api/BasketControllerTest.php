<?php

namespace App\Tests\Controller\Api;

use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class BasketControllerTest extends AbstractTestCase
{
    public function testBasketView(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);

        $client->request(
            method: 'GET',
            uri: '/api/basket/'
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testBasketItemUpdate(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $this->prepareValidBasketForUser($testUser);
        $productId = $this->getFirstBasketItemProductId($testUser);

        $content = json_encode([
            'productId' => $productId,
            'quantity' => mt_rand(1, 5),
        ]);
        $client->request(
            method: 'PUT',
            uri: '/api/basket/update',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: false === $content ? null : $content
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testBasketItemAdd(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $product = $this->getRandomProduct();

        $content = json_encode([
            'productId' => $product->getId(),
            'quantity' => mt_rand(1, 5),
        ]);
        $client->request(
            method: 'POST',
            uri: '/api/basket/add',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: false === $content ? null : $content
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testBasketItemRemove(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $productId = $this->getFirstBasketItemProductId($testUser);

        $content = json_encode([
            'productId' => $productId,
        ]);
        $client->request(
            method: 'DELETE',
            uri: '/api/basket/remove',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: false === $content ? null : $content
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('id', $responseData);

        $foundItems = array_filter($responseData['items'], function ($item) use ($productId) {
            return $item['product']['id'] === $productId;
        });
        $this->assertCount(0, $foundItems, "Product with ID $productId should not be in the basket items.");
    }
}
