<?php

namespace App\Domain\Booking\Exceptions;

use RuntimeException;

class FacilityUnavailable extends RuntimeException
{
    public static function forDates(): self
    {
        return new self('The facility is not available for the selected dates.');
    }

    public static function invalidRequest(string $reason): self
    {
        return new self($reason);
    }
}
