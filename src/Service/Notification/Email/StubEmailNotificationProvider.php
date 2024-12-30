<?php

namespace App\Service\Notification\Email;

use App\Producer\KafkaProducerService;
use Psr\Log\LoggerInterface;

readonly class StubEmailNotificationProvider implements EmailNotificationProviderInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private KafkaProducerService $producerService,
        private string $kafkaTopic
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function sendNotification(string $email, array $data): void
    {
        $this->producerService->sendMessage($data, $this->kafkaTopic);
        $this->logger->info("Stub email notification sent to {$email}", $data);
    }
}
