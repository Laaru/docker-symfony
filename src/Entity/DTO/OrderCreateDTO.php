<?php

namespace App\Entity\DTO;

use Doctrine\DBAL\Types\Types;
use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class OrderCreateDTO
{
    public function __construct(

        /** @var string */
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\+?[1-9]\d{1,14}$/', message: 'Invalid phone number')]
        #[Property(example: '88005553535')]
        public mixed $phone,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Choice(options: [1, 2])]
        #[Property(example: 1)]
        public mixed $deliveryId,

        /** @var int */
        #[Assert\Type(type: Types::INTEGER)]
        #[Assert\NotBlank]
        #[Assert\Choice(options: [1, 2])]
        #[Property(example: 2)]
        public mixed $paymentId,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Property(example: '847 Spencer Alley Apt. 040')]
        public mixed $deliveryAddress,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Property(example: '1876887532')]
        public mixed $deliveryAddressKladrId,
    ) {}
}
