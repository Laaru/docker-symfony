<?php

namespace App\Entity\DTO;

use Doctrine\DBAL\Types\Types;
use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserRegisterDTO
{
    public function __construct(

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        #[Property(example: 'firstName')]
        public mixed $firstName,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\Length(max: 255)]
        #[Property(example: 'lastName')]
        public mixed $lastName,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\Email(message: 'Invalid email format')]
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        #[Property(example: 'email@email.com')]
        public mixed $email,

        /** @var string */
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\+?[1-9]\d{1,14}$/', message: 'Invalid phone number')]
        #[Property(example: '88005553535')]
        public mixed $phone,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Password must contain at least one uppercase letter')]
        #[Assert\Regex(pattern: '/\d/', message: 'Password must contain at least one number')]
        #[Assert\Regex(pattern: '/[\W_]/', message: 'Password must contain at least one special character')]
        #[Property(example: 'Password_1')]
        public mixed $password,

        /** @var string */
        #[Assert\Type(type: Types::STRING)]
        #[Assert\NotBlank]
        #[Assert\EqualTo(propertyPath: 'password', message: 'Passwords must match')]
        #[Property(example: 'Password_1')]
        public mixed $passwordConfirmation,
    ) {}
}
