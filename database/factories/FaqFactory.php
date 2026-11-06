<?php

namespace Database\Factories;

use App\Domain\Content\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'answer' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'sort_order' => 0,
            'is_published' => true,
        ];
    }
}
