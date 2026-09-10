<?php

use App\Livewire\EditAccessibilityProject;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('can view accessibility projects index', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    $response = $this->actingAs($user)->get('/accessibility-projects');

    $response->assertStatus(200);
});

test('can create accessibility project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    $response = $this->actingAs($user)->post('/accessibility-projects', [
        'name' => 'Test Project',
        'description' => 'Test description',
        'target_wcag_level' => 'AA',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accessibility_projects', [
        'name' => 'Test Project',
        'team_id' => $team->id,
    ]);
});

test('can update project wcag level from edit form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'target_wcag_level' => 'AA',
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    Livewire::actingAs($user)
        ->test(EditAccessibilityProject::class, ['project' => $project])
        ->set('target_wcag_level', 'AAA')
        ->call('update')
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($project->refresh()->target_wcag_level)->toBe('AAA');
});

test('can view project details', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertSee($project->name);
});

test('can add page to project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/pages", [
        'name' => 'Homepage',
        'url' => 'https://example.com/',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accessibility_pages', [
        'name' => 'Homepage',
        'project_id' => $project->id,
    ]);
});

test('can report issue', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/issues", [
        'title' => 'Missing alt text',
        'description' => 'Images lack alt text',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accessibility_issues', [
        'title' => 'Missing alt text',
        'project_id' => $project->id,
    ]);
});
