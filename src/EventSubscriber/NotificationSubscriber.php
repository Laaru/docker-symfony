<?php

namespace App\EventSubscriber;

use App\Event\OrderCreatedEvent;
use App\Event\UserRegisteredEvent;
use App\Service\Notification\Email\EmailNotificationProviderInterface;
use App\Service\Notification\Sms\SmsNotificationProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Uid\Uuid;

readonly class NotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailNotificationProviderInterface $emailNotificationProvider,
        private SmsNotificationProviderInterface $smsNotificationProvider
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => 'onUserRegistered',
            OrderCreatedEvent::class => 'onOrderCreated',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $email = $event->getEmail();
        $phone = $event->getPhone();

        $this->emailNotificationProvider->sendNotification($email, [
            'type' => 'email',
            'userEmail' => $email,
            'promoId' => Uuid::v4(),
        ]);
        $this->smsNotificationProvider->sendNotification($phone, [
            'type' => 'sms',
            'userPhone' => $phone,
            'promoId' => Uuid::v4(),
        ]);
    }

    public function onOrderCreated(OrderCreatedEvent $event): void
    {
        $commonData = [
            'notificationType' => 'requires_payment',
            'orderNum' => $event->getNum(),
            'deliveryType' => $event->getDeliverySlug(),
            'orderItems' => $event->getItems(),
            'deliveryAddress' => [
                'kladrId' => $event->getAddressKladrId(),
                'fullAddress' => $event->getFullAddress(),
            ],
        ];

        $email = $event->getEmail();
        $emailData = [
            'type' => 'email',
            'userEmail' => $email,
        ];
        $this->emailNotificationProvider->sendNotification($email, array_merge($commonData, $emailData));

        $phone = $event->getPhone();
        $phoneData = [
            'type' => 'sms',
            'userPhone' => $phone,
        ];
        $this->smsNotificationProvider->sendNotification($phone, array_merge($commonData, $phoneData));
    }
}
