<?php

namespace App\Tests\Controller\Api;

use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends AbstractTestCase
{
    public function testProductRead(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $product = $this->getOneProductByExternalId(1);

        $client->request(
            method: 'GET',
            uri: '/api/product/' . $product->getId()
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($product->getId(), $responseData['id'], 'ID in response does not match the expected value.');
    }

    public function testProductUpdate(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $product = $this->getRandomProduct();

        $content = json_encode([
            'externalId' => $product->getExternalId(),
            'name' => 'test product',
            'basePrice' => mt_rand(1000, 9999),
            'colorExternalId' => mt_rand(1, 10),
            'inStockInStores' => [mt_rand(1, 10)],
            'weight' => mt_rand(1, 100),
            'height' => mt_rand(1, 100),
            'width' => mt_rand(1, 100),
            'length' => mt_rand(1, 100),
            'tax' => 20,
            'version' => 1,
        ]);

        $client->request(
            method: 'PUT',
            uri: '/api/product/' . $product->getId(),
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
        $this->assertEquals($product->getId(), $responseData['id'], 'ID in response does not match the expected value.');
    }

    public function testProductCreate(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);

        $content = json_encode([
            'externalId' => mt_rand(1000, 999999999),
            'name' => 'test product',
            'basePrice' => mt_rand(1000, 9999),
            'colorExternalId' => mt_rand(1, 10),
            'inStockInStores' => [mt_rand(1, 10)],
            'weight' => mt_rand(1, 100),
            'height' => mt_rand(1, 100),
            'width' => mt_rand(1, 100),
            'length' => mt_rand(1, 100),
            'tax' => 20,
            'version' => 1,
        ]);

        $client->request(
            method: 'POST',
            uri: '/api/product/',
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

    public function testProductDelete(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $product = $this->getRandomProduct();

        $client->request(
            method: 'DELETE',
            uri: '/api/product/' . $product->getId()
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('message', $responseData);
    }
}
