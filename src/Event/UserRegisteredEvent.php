<?php

namespace App\Event;

readonly class UserRegisteredEvent
{
    public function __construct(
        private string $email,
        private string $phone
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
