<?php

namespace App\Domain\Event\Exceptions;

use RuntimeException;

class EventRegistrationClosed extends RuntimeException
{
    public static function notOpen(): self
    {
        return new self('Registration for this event is not open.');
    }

    public static function full(): self
    {
        return new self('There is not enough quota left for this event.');
    }
}
