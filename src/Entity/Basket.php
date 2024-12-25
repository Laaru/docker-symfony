<?php

namespace App\Entity;

use App\Repository\BasketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Basket
{
    #[Groups(['basket'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['basket'])]
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['basket'])]
    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'basket', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userRelation = null;

    #[Groups(['basket'])]
    #[ORM\OneToMany(
        targetEntity: BasketItem::class,
        mappedBy: 'basket',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getUserRelation(): ?User
    {
        return $this->userRelation;
    }

    public function setUserRelation(User $userRelation): static
    {
        $this->userRelation = $userRelation;

        return $this;
    }

    /**
     * @return Collection<int, BasketItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(BasketItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setBasket($this);
        }

        return $this;
    }

    public function createAndAddItem(Product $product, int $quantity = 1): static
    {
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                $item->setQuantity($item->getQuantity() + $quantity);

                return $this;
            }
        }

        $item = new BasketItem();
        $item->setBasket($this)
            ->setProduct($product)
            ->setQuantity($quantity);

        $this->items->add($item);

        return $this;
    }

    public function updateItem(int $productId, int $quantity = 1): static
    {
        foreach ($this->items as $item) {
            if ($item->getProduct()->getId() === $productId) {
                $item->setQuantity($quantity);
                break;
            }
        }

        return $this;
    }

    public function removeItem(BasketItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getBasket() === $this) {
                $item->setBasket(null);
            }
        }

        return $this;
    }

    public function removeItemByProductId(int $productId): static
    {
        foreach ($this->items as $item) {
            if ($item->getProduct()->getId() === $productId) {
                $this->removeItem($item);
                break;
            }
        }

        return $this;
    }

    public function getTotalItemsCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $count += $item->getQuantity();
        }

        return $count;
    }

    public function isEmpty(): bool
    {
        return $this->items->count() <= 0;
    }

    public function clear(): static
    {
        $this->items->clear();

        return $this;
    }
}
