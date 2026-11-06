<?php

namespace Database\Factories;

use App\Domain\Content\Enums\GalleryItemType;
use App\Domain\Content\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => GalleryItemType::Image,
            'title' => ['id' => fake()->sentence(3), 'en' => fake()->sentence(3)],
            'category' => fake()->word(),
            'sort_order' => 0,
            'is_published' => true,
        ];
    }
}
