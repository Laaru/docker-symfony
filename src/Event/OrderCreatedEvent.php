<?php

namespace App\Event;

use App\Entity\OrderItem;
use Doctrine\Common\Collections\Collection;

readonly class OrderCreatedEvent
{
    /**
     * @param Collection<int, OrderItem> $items
     */
    public function __construct(
        private string $email,
        private string $phone,
        private string|int $num,
        private string $deliverySlug,
        private ?int $addressKladrId,
        private ?string $fullAddress,
        private Collection $items,
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getNum(): string
    {
        return (string) $this->num;
    }

    public function getDeliverySlug(): string
    {
        return $this->deliverySlug;
    }

    public function getAddressKladrId(): ?int
    {
        return $this->addressKladrId;
    }

    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    public function getItems(): array
    {
        $itemsForEvent = [];
        foreach ($this->items as $orderItem) {
            $itemsForEvent[] = [
                'name' => $orderItem->getProduct()->getName(),
                'cost' => $orderItem->getPrice(),
                'additionalInfo' => $orderItem->getProduct()->getDescription(),
            ];
        }

        return $itemsForEvent;
    }
}
