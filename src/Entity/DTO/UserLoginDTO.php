<?php

namespace App\Entity\DTO;

use Doctrine\DBAL\Types\Types;
use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserLoginDTO
{
    public function __construct(

        /** @var string */
        #[Assert\NotBlank]
        #[Property(example: '88005553535')]
        public mixed $phone,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\NotBlank]
        #[Property(example: 'Password_1')]
        public mixed $password,
    ) {}
}
