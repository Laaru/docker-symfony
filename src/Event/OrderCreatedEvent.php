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
        private ?string $addressKladrId,
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

    public function getAddressKladrId(): ?string
    {
        return $this->addressKladrId;
    }

    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    /**
     * @return array<mixed>
     */
    public function getItems(): array
    {
        $itemsForEvent = [];
        foreach ($this->items as $orderItem) {
            $product = $orderItem->getProduct();
            if (!$product) {
                continue;
            }
            $itemsForEvent[] = [
                'name' => $product->getName(),
                'cost' => $orderItem->getPrice(),
                'additionalInfo' => $product->getDescription(),
            ];
        }

        return $itemsForEvent;
    }
}
