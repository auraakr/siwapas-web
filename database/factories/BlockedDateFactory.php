<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\BlockedDate;
use App\Domain\Catalog\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedDate>
 */
class BlockedDateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'starts_on' => now()->addDays(10)->toDateString(),
            'ends_on' => now()->addDays(12)->toDateString(),
            'reason' => fake()->sentence(3),
        ];
    }
}
