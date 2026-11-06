<?php

namespace Database\Factories;

use App\Domain\Catalog\Enums\RentalUnit;
use App\Domain\Catalog\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => ['id' => fake()->words(2, true), 'en' => fake()->words(2, true)],
            'description' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'capacity' => fake()->numberBetween(10, 100),
            'unit' => RentalUnit::Day,
            'base_price' => fake()->numberBetween(5, 50) * 100000,
            'rules' => ['id' => ['Dilarang merokok'], 'en' => ['No smoking']],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
