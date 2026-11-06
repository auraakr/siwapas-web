<?php

namespace Database\Factories;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\OrderStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'from_status' => OrderStatus::PendingPayment,
            'to_status' => OrderStatus::AwaitingVerification,
            'actor_type' => 'system',
        ];
    }
}
