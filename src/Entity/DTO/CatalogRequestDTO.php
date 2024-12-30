<?php

namespace App\Entity\DTO;

use Doctrine\DBAL\Types\Types;
use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CatalogRequestDTO
{
    public function __construct(

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 2)]
        public int $page,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\Positive]
        #[Property(example: 1000)]
        public ?int $priceMin,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\Positive]
        #[Property(example: 1000)]
        public ?int $priceMax,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\Positive]
        #[Property(example: 5)]
        public ?int $colorId,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\Positive]
        #[Property(example: 9)]
        public ?int $storeId,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\NotBlank]
        #[Assert\Choice(options: ['desc', 'asc'])]
        #[Property(example: 'desc')]
        public string $order,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\NotBlank]
        #[Assert\Choice(options: ['basePrice', 'updatedAt'])]
        #[Property(example: 'desc')]
        public string $sort,
    ) {}
}
