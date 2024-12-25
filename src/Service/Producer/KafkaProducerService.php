<?php

namespace App\Service\Producer;

use RdKafka\Producer;

class KafkaProducerService
{
    private Producer $producer;
    private string $topicName;

    public function __construct(string $kafkaBroker, string $topicName)
    {
        $this->producer = new Producer();
        $this->producer->addBrokers($kafkaBroker);

        $this->topicName = $topicName;
    }

    public function sendMessage(array $message): void
    {
        $jsonMessage = json_encode($message);

        $topic = $this->producer->newTopic($this->topicName);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $jsonMessage);
        $this->producer->poll(0);
    }
}
