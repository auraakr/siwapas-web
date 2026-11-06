<?php

namespace App\Domain\Booking\Services;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\BlockedDate;
use App\Domain\Catalog\Models\Facility;
use Carbon\CarbonInterface;

class AvailabilityChecker
{
    /**
     * A stay occupies the nights from check-in up to, but excluding, check-out.
     * Blocked dates are inclusive on both ends.
     */
    public function isAvailable(Facility $facility, CarbonInterface $checkIn, CarbonInterface $checkOut): bool
    {
        if (! $facility->is_active) {
            return false;
        }

        $lastNight = $checkOut->toImmutable()->subDay()->toDateString();

        $blocked = BlockedDate::query()
            ->where('facility_id', $facility->id)
            ->whereDate('starts_on', '<=', $lastNight)
            ->whereDate('ends_on', '>=', $checkIn->toDateString())
            ->exists();

        if ($blocked) {
            return false;
        }

        return ! Reservation::query()
            ->where('facility_id', $facility->id)
            ->where('is_active', true)
            ->whereDate('check_in', '<', $checkOut->toDateString())
            ->whereDate('check_out', '>', $checkIn->toDateString())
            ->exists();
    }
}
