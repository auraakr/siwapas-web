<?php

namespace Database\Factories;

use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'slug' => fake()->unique()->slug(2),
            'title' => ['id' => fake()->sentence(3), 'en' => fake()->sentence(3)],
            'body' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'status' => PublishStatus::Published,
        ];
    }
}
