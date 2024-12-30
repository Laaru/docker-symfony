<?php

namespace App\Service\Notification\Sms;

interface SmsNotificationProviderInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function sendNotification(string $phone, array $data): void;
}
