<?php

namespace App\Tests\Controller\Api;

use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderStatusControllerTest extends WebTestCase
{
    public function testChangeOrderStatus(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('external-api');
        $client->loginUser($testUser);

        $orderRepository = static::getContainer()->get(OrderRepository::class);
        $lastOrder = $orderRepository->getLastOrder();

        $statusExternalid = 7;

        $client->request(
            method: 'POST',
            uri: '/external_api/order_status',
            server: [
                'HTTP_auth-token' => 'external-api-secret-key',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode([
                'orderId' => $lastOrder->getId(),
                'statusId' => $statusExternalid,
            ])
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(
            $responseData['orderStatus']['externalId'],
            $statusExternalid,
            'Order status externalId must be '.$statusExternalid
        );
    }
}
