<?php

namespace Database\Factories;

use App\Models\AccessibilityPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessibilityPage>
 */
class AccessibilityPageFactory extends Factory
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
            'name' => fake()->sentence(),
            'url' => fake()->url(),
            'description' => fake()->paragraph(),
        ];
    }
}
