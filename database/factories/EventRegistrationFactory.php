<?php

namespace Database\Factories;

use App\Domain\Event\Models\Event;
use App\Domain\Event\Models\EventRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => null,
            'event_id' => Event::factory(),
            'participant_count' => 1,
            'is_active' => true,
            'registration_code' => 'EVT-'.strtoupper(Str::random(8)),
        ];
    }
}
