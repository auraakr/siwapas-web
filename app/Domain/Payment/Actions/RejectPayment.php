<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectPayment
{
    public function __construct(private TransitionOrderStatus $transition) {}

    public function handle(Payment $payment, User $admin, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $admin, $reason): Payment {
            $this->transition->handle($payment->order, OrderStatus::Rejected, $admin, $reason);

            $payment->update([
                'status' => PaymentStatus::Rejected,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $payment;
        });
    }
}
