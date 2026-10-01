<?php

use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\EditAccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use Livewire\Livewire;

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

test('issue detail uses a resolution modal instead of browser prompts', function () {
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

    $this->actingAs($user)
        ->get(route('accessibility-issues.show', [$project, $issue]))
        ->assertSuccessful()
        ->assertSee(__('Change resolution'))
        ->assertSee('resolve-issue-modal', false)
        ->assertDontSee('prompt(', false);
});

test('issue creation starts open and rejects direct status changes', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);

    Livewire::actingAs($user)->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Keyboard trap')
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    $issue = $project->issues()->firstOrFail();
    expect($issue->status)->toBe('open');

    $this->actingAs($user)->post(route('accessibility-issues.store', $project), [
        'title' => 'Skipped resolution',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'resolved',
    ])->assertSessionHasErrors('status');

    Livewire::actingAs($user)->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->set('title', 'Keyboard trap updated')
        ->call('update')
        ->assertHasNoErrors();

    expect($issue->refresh()->status)->toBe('open');
});

test('closing an issue requires a resolution note', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $issue = $project->issues()->create([
        'title' => 'Keyboard trap', 'severity' => 'major', 'difficulty' => 'easy', 'status' => 'open',
    ]);

    $this->actingAs($user)->patch(route('accessibility-issues.resolve', [$project, $issue]), [
        'resolution_status' => 'fixed',
    ])->assertSessionHasErrors('resolution_notes');

    expect($issue->refresh()->status)->toBe('open');
});

test('issues on excluded pages are marked as excluded without blocking their creation', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $page = $project->pages()->create([
        'name' => 'Excluded page', 'url' => 'https://example.test/old', 'scope' => 'out_of_scope',
    ]);

    Livewire::actingAs($user)->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('page_id', $page->id)
        ->assertSee(__('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.'))
        ->set('title', 'Old navigation')
        ->call('submit')
        ->assertRedirect();

    $issue = $project->issues()->firstOrFail();
    $this->actingAs($user)->get(route('accessibility-issues.show', [$project, $issue]))
        ->assertSuccessful()
        ->assertSee(__('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.'));

    Livewire::actingAs($user)->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->assertSee(__('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.'));
});

test('issue forms do not expose or accept examples from another team', function () {
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
    ]);
    $ownExample = $criterion->examples()->create(['title' => 'Team example', 'team_id' => $team->id]);
    $otherExample = $criterion->examples()->create(['title' => 'Other example', 'team_id' => $otherTeam->id]);

    Livewire::actingAs($user)->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('wcag_criteria', [(string) $criterion->id])
        ->assertSee('Team example')
        ->assertDontSee('Other example')
        ->set('title', 'Issue with foreign example')
        ->set('example_ids', [(string) $otherExample->id])
        ->call('submit')
        ->assertHasErrors('example_ids.0');

    expect($project->issues()->count())->toBe(0);

    Livewire::actingAs($user)->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('wcag_criteria', [(string) $criterion->id])
        ->set('title', 'Issue with team example')
        ->set('example_ids', [(string) $ownExample->id])
        ->call('submit')
        ->assertRedirect();

    $issue = $project->issues()->firstOrFail();
    Livewire::actingAs($user)->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->assertSee('Team example')
        ->assertDontSee('Other example')
        ->set('example_ids', [(string) $otherExample->id])
        ->call('update')
        ->assertHasErrors('example_ids.0');
});
