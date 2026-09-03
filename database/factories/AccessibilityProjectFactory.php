<?php

namespace Database\Factories;

use App\Models\AccessibilityProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessibilityProject>
 */
class AccessibilityProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => \App\Models\Team::factory(),
            'name' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'target_wcag_level' => fake()->randomElement(['A', 'AA', 'AAA']),
            'status' => fake()->randomElement(['planning', 'in_progress', 'completed']),
            'audit_date' => fake()->dateTime(),
        ];
    }
}
