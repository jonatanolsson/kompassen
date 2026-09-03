<?php

namespace Database\Factories;

use App\Models\AccessibilityIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessibilityIssue>
 */
class AccessibilityIssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accessibility_project_id' => \App\Models\AccessibilityProject::factory(),
            'page_id' => \App\Models\AccessibilityPage::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(['critical', 'major', 'moderate', 'minor']),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'component_area' => fake()->word(),
            'status' => fake()->randomElement(['open', 'resolved', 'wont_fix']),
        ];
    }
}
