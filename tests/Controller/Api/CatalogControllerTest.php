<?php

namespace App\Tests\Controller\Api;

use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class CatalogControllerTest extends AbstractTestCase
{
    public function testCatalogRead(): void
    {
        $client = self::createClient();

        $testUser = $this->getTestUserByEmail('admin');
        $client->loginUser($testUser);

        $client->request(
            method: 'GET',
            uri: '/api/catalog/',
            parameters: [
                'page' => 1,
                'order' => 'desc',
                'sort' => 'updatedAt',
            ]
        );
        $response = $client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = $response->getContent()
            ? json_decode($response->getContent(), true)
            : [];
        $this->assertArrayHasKey('catalog', $responseData);
        $this->assertArrayHasKey('filterOptions', $responseData);
        $this->assertArrayHasKey('orderOptions', $responseData);
    }
}
