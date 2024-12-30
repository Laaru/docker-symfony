<?php

namespace App\Tests\Controller\Api;

use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderStatusControllerTest extends AbstractTestCase
{
    public function testChangeOrderStatus(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('external-api');
        $client->loginUser($testUser);
        $lastOrder = $this->getLastOrder();

        $statusExternalId = 7;

        $content = json_encode([
            'orderId' => $lastOrder->getId(),
            'statusId' => $statusExternalId,
        ]);
        $client->request(
            method: 'POST',
            uri: '/external_api/order_status',
            server: [
                'HTTP_auth-token' => 'external-api-secret-key',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: false === $content ? null : $content
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(
            $responseData['orderStatus']['externalId'],
            $statusExternalId,
            'Order status externalId must be ' . $statusExternalId
        );
    }
}
