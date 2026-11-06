<?php

namespace App\Domain\Event\DTOs;

use App\Domain\Payment\DTOs\CustomerData;

final readonly class EventRegistrationData
{
    /**
     * @param  list<array{name: string, phone?: string|null}>  $participants
     */
    public function __construct(
        public int $eventId,
        public CustomerData $customer,
        public array $participants,
    ) {}
}
