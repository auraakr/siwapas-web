<?php

namespace App\Domain\Payment\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case AwaitingVerification = 'awaiting_verification';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case DpPaid = 'dp_paid';
    case AwaitingSettlementVerification = 'awaiting_settlement_verification';
    case PaidInFull = 'paid_in_full';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /**
     * Full payment (events): pending_payment -> awaiting_verification -> confirmed.
     * Down payment (reservations): pending_payment -> awaiting_verification -> dp_paid
     * -> awaiting_settlement_verification -> paid_in_full, or dp_paid -> paid_in_full when settled on site.
     * A rejected proof sends the order back to pending_payment (or expired once its deadline passed).
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PendingPayment => [self::AwaitingVerification, self::Expired, self::Cancelled],
            self::AwaitingVerification => [self::PendingPayment, self::Paid, self::Confirmed, self::DpPaid, self::Rejected, self::Expired, self::Cancelled],
            self::Paid => [self::Confirmed, self::Refunded],
            self::Confirmed => [self::Cancelled, self::Refunded],
            self::DpPaid => [self::AwaitingSettlementVerification, self::PaidInFull, self::Cancelled],
            self::AwaitingSettlementVerification => [self::DpPaid, self::PaidInFull, self::Cancelled],
            self::PaidInFull => [self::Cancelled, self::Refunded],
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
        return in_array($this, [
            self::PendingPayment,
            self::AwaitingVerification,
            self::Paid,
            self::Confirmed,
            self::DpPaid,
            self::AwaitingSettlementVerification,
            self::PaidInFull,
        ], true);
    }

    /**
     * The booking is secured: the first payment (full or down payment) has been verified.
     */
    public function isBooked(): bool
    {
        return in_array($this, [
            self::Paid,
            self::Confirmed,
            self::DpPaid,
            self::AwaitingSettlementVerification,
            self::PaidInFull,
        ], true);
    }

    /**
     * Statuses in which a payment proof waits for an admin decision.
     *
     * @return list<self>
     */
    public static function awaitingDecision(): array
    {
        return [self::AwaitingVerification, self::AwaitingSettlementVerification];
    }
}
