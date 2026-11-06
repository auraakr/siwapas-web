<?php

namespace Database\Factories;

use App\Domain\Content\Enums\ArticleCategory;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(4),
            'title' => ['id' => fake()->sentence(5), 'en' => fake()->sentence(5)],
            'excerpt' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'body' => ['id' => fake()->paragraphs(3, true), 'en' => fake()->paragraphs(3, true)],
            'category' => ArticleCategory::Culture,
            'published_at' => now(),
            'author_name' => fake()->name(),
            'status' => PublishStatus::Published,
        ];
    }
}
