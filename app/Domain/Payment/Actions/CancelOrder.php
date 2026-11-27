<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\CancellationParty;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelOrder
{
    public function __construct(private TransitionOrderStatus $transition) {}

    /**
     * Admin cancellation. The slot or quota held by the order is released.
     *
     * When the tenant cancels, a paid down payment is forfeited (no refund). When the managers cancel,
     * the admin writes down how it was settled with the tenant.
     */
    public function handle(
        Order $order,
        User $admin,
        ?string $note,
        CancellationParty $party = CancellationParty::Manager,
    ): Order {
        return DB::transaction(function () use ($order, $admin, $note, $party): Order {
            // The history keeps the admin's own words; who cancelled is stored on the order.
            $history = filled($note) ? $note : match ($party) {
                CancellationParty::Tenant => 'Cancelled at the request of the tenant; the down payment is forfeited.',
                CancellationParty::Manager => 'Cancelled by the managers.',
            };

            $this->transition->handle($order, OrderStatus::Cancelled, $admin, $history);

            $order->update(['cancelled_by' => $party, 'resolution_note' => $note]);

            return $order;
        });
    }
}
