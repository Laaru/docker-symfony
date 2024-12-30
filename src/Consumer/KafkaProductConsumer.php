<?php

namespace App\Consumer;

use App\Entity\DTO\Collection\ProductDTOCollection;
use App\Factory\ProductUpdateDTOFactory;
use App\Service\Product\ProductImportService;
use Psr\Log\LoggerInterface;
use RdKafka\KafkaConsumer;
use RdKafka\Message;

class KafkaProductConsumer
{
    private KafkaConsumer $consumer;

    public function __construct(
        private readonly string $kafkaBroker,
        private readonly string $kafkaTopic,
        private readonly string $kafkaConsumerGroup,
        private readonly LoggerInterface $logger,
        private readonly ProductImportService $productImportService,
        private readonly ProductUpdateDTOFactory $productUpdateDTOFactory,
    ) {
        $config = new \RdKafka\Conf();
        $config->set('group.id', $this->kafkaConsumerGroup);
        $config->set('metadata.broker.list', $this->kafkaBroker);
        $config->set('auto.offset.reset', 'earliest');
        $config->set('enable.auto.commit', 'true');

        $this->consumer = new KafkaConsumer($config);
    }

    public function consume(): void
    {
        $this->consumer->subscribe([$this->kafkaTopic]);

        // @phpstan-ignore-next-line
        while (true) {
            $message = $this->consumer->consume(1000);

            if (
                null === $message
                || RD_KAFKA_RESP_ERR__PARTITION_EOF === $message->err
                || RD_KAFKA_RESP_ERR__TIMED_OUT === $message->err
            ) {
                continue;
            }

            if ($message->err) {
                $this->logger->error('Kafka consume error: ' . $message->errstr());
                continue;
            }

            $this->processMessage($message);
        }
    }

    private function processMessage(Message $message): void
    {
        $data = json_decode($message->payload, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->logger->error('Invalid JSON received', [
                'error' => json_last_error_msg(),
                'message' => $message->payload,
            ]);

            return;
        }

        $this->logger->info('Received message', $data);

        $products = new ProductDTOCollection(
            ...array_map(
                fn ($productData) => $this->productUpdateDTOFactory->createFromArray($productData),
                $data
            )
        );

        $this->productImportService->importMultipleProducts($products);
    }
}
