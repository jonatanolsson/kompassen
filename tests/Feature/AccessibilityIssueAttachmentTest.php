<?php

use App\Models\Team;
use App\Models\AccessibilityProject;
use App\Models\AccessibilityIssue;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can upload images when creating issue', function () {
    Storage::fake('public');
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $image = UploadedFile::fake()->image('screenshot.png');

    $response = $this->actingAs($user)->post(
        route('accessibility-issues.store', $project),
        [
            'title' => 'Missing alt text',
            'description' => 'Images need alt text',
            'severity' => 'major',
            'difficulty' => 'easy',
            'status' => 'open',
            'attachments' => [$image],
        ]
    );

    $response->assertRedirect(route('accessibility-projects.show', $project));
    $issue = AccessibilityIssue::first();
    expect($issue->attachments)->toHaveCount(1);
    expect($issue->attachments->first()->original_filename)->toBe('screenshot.png');
});

test('user can upload multiple images', function () {
    Storage::fake('public');
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $images = [
        UploadedFile::fake()->image('screenshot1.png'),
        UploadedFile::fake()->image('screenshot2.png'),
        UploadedFile::fake()->image('screenshot3.jpeg'),
    ];

    $response = $this->actingAs($user)->post(
        route('accessibility-issues.store', $project),
        [
            'title' => 'Missing alt text',
            'description' => 'Images need alt text',
            'severity' => 'major',
            'difficulty' => 'easy',
            'status' => 'open',
            'attachments' => $images,
        ]
    );

    $response->assertRedirect(route('accessibility-projects.show', $project));
    $issue = AccessibilityIssue::first();
    expect($issue->attachments)->toHaveCount(3);
});
