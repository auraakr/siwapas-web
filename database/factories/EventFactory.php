<?php

namespace Database\Factories;

use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(4),
            'title' => ['id' => fake()->sentence(4), 'en' => fake()->sentence(4)],
            'description' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'organizer' => fake()->company(),
            'location_name' => fake()->words(2, true),
            'address' => fake()->address(),
            'starts_at' => now()->addDays(30),
            'ends_at' => now()->addDays(30)->addHours(4),
            'fee' => 0,
            'quota' => 100,
            'status' => EventStatus::Published,
        ];
    }
}
