<?php

namespace App\Tests\Controller\Api;

use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends AbstractTestCase
{
    public function testOrderInit(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $this->prepareValidBasketForUser($testUser);

        $client->request(
            method: 'GET',
            uri: '/api/order/init'
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('basket', $responseData);
        $this->assertArrayHasKey('deliveryOptions', $responseData);
        $this->assertArrayHasKey('paymentOptions', $responseData);
    }

    public function testOrderMake(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);
        $this->prepareValidBasketForUser($testUser);

        $content = json_encode([
            'phone' => '88005553535',
            'deliveryId' => mt_rand(1, 2),
            'paymentId' => mt_rand(1, 2),
            'deliveryAddress' => '847 Spencer Alley Apt. 040',
            'deliveryAddressKladrId' => '92873983',
        ]);
        $client->request(
            method: 'POST',
            uri: '/api/order/make',
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
        $this->assertArrayHasKey('orderStatus', $responseData);
        $this->assertArrayHasKey('items', $responseData);
    }
}
