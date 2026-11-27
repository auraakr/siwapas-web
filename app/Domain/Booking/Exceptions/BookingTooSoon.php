<?php

namespace App\Domain\Booking\Exceptions;

use RuntimeException;

/**
 * The down payment is due before the check-in day starts; a booking that leaves too little time
 * to pay and have the proof checked is refused, and the visitor is pointed to the managers instead.
 */
class BookingTooSoon extends RuntimeException
{
    public static function forCheckIn(string $checkIn): self
    {
        return new self("Too little time is left to pay the down payment before check-in on {$checkIn}.");
    }
}
