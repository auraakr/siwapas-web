<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The only place that is allowed to change orders.status.
 */
class TransitionOrderStatus
{
    public function handle(
        Order $order,
        OrderStatus $to,
        ?User $actor = null,
        ?string $note = null,
        string $actorType = 'system',
    ): Order {
        return DB::transaction(function () use ($order, $to, $actor, $note, $actorType): Order {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $from = $locked->status;

            if (! $from->canTransitionTo($to)) {
                throw InvalidOrderTransition::between($from, $to);
            }

            $locked->status = $to;

            if ($to === OrderStatus::Confirmed) {
                $locked->confirmed_at = now();
            }

            $locked->save();

            $locked->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'actor_type' => $actor ? 'admin' : $actorType,
                'actor_id' => $actor?->getKey(),
                'note' => $note,
            ]);

            if (! $to->holdsInventory()) {
                $locked->reservation()->update(['is_active' => false]);
                $locked->eventRegistration()->update(['is_active' => false]);
            }

            $order->setRawAttributes($locked->getAttributes(), true);

            return $order;
        });
    }
}
