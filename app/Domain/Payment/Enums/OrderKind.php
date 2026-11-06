<?php

namespace App\Domain\Payment\Enums;

enum OrderKind: string
{
    case Reservation = 'reservation';
    case EventRegistration = 'event_registration';
}
