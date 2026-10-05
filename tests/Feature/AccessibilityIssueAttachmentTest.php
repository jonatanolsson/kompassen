<?php

use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\EditAccessibilityIssue;
use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('issue forms show a visible image upload dropzone', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);
    $issue = $project->issues()->create([
        'title' => 'Missing alt text',
        'description' => 'Images need alt text',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $createHtml = Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->assertSee(__('Choose images or drop them here'))
        ->assertSee(__('JPG, PNG, GIF or WebP. Max 5 MB per image.'))
        ->html();

    expect($createHtml)->toContain('data-flux-file-upload-dropzone')
        ->and(strpos($createHtml, 'data-flux-file-upload-dropzone'))
        ->toBeLessThan(strpos($createHtml, '<details'));

    Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->assertSee(__('Choose images or drop them here'))
        ->assertSee(__('JPG, PNG, GIF or WebP. Max 5 MB per image.'))
        ->assertSee('open: true', false);
});

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
    expect($issue->attachments->first()->path)->not->toBeEmpty();
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

test('user can upload an image when editing an issue', function () {
    Storage::fake('public');
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);
    $issue = $project->issues()->create([
        'title' => 'Missing alt text',
        'description' => 'Images need alt text',
        'severity' => 'major',
        'difficulty' => 'easy',
        'component_area' => 'content',
        'status' => 'open',
    ]);

    $image = UploadedFile::fake()->image('edited-screenshot.png');

    Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->set('attachments', [$image])
        ->call('update')
        ->assertRedirect(route('accessibility-issues.edit', [$project, $issue]));

    $attachment = $issue->attachments()->first();

    expect($attachment->original_filename)->toBe('edited-screenshot.png')
        ->and($attachment->filename)->not->toBeEmpty()
        ->and($attachment->path)->not->toBeEmpty();

    Storage::disk('public')->assertExists($attachment->path);
});
