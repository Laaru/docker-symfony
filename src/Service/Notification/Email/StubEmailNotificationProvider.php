<?php

namespace App\Service\Notification\Email;

use Psr\Log\LoggerInterface;

class StubEmailNotificationProvider implements EmailNotificationProviderInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sendNotification(string $email, array $data): void
    {
        $this->logger->info("Stub email notification sent to {$email}", $data);
    }
}
