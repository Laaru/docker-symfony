<?php

namespace App\Service\Order;

use App\Message\GenerateOrderReportMessage;
use App\Repository\OrderRepository;
use App\Service\Producer\KafkaProducerService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class OrderReportService
{
    public function __construct(
        private MessageBusInterface $bus,
        private KafkaProducerService $kafkaProducerService,
        private ParameterBagInterface $parameterBag,
        private OrderRepository $orderRepository
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
            $reportDirectory = $this->parameterBag->get('kernel.project_dir').'/public/reports/orders/';
            $reportFilePath = $reportDirectory.$reportId.'.json';

            $filesystem = new Filesystem();
            if (!$filesystem->exists($reportDirectory)) {
                try {
                    $filesystem->mkdir($reportDirectory);
                } catch (IOExceptionInterface $e) {
                    throw new \RuntimeException('Dir creation error: '.$e->getMessage());
                }
            }

            $reportData = [];
            /* @var $order \App\Entity\Order */
            foreach ($this->orderRepository->findAll() as $order) {
                /* @var $orderItem \App\Entity\OrderItem */
                foreach ($order->getItems() as $orderItem) {
                    $product = $orderItem->getProduct();
                    $reportData[] = [
                        'product_name' => $product->getName(),
                        'price' => $orderItem->getPrice(),
                        'amount' => $orderItem->getQuantity(),
                        'user' => ['id' => $order->getUserRelation()->getId()],
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

        $this->kafkaProducerService->sendMessage($dispatchMessage);
    }
}
