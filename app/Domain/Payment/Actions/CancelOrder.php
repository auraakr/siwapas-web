<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Models\User;

class CancelOrder
{
    public function __construct(private TransitionOrderStatus $transition) {}

    /**
     * Admin cancellation. The slot or quota held by the order is released.
     */
    public function handle(Order $order, User $admin, string $reason): Order
    {
        return $this->transition->handle($order, OrderStatus::Cancelled, $admin, $reason);
    }
}
