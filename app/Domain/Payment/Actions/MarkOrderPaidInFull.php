<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Enums\SettlementMethod;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin-only: record that the rest of a reservation was paid, in cash on site or by a transfer the
 * admin has checked. Allowed at any time after the down payment, also after the check-out day.
 */
class MarkOrderPaidInFull
{
    public function __construct(private TransitionOrderStatus $transition) {}

    public function handle(Order $order, User $admin, SettlementMethod $method, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $admin, $method, $note): Order {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->requiresDownPayment() || ! $locked->status->canTransitionTo(OrderStatus::PaidInFull)) {
                throw InvalidOrderTransition::between($locked->status, OrderStatus::PaidInFull);
            }

            $verified = [
                'method' => $method === SettlementMethod::Cash ? Payment::METHOD_CASH : Payment::METHOD_TRANSFER,
                'status' => PaymentStatus::Verified,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ];

            $pending = $locked->payments()
                ->where('type', PaymentType::Settlement)
                ->where('status', PaymentStatus::Pending)
                ->latest('id')
                ->first();

            if ($pending !== null) {
                $pending->update($verified);
            } else {
                $locked->payments()->create([
                    'type' => PaymentType::Settlement,
                    'expected_amount' => (int) $locked->remaining_amount,
                    ...$verified,
                ]);
            }

            $label = $method === SettlementMethod::Cash
                ? 'Settled in cash on site.'
                : 'Settlement transfer confirmed by admin.';

            $this->transition->handle($locked, OrderStatus::PaidInFull, $admin, trim($label.' '.($note ?? '')));

            $locked->update(['settlement_method' => $method, 'settled_at' => now()]);

            $order->setRawAttributes($locked->refresh()->getAttributes(), true);

            return $order;
        });
    }
}
