<?php

namespace Database\Factories;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\BankAccount;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => 'manual_transfer',
            'bank_account_id' => BankAccount::factory(),
            'bank_snapshot' => [['type' => 'bank', 'name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'Desa Wisata', 'qris_url' => null]],
            'expected_amount' => 500123,
            'status' => PaymentStatus::Pending,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Verified,
            'verified_at' => now(),
        ]);
    }
}
