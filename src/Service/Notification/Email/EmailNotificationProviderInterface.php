<?php

namespace App\Service\Notification\Email;

interface EmailNotificationProviderInterface
{
    public function sendNotification(string $email, array $data): void;
}
