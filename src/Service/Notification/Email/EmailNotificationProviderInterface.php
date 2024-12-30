<?php

namespace App\Service\Notification\Email;

interface EmailNotificationProviderInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function sendNotification(string $email, array $data): void;
}
