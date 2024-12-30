<?php

namespace App\Producer;

use Psr\Log\LoggerInterface;
use RdKafka\Exception;
use RdKafka\Producer;

class KafkaProducerService
{
    private Producer $producer;

    public function __construct(
        private readonly string $kafkaBroker,
        private readonly LoggerInterface $logger
    ) {
        $this->producer = new Producer();
        $this->producer->addBrokers($this->kafkaBroker);
    }

    /**
     * @param array<mixed> $message
     *
     * @throws Exception
     */
    public function sendMessage(array $message, string $topicName): void
    {
        try {
            $jsonMessage = json_encode($message);
            if (false === $jsonMessage) {
                throw new \RuntimeException('Failed to encode message to JSON: ' . json_last_error_msg());
            }

            $topic = $this->producer->newTopic($topicName);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $jsonMessage);
            $this->producer->poll(0);
        } catch (\Throwable $e) {
            $this->logger->error('Kafka produce error: ' . $e->getMessage());
        }
    }
}
