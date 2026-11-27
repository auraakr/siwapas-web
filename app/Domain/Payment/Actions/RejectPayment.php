<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectPayment
{
    public function __construct(private TransitionOrderStatus $transition) {}

    /**
     * Reject a transfer proof. The visitor sees the reason on the order page.
     *
     * - Down payment or full payment: the order goes back to pending_payment so the visitor can upload
     *   again before the deadline. Past the deadline it expires and the slot is released. With
     *   $permanent (for example a forged proof) the order is rejected for good and the slot released.
     * - Settlement: the booking stands; the order goes back to dp_paid for another settlement proof.
     */
    public function handle(Payment $payment, User $admin, string $reason, bool $permanent = false): Payment
    {
        return DB::transaction(function () use ($payment, $admin, $reason, $permanent): Payment {
            $locked = Payment::query()->with('order')->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();
            $order = $locked->order;

            if ($locked->status !== PaymentStatus::Pending) {
                throw InvalidOrderTransition::between($order->status, $order->status);
            }

            $to = match (true) {
                $locked->type === PaymentType::Settlement => OrderStatus::DpPaid,
                $permanent => OrderStatus::Rejected,
                $order->paymentDeadlinePassed() => OrderStatus::Expired,
                default => OrderStatus::PendingPayment,
            };

            $this->transition->handle($order, $to, $admin, $reason);

            $locked->update([
                'status' => PaymentStatus::Rejected,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ]);

            if ($to === OrderStatus::PendingPayment) {
                // A fresh payment of the same amount takes the next upload; the rejected one keeps its history.
                $order->payments()->create([
                    'type' => $locked->type,
                    'method' => $locked->method,
                    'bank_account_id' => $locked->bank_account_id,
                    'bank_snapshot' => $locked->bank_snapshot,
                    'expected_amount' => $locked->expected_amount,
                    'status' => PaymentStatus::Pending,
                ]);
            }

            $payment->setRawAttributes($locked->getAttributes(), true);

            return $payment;
        });
    }
}
