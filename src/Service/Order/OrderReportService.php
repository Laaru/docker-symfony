<?php

namespace App\Service\Order;

use App\Entity\Order;
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

            $reportData = [];
            /** @var Order[] $orders */
            $orders = $this->orderRepository->findAll();
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
                    $reportData[] = [
                        'product_name' => $product->getName(),
                        'price' => $orderItem->getPrice(),
                        'amount' => $orderItem->getQuantity(),
                        'user' => ['id' => $user->getId()],
                    ];
                }
            }

            file_put_contents($reportFilePath, json_encode($reportData));

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
