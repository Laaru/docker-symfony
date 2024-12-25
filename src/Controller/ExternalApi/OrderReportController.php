<?php

namespace App\Controller\ExternalApi;

use App\Service\Order\OrderReportService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/external_api',
    name: 'api_util_',
    defaults: ['show_exception_as_json' => true]
)]
#[OA\Tag(name: 'external_api')]
#[OA\Response(
    response: 200,
    description: 'Initiates order report',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'reportId',
                type: 'string',
                default: '1e7d1add-1873-4081-8fe4-9a103697f969'
            ),
        ],
        type: 'object'
    )
)]
#[OA\Parameter(
    name: 'auth-token',
    description: 'Custom auth token for authentication',
    in: 'header',
    required: true,
    schema: new OA\Schema(type: 'string')
)]
class OrderReportController extends AbstractController
{
    #[Route('/order_report', name: 'init_order_report', methods: ['GET'])]
    public function initOrderReport(
        OrderReportService $orderReportService
    ): JsonResponse {
        $reportId = $orderReportService->launchReportGenerationMessage();

        return $this->json(data: ['reportId' => $reportId]);
    }
}
