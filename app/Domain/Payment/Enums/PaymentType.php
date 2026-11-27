<?php

namespace App\Domain\Payment\Enums;

/**
 * What a payment covers. Reservations are paid as a down payment followed by a settlement;
 * event tickets (and orders from before down payments existed) are paid in full at once.
 */
enum PaymentType: string
{
    case Full = 'full';
    case DownPayment = 'dp';
    case Settlement = 'settlement';
}
