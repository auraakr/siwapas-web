<?php

namespace App\Domain\Payment\DTOs;

final readonly class CustomerData
{
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $email = null,
        public ?string $notes = null,
    ) {}
}
