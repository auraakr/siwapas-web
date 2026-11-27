<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Enums\SettlementMethod;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin-only. Payments are never approved automatically.
 */
class VerifyPayment
{
    public function __construct(private TransitionOrderStatus $transition) {}

    /**
     * A verified full payment confirms the order, a verified down payment secures the booking, and a
     * verified settlement marks the reservation as paid in full.
     */
    public function handle(Payment $payment, User $admin): Payment
    {
        return DB::transaction(function () use ($payment, $admin): Payment {
            $locked = Payment::query()->with('order')->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== PaymentStatus::Pending) {
                throw InvalidOrderTransition::between($locked->order->status, $locked->order->status);
            }

            [$to, $note] = match ($locked->type) {
                PaymentType::Full => [OrderStatus::Confirmed, 'Payment verified.'],
                PaymentType::DownPayment => [OrderStatus::DpPaid, 'Down payment verified.'],
                PaymentType::Settlement => [OrderStatus::PaidInFull, 'Settlement verified.'],
            };

            $order = $this->transition->handle($locked->order, $to, $admin, $note);

            if ($locked->type === PaymentType::Settlement) {
                $order->update(['settlement_method' => SettlementMethod::Transfer, 'settled_at' => now()]);
            }

            $locked->update([
                'status' => PaymentStatus::Verified,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $payment->setRawAttributes($locked->getAttributes(), true);

            return $payment;
        });
    }
}
