<?php

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;

test('owner adds member, updates role, and assigns an issue in browser', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $team->id]);
    $editor = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $ownerMembership = ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    $issue = $project->issues()->create([
        'title' => 'Unassigned issue',
        'severity' => 'major',
        'difficulty' => 'medium',
        'status' => 'open',
    ]);

    $page = visit('/login')
        ->fill('email', $owner->email)
        ->fill('password', 'password')
        ->click(__('Log in'))
        ->assertSee(__('Dashboard'))
        ->navigate(route('project-members.index', $project))
        ->fill('email', $editor->email)
        ->select('role', 'editor')
        ->click('form[action="'.route('project-members.store', $project).'"] button[type="submit"]')
        ->assertSee(__('Member added successfully.'));

    $membership = ProjectMember::query()
        ->where('project_id', $project->id)
        ->where('user_id', $editor->id)
        ->firstOrFail();

    $updateRoute = route('project-members.update', [$project, $membership]);

    $page->select('select[aria-label="'.__('Role for :name', ['name' => $editor->name]).'"]', 'viewer')
        ->click('form[action="'.$updateRoute.'"] button[aria-label="'.__('Save role').'"]')
        ->assertSee(__('Member role updated.'));

    expect($membership->refresh()->role)->toBe('viewer');

    $page->navigate(route('accessibility-issues.show', [$project, $issue]))
        ->select('assigned_to', (string) $editor->id)
        ->click(__('Assign'))
        ->assertSee($editor->name)
        ->assertNoJavaScriptErrors();

    expect($issue->refresh()->assigned_to)->toBe($editor->id)
        ->and($ownerMembership->refresh()->role)->toBe('owner');
});
