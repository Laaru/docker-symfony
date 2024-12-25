<?php

namespace App\Service\Notification\Sms;

use Psr\Log\LoggerInterface;

class StubSmsNotificationProvider implements SmsNotificationProviderInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sendNotification(string $phone, array $data): void
    {
        $this->logger->info("Stub sms notification sent to {$phone}", $data);
    }
}
