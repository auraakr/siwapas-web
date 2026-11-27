<?php

namespace Database\Factories;

use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'SWP-'.now()->format('ym').'-'.fake()->unique()->numerify('####'),
            'access_token' => Str::random(48),
            'kind' => OrderKind::Reservation,
            'customer_name' => fake()->name(),
            'customer_phone' => '08'.fake()->numerify('##########'),
            'customer_email' => fake()->safeEmail(),
            'status' => OrderStatus::PendingPayment,
            'subtotal' => 500000,
            'unique_code' => 123,
            'total' => 500123,
            'expires_at' => now()->addHours(24),
        ];
    }

    public function awaitingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::AwaitingVerification,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * A reservation paid with a 50% down payment: 250.000 now (+ unique code), 250.000 later.
     */
    public function withDownPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => OrderKind::Reservation,
            'dp_percentage' => 50,
            'dp_amount' => intdiv($attributes['subtotal'] + 1, 2),
            'remaining_amount' => $attributes['subtotal'] - intdiv($attributes['subtotal'] + 1, 2),
        ]);
    }

    /**
     * The down payment is verified: the booking stands and the rest is still due.
     */
    public function dpPaid(): static
    {
        return $this->withDownPayment()->state(fn (array $attributes) => [
            'status' => OrderStatus::DpPaid,
            'confirmed_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Expired,
            'expires_at' => now()->subHour(),
        ]);
    }
}
