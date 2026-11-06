<?php

namespace App\Domain\Booking\DTOs;

use App\Domain\Payment\DTOs\CustomerData;
use Carbon\CarbonInterface;

final readonly class ReservationData
{
    public function __construct(
        public int $facilityId,
        public CarbonInterface $checkIn,
        public CarbonInterface $checkOut,
        public int $guests,
        public CustomerData $customer,
    ) {}
}
