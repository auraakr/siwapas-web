<?php

namespace App\Domain\Payment\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case AwaitingVerification = 'awaiting_verification';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
}
