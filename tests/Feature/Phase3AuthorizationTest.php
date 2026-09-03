<?php

use App\Models\AccessibilityProject;
use App\Models\Team;
use App\Models\ProjectMember;
use App\Models\ProjectShareLink;
use App\Models\User;

// ==================== Project Member Tests ====================

test('owner can add member to project', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $member = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($owner)->post(
        route('project-members.store', $project),
        [
            'email' => $member->email,
            'role' => 'editor',
        ]
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id' => $member->id,
        'role' => 'editor',
    ]);
});

test('editor cannot add members to project', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $editor = User::factory()->create(['team_id' => $household->id]);
    $newMember = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $response = $this->actingAs($editor)->post(
        route('project-members.store', $project),
        [
            'email' => $newMember->email,
            'role' => 'viewer',
        ]
    );

    $response->assertStatus(403);
});

test('owner can update member role', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $member = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $editorMember = ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $member->id,
        'role' => 'editor',
    ]);

    $response = $this->actingAs($owner)->put(
        route('project-members.update', [$project, $editorMember]),
        ['role' => 'viewer']
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('project_members', [
        'id' => $editorMember->id,
        'role' => 'viewer',
    ]);
});

test('owner can remove member from project', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $member = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $memberRecord = ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $member->id,
        'role' => 'editor',
    ]);

    $response = $this->actingAs($owner)->delete(
        route('project-members.destroy', [$project, $memberRecord])
    );

    $response->assertRedirect();
    $this->assertDatabaseMissing('project_members', [
        'id' => $memberRecord->id,
    ]);
});

// ==================== Role-Based Authorization Tests ====================

test('editor can view and edit project', function () {
    $household = Team::factory()->create();
    $editor = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    // Can view
    $response = $this->actingAs($editor)->get("/accessibility-projects/{$project->id}");
    $response->assertStatus(200);

    // Can edit
    $response = $this->actingAs($editor)->put(
        route('accessibility-projects.update', $project),
        [
            'name' => 'Updated Name',
            'description' => 'Updated',
            'target_wcag_level' => 'AAA',
        ]
    );
    $response->assertRedirect();
});

test('editor cannot delete project', function () {
    $household = Team::factory()->create();
    $editor = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $response = $this->actingAs($editor)->delete(
        route('accessibility-projects.destroy', $project)
    );

    $response->assertStatus(403);
});

test('viewer can view project but not edit', function () {
    $household = Team::factory()->create();
    $viewer = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);

    // Can view
    $response = $this->actingAs($viewer)->get("/accessibility-projects/{$project->id}");
    $response->assertStatus(200);

    // Cannot edit
    $response = $this->actingAs($viewer)->put(
        route('accessibility-projects.update', $project),
        [
            'name' => 'Hacker Name',
            'description' => 'Hacked',
            'target_wcag_level' => 'AAA',
        ]
    );
    $response->assertStatus(403);
});

test('user not in project cannot access it', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}");

    $response->assertStatus(403);
});

// ==================== Share Link Tests ====================

test('owner can create share link', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($owner)->post(
        route('project-share-links.store', $project),
        ['expires_at' => now()->addDays(7)->format('Y-m-d')]
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('project_share_links', [
        'project_id' => $project->id,
        'created_by' => $owner->id,
    ]);
});

test('editor can create share link', function () {
    $household = Team::factory()->create();
    $editor = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $response = $this->actingAs($editor)->post(
        route('project-share-links.store', $project),
        ['expires_at' => null]
    );

    $response->assertRedirect();
});

test('viewer cannot create share link', function () {
    $household = Team::factory()->create();
    $viewer = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);

    $response = $this->actingAs($viewer)->post(
        route('project-share-links.store', $project),
        ['expires_at' => null]
    );

    $response->assertStatus(403);
});

test('owner can delete share link', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $link = ProjectShareLink::create([
        'project_id' => $project->id,
        'created_by' => $owner->id,
    ]);

    $response = $this->actingAs($owner)->delete(
        route('project-share-links.destroy', [$project, $link])
    );

    $response->assertRedirect();
    $this->assertDatabaseMissing('project_share_links', ['id' => $link->id]);
});

// ==================== Guest Access Tests ====================

test('guest can view project via valid share link', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $link = ProjectShareLink::create([
        'project_id' => $project->id,
        'created_by' => $owner->id,
    ]);

    $response = $this->get(route('projects.shared', $link->token));

    $response->assertStatus(200);
    $response->assertSee($project->name);
});

test('guest cannot view project with invalid share link', function () {
    $response = $this->get(route('projects.shared', 'invalid-token'));

    $response->assertStatus(404);
});

test('guest cannot view project with expired share link', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $link = ProjectShareLink::create([
        'project_id' => $project->id,
        'created_by' => $owner->id,
        'expires_at' => now()->subDay(),
    ]);

    $response = $this->get(route('projects.shared', $link->token));

    $response->assertStatus(410);
});

test('guest view does not show edit/delete buttons', function () {
    $household = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);

    $link = ProjectShareLink::create([
        'project_id' => $project->id,
        'created_by' => $owner->id,
    ]);

    $response = $this->get(route('projects.shared', $link->token));

    $response->assertStatus(200);
    $response->assertDontSee('Edit');
    $response->assertDontSee('Delete');
});
