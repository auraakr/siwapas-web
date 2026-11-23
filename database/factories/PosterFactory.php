<?php

namespace Database\Factories;

use App\Domain\Content\Models\Poster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poster>
 */
class PosterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['id' => fake()->sentence(3), 'en' => fake()->sentence(3)],
            'alt' => ['id' => fake()->sentence(5), 'en' => fake()->sentence(5)],
        ];
    }
}
