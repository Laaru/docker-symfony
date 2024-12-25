<?php

namespace App\Entity\DTO;

use Doctrine\DBAL\Types\Types;
use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class OrderStatusUpdateDTO
{
    public function __construct(

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Property(example: 1)]
        public mixed $orderId,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Property(example: 2)]
        public mixed $statusId,
    ) {}
}
