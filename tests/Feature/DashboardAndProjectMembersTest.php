<?php

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;

test('dashboard shows scoped project summaries and quick actions', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $visibleProject = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'name' => 'Visible audit',
    ]);
    $hiddenProject = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'name' => 'Hidden audit',
    ]);

    ProjectMember::create([
        'project_id' => $visibleProject->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $visibleProject->issues()->create([
        'title' => 'Missing alternative text',
        'description' => 'Images need useful alternative text.',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $visibleProject->reports()->create([
        'title' => 'Initial audit',
        'target_wcag_level' => 'AA',
        'total_issues' => 1,
        'major_count' => 1,
        'html_content' => '<p>Initial audit</p>',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Visible audit')
        ->assertDontSee('Hidden audit')
        ->assertSee(__('Open Issues'))
        ->assertSee(__('Quick Actions'))
        ->assertSee(__('Report Issue'))
        ->assertSee(__('Generate Report'));
});

test('project members are visible and management controls are owner-only', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create([
        'team_id' => $team->id,
        'name' => 'Project Owner',
    ]);
    $editor = User::factory()->create([
        'team_id' => $team->id,
        'name' => 'Project Editor',
    ]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $this->actingAs($owner)
        ->get(route('accessibility-projects.show', $project))
        ->assertSuccessful()
        ->assertSee(route('project-members.index', $project));

    $this->actingAs($editor)
        ->get(route('accessibility-projects.show', $project))
        ->assertSuccessful()
        ->assertSee(route('project-members.index', $project));

    $this->actingAs($owner)
        ->get(route('project-members.index', $project))
        ->assertSuccessful()
        ->assertSee(__('Add Member'))
        ->assertSee(__('Only users from your team can be added.'))
        ->assertSee('Project Editor');

    $this->actingAs($editor)
        ->get(route('project-members.index', $project))
        ->assertSuccessful()
        ->assertDontSee(__('Add Member'))
        ->assertSee('Project Owner');
});

test('owner can add team member and cannot add someone from another team', function () {
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $team->id]);
    $teammate = User::factory()->create(['team_id' => $team->id]);
    $outsider = User::factory()->create(['team_id' => $otherTeam->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $this->actingAs($owner)
        ->post(route('project-members.store', $project), [
            'email' => $teammate->email,
            'role' => 'viewer',
        ])
        ->assertRedirect();

    expect(ProjectMember::query()
        ->where('project_id', $project->id)
        ->where('user_id', $teammate->id)
        ->value('role'))->toBe('viewer');

    $this->actingAs($owner)
        ->post(route('project-members.store', $project), [
            'email' => $outsider->email,
            'role' => 'viewer',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('email');

    expect(ProjectMember::query()
        ->where('project_id', $project->id)
        ->where('user_id', $outsider->id)
        ->exists())->toBeFalse();
});

test('non-owner cannot manage project members', function () {
    $team = Team::factory()->create();
    $editor = User::factory()->create(['team_id' => $team->id]);
    $member = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $projectMember = ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $this->actingAs($editor)
        ->post(route('project-members.store', $project), [
            'email' => $member->email,
            'role' => 'viewer',
        ])
        ->assertForbidden();

    $this->actingAs($editor)
        ->put(route('project-members.update', [$project, $projectMember]), [
            'role' => 'viewer',
        ])
        ->assertForbidden();

    $this->actingAs($editor)
        ->delete(route('project-members.destroy', [$project, $projectMember]))
        ->assertForbidden();
});
