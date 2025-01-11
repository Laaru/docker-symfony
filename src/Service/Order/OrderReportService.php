<?php

namespace App\Service\Order;

use App\Entity\OrderItem;
use App\Message\GenerateOrderReportMessage;
use App\Producer\KafkaProducerService;
use App\Repository\OrderRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class OrderReportService
{
    private const int CHUNK_SIZE = 10;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly KafkaProducerService $kafkaProducerService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly OrderRepository $orderRepository,
        private readonly string $kafkaTopic
    ) {}

    public function launchReportGenerationMessage(): string
    {
        $reportId = Uuid::v4();
        $this->bus->dispatch(new GenerateOrderReportMessage($reportId));

        return $reportId;
    }

    public function generateReport(string $reportId): void
    {
        try {
            $kernelDir = $this->parameterBag->get('kernel.project_dir');
            if (!is_string($kernelDir) || empty($kernelDir)) {
                $kernelDir = dirname(__DIR__);
            }
            $reportDirectory = $kernelDir . '/public/reports/orders/';
            $reportFilePath = $reportDirectory . $reportId . '.json';

            $filesystem = new Filesystem();
            if (!$filesystem->exists($reportDirectory)) {
                try {
                    $filesystem->mkdir($reportDirectory);
                } catch (IOExceptionInterface $e) {
                    throw new \RuntimeException('Dir creation error: ' . $e->getMessage());
                }
            }

            $fileHandle = fopen($reportFilePath, 'w');
            if (!$fileHandle) {
                throw new \RuntimeException('Unable to open file for writing: ' . $reportFilePath);
            }

            fwrite($fileHandle, '[');
            $isFirstItem = true;

            $ordersChunk = $this->orderRepository->getAllOrdersViaGenerator($this::CHUNK_SIZE);
            foreach ($ordersChunk as $orders) {
                foreach ($orders as $order) {
                    /* @var $orderItem OrderItem */
                    foreach ($order->getItems() as $orderItem) {
                        $product = $orderItem->getProduct();
                        if (!$product) {
                            continue;
                        }
                        $user = $order->getUserRelation();
                        if (!$user) {
                            continue;
                        }

                        if (!$isFirstItem) {
                            fwrite($fileHandle, ',');
                        }

                        $reportData = [
                            'product_name' => $product->getName(),
                            'price' => $orderItem->getPrice(),
                            'amount' => $orderItem->getQuantity(),
                            'user' => ['id' => $user->getId()],
                        ];

                        fwrite($fileHandle, json_encode($reportData, JSON_THROW_ON_ERROR));
                        $isFirstItem = false;
                    }
                }
            }

            fwrite($fileHandle, ']');
            fclose($fileHandle);

            $dispatchMessage = [
                'reportId' => $reportId,
                'result' => 'success',
            ];
        } catch (\Exception $e) {
            $dispatchMessage = [
                'reportId' => $reportId,
                'result' => 'fail',
                'detail' => [
                    'error' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ];
        }

        $this->kafkaProducerService->sendMessage($dispatchMessage, $this->kafkaTopic);
    }
}
