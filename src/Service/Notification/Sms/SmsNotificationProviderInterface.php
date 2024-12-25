<?php

namespace App\Service\Notification\Sms;

interface SmsNotificationProviderInterface
{
    public function sendNotification(string $phone, array $data): void;
}
