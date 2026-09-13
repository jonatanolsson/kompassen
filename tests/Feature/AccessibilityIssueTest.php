<?php

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;

test('resolving an issue keeps status and resolution status synchronized', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $issue = $project->issues()->create([
        'title' => 'Missing alternative text',
        'description' => 'Images lack alternatives.',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $response = $this->actingAs($user)->patch(route('accessibility-issues.resolve', [$project, $issue]), [
        'resolution_status' => 'fixed',
        'resolution_notes' => 'Corrected image alternatives.',
    ]);

    $response->assertRedirect();
    expect($issue->refresh()->status)->toBe('resolved')
        ->and($issue->resolution_status)->toBe('fixed')
        ->and($issue->resolution_notes)->toBe('Corrected image alternatives.')
        ->and($issue->resolved_at)->not->toBeNull();
});

test('reopening an issue clears its resolved timestamp', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $issue = $project->issues()->create([
        'title' => 'Missing alternative text',
        'description' => 'Images lack alternatives.',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'resolved',
    ]);

    $response = $this->actingAs($user)->patch(route('accessibility-issues.resolve', [$project, $issue]), [
        'resolution_status' => 'open',
    ]);

    $response->assertRedirect();
    expect($issue->refresh()->status)->toBe('open')
        ->and($issue->resolution_status)->toBe('open')
        ->and($issue->resolved_at)->toBeNull();
});
