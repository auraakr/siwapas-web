<?php

namespace App\Domain\Booking\Services;

use App\Domain\Booking\Exceptions\BookingTooSoon;
use Carbon\CarbonImmutable;

class PaymentDeadline
{
    /**
     * The down payment is due at the end of the hold period, or at the start of the check-in day
     * (Asia/Jakarta) when that comes sooner. Returns the start of the check-in day in UTC, which the
     * order uses to cap its hold period.
     *
     * @throws BookingTooSoon when less than the minimum time to pay would be left
     */
    public function forCheckIn(string $checkIn): CarbonImmutable
    {
        $startOfCheckIn = CarbonImmutable::parse($checkIn, config('siwapas.display_timezone'))->startOfDay()->utc();
        $deadline = $startOfCheckIn->min(CarbonImmutable::now()->addHours((int) config('siwapas.payment.hold_hours')));

        if (CarbonImmutable::now()->diffInMinutes($deadline, false) < (int) config('siwapas.payment.min_payment_window_minutes')) {
            throw BookingTooSoon::forCheckIn($checkIn);
        }

        return $startOfCheckIn;
    }
}
