<?php

namespace Database\Factories;

use App\Models\AccessibilityIssueAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessibilityIssueAttachment>
 */
class AccessibilityIssueAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accessibility_issue_id' => \App\Models\AccessibilityIssue::factory(),
            'filename' => \Illuminate\Support\Str::ulid() . '.png',
            'original_filename' => $this->faker->word() . '.png',
            'mime_type' => 'image/png',
            'size' => $this->faker->numberBetween(10000, 500000),
            'path' => 'accessibility-issues/' . \Illuminate\Support\Str::ulid() . '/' . \Illuminate\Support\Str::ulid() . '.png',
        ];
    }
}
