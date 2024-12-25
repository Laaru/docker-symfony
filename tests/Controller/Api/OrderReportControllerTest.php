<?php

namespace App\Tests\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderReportControllerTest extends WebTestCase
{
    public function testInitOrderReport(): void
    {
        $client = self::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('external-api');
        $client->loginUser($testUser);

        $client->request(
            method: 'GET',
            uri: '/external_api/order_report',
            server: ['HTTP_auth-token' => 'external-api-secret-key']
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('reportId', $responseData);
    }
}
