<?php

namespace Database\Factories;

use App\Domain\Payment\Enums\PaymentMethodType;
use App\Domain\Payment\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => PaymentMethodType::Bank,
            'bank_name' => fake()->randomElement(['BCA', 'BRI', 'Mandiri', 'BNI']),
            'account_number' => fake()->numerify('##########'),
            'account_holder' => fake()->name(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function qris(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PaymentMethodType::Qris,
            'bank_name' => 'QRIS',
            'account_number' => null,
            'account_holder' => null,
        ]);
    }
}
