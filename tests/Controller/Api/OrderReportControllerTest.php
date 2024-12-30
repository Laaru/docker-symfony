<?php

namespace App\Tests\Controller\Api;

use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderReportControllerTest extends AbstractTestCase
{
    public function testInitOrderReport(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('external-api');
        $client->loginUser($testUser);

        $client->request(
            method: 'GET',
            uri: '/external_api/order_report',
            server: ['HTTP_auth-token' => 'external-api-secret-key']
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('reportId', $responseData);
    }
}
