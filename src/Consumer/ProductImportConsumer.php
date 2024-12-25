<?php

namespace App\Consumer;

use App\Service\Product\ProductImportService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Psr\Log\LoggerInterface;

class ProductImportConsumer implements ConsumerInterface
{
    private ProductImportService $productImportService;
    private LoggerInterface $logger;

    public function __construct(ProductImportService $productImportService, LoggerInterface $logger)
    {
        $this->productImportService = $productImportService;
        $this->logger = $logger;
        $this->logger->info('products consumer started');
    }

    public function execute($msg): void
    {
        $body = $msg->getBody();
        $data = json_decode($body, true);
        $this->productImportService->importMultipleProducts($data);
    }
}
