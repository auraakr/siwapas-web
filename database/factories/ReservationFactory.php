<?php

namespace Database\Factories;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'facility_id' => Facility::factory(),
            'check_in' => now()->addDays(7)->toDateString(),
            'check_out' => now()->addDays(8)->toDateString(),
            'guests' => 10,
            'price_breakdown' => [],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
