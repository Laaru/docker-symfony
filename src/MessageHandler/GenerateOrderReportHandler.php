<?php

namespace App\MessageHandler;

use App\Message\GenerateOrderReportMessage;
use App\Service\Order\OrderReportService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenerateOrderReportHandler
{
    public function __construct(
        private OrderReportService $orderReportService
    ) {}

    public function __invoke(GenerateOrderReportMessage $message): void
    {
        $reportId = $message->getReportId();
        $this->orderReportService->generateReport($reportId);
    }
}
