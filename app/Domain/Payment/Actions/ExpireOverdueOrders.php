<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Order;

class ExpireOverdueOrders
{
    public function __construct(private TransitionOrderStatus $transition) {}

    /**
     * Expire unpaid orders past their deadline and release their slots.
     * Safe to run repeatedly.
     */
    public function handle(): int
    {
        $expired = 0;

        Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->where('expires_at', '<=', now())
            ->each(function (Order $order) use (&$expired): void {
                try {
                    $this->transition->handle($order, OrderStatus::Expired, note: 'Payment deadline passed.');
                    $expired++;
                } catch (InvalidOrderTransition) {
                    // Already moved on (e.g. a proof was uploaded in the meantime).
                }
            });

        return $expired;
    }
}
