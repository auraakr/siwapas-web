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

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PendingPayment => [self::AwaitingVerification, self::Expired, self::Cancelled],
            self::AwaitingVerification => [self::Paid, self::Confirmed, self::Rejected, self::Cancelled],
            self::Paid => [self::Confirmed, self::Refunded],
            self::Confirmed => [self::Cancelled, self::Refunded],
            self::Rejected, self::Expired, self::Cancelled, self::Refunded => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    /**
     * Whether an order in this status still holds its slot or quota.
     */
    public function holdsInventory(): bool
    {
        return in_array($this, [self::PendingPayment, self::AwaitingVerification, self::Paid, self::Confirmed], true);
    }
}
