<?php

namespace App\Entity\DTO;

use Doctrine\DBAL\Types\Types;
use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProductUpdateDTO
{
    public function __construct(

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 1000)]
        public mixed $externalId,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        #[Property(example: 'keyboard')]
        public mixed $name,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 9999)]
        public mixed $basePrice,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Property(example: 9999)]
        public mixed $salePrice,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Property(example: 'description')]
        public mixed $description,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 5)]
        public mixed $colorExternalId,

        /** @var array */
        #[Assert\Type(type: 'array')]
        #[Property(example: [1, 5, 17])]
        public mixed $storesExternalIds,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 2000)]
        public mixed $weight,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 100)]
        public mixed $height,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 200)]
        public mixed $width,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 300)]
        public mixed $length,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Choice(options: [0, 12, 20])]
        #[Property(example: 20)]
        public mixed $tax,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 2)]
        public mixed $version
    ) {}
}
