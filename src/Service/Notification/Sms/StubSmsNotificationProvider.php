<?php

namespace App\Service\Notification\Sms;

use App\Producer\KafkaProducerService;
use Psr\Log\LoggerInterface;

readonly class StubSmsNotificationProvider implements SmsNotificationProviderInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private KafkaProducerService $producerService,
        private string $kafkaTopic
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function sendNotification(string $phone, array $data): void
    {
        $this->producerService->sendMessage($data, $this->kafkaTopic);
        $this->logger->info("Stub sms notification sent to {$phone}", $data);
    }
}
