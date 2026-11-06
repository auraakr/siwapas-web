<?php

namespace Database\Factories;

use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(3),
            'type' => DestinationType::Destination,
            'name' => ['id' => fake()->words(3, true), 'en' => fake()->words(3, true)],
            'summary' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'description' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-7.0, -6.8),
            'longitude' => fake()->longitude(107.6, 107.8),
            'opening_hours' => ['daily' => '08:00-17:00'],
            'facilities' => ['Parkir', 'Toilet'],
            'rules' => ['Jaga kebersihan'],
            'requires_reservation' => false,
            'is_featured' => false,
            'sort_order' => 0,
            'status' => PublishStatus::Published,
        ];
    }
}
