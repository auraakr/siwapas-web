<?php

namespace Database\Factories;

use App\Domain\Catalog\Enums\PricingRuleType;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Catalog\Models\PricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingRule>
 */
class PricingRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'name' => ['id' => fake()->words(2, true), 'en' => fake()->words(2, true)],
            'type' => PricingRuleType::Weekend,
            'days_of_week' => [6, 7],
            'price' => fake()->numberBetween(5, 60) * 100000,
            'priority' => 0,
        ];
    }
}
