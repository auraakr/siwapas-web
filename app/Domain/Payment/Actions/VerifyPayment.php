<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin-only. Payments are never approved automatically.
 */
class VerifyPayment
{
    public function __construct(private TransitionOrderStatus $transition) {}

    public function handle(Payment $payment, User $admin): Payment
    {
        return DB::transaction(function () use ($payment, $admin): Payment {
            $this->transition->handle($payment->order, OrderStatus::Confirmed, $admin, 'Payment verified.');

            $payment->update([
                'status' => PaymentStatus::Verified,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            return $payment;
        });
    }
}
