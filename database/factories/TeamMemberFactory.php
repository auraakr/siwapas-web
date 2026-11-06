<?php

namespace Database\Factories;

use App\Domain\Content\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => ['id' => fake()->jobTitle(), 'en' => fake()->jobTitle()],
            'bio' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'sort_order' => 0,
        ];
    }
}
