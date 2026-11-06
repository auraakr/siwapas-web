<?php

namespace Database\Factories;

use App\Domain\Event\Models\EventParticipant;
use App\Domain\Event\Models\EventRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventParticipant>
 */
class EventParticipantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_registration_id' => EventRegistration::factory(),
            'name' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
        ];
    }
}
