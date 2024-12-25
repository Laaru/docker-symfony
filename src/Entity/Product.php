<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug', 'externalId'])]
class Product
{
    #[Groups(['list', 'detail', 'basket', 'order'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $id = null;

    #[Groups(['detail', 'basket', 'order'])]
    #[ORM\Column(name: 'external_id', type: Types::INTEGER, unique: true, nullable: false)]
    private ?int $externalId = null;

    #[Groups(['detail'])]
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['detail'])]
    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['list', 'detail', 'basket', 'order'])]
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private ?string $name = null;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    private ?string $slug = null;

    #[Groups(['list', 'detail', 'basket'])]
    #[ORM\Column(name: 'base_price', type: Types::INTEGER, nullable: false)]
    private ?int $basePrice = null;

    #[Groups(['list', 'detail', 'basket'])]
    #[ORM\Column(name: 'sale_price', type: Types::INTEGER, nullable: true)]
    private ?int $salePrice = null;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['list', 'detail'])]
    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Color $color = null;

    #[Groups(['list', 'detail'])]
    #[ORM\ManyToMany(targetEntity: Store::class, inversedBy: 'products')]
    private Collection $stores;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $weight = null;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $height = null;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $width = null;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $length = null;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $tax = null;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $version = null;

    public function __construct()
    {
        $this->stores = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getBasePrice(): ?int
    {
        return $this->basePrice;
    }

    public function setBasePrice(int $basePrice): static
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    public function setSalePrice(?int $salePrice): static
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, Store>
     */
    public function getStores(): Collection
    {
        return $this->stores;
    }

    public function addInStockInStore(Store $inStockInStore): static
    {
        if (!$this->stores->contains($inStockInStore)) {
            $this->stores->add($inStockInStore);
        }

        return $this;
    }

    public function removeInStockInStore(Store $store): static
    {
        $this->stores->removeElement($store);

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function setTax(int $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(int $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }
}
