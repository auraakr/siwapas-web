<?php

namespace Database\Factories;

use App\Domain\Content\Models\LocalProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocalProduct>
 */
class LocalProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['id' => fake()->words(2, true), 'en' => fake()->words(2, true)],
            'maker_name' => fake()->name(),
            'description' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'whatsapp_number' => '08'.fake()->numerify('##########'),
            'sort_order' => 0,
            'is_published' => true,
        ];
    }
}
