<?php

use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\EditAccessibilityIssue;
use App\Models\AccessibilityIssueAttachment;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\ProjectShareLink;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('nested project resources cannot be accessed through another project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $otherProject = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $page = $otherProject->pages()->create([
        'name' => 'Other page',
        'url' => 'https://other.example.test',
        'scope' => 'in_scope',
    ]);
    $issue = $otherProject->issues()->create([
        'page_id' => $page->id,
        'title' => 'Other issue',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $member = ProjectMember::create([
        'project_id' => $otherProject->id,
        'user_id' => $user->id,
        'role' => 'viewer',
    ]);
    $shareLink = ProjectShareLink::create([
        'project_id' => $otherProject->id,
        'created_by' => $user->id,
    ]);
    $attachment = AccessibilityIssueAttachment::create([
        'accessibility_issue_id' => $issue->id,
        'filename' => 'screenshot.png',
        'original_filename' => 'screenshot.png',
        'mime_type' => 'image/png',
        'size' => 100,
        'path' => 'accessibility-issues/'.$issue->id.'/screenshot.png',
    ]);

    $this->actingAs($user)
        ->get(route('accessibility-issues.show', [$project, $issue]))
        ->assertNotFound();

    $this->actingAs($user)
        ->delete(route('accessibility-issues.destroy', [$project, $issue]))
        ->assertNotFound();

    $this->actingAs($user)
        ->put(route('accessibility-pages.update', [$project, $page]), [
            'name' => 'Tampered page',
            'url' => $page->url,
            'scope' => 'in_scope',
        ])
        ->assertNotFound();

    $this->actingAs($user)
        ->put(route('project-members.update', [$project, $member]), ['role' => 'owner'])
        ->assertNotFound();

    $this->actingAs($user)
        ->delete(route('project-share-links.destroy', [$project, $shareLink]))
        ->assertNotFound();

    $this->actingAs($user)
        ->delete(route('accessibility-issue-attachments.destroy', [$project, $issue, $attachment]))
        ->assertNotFound();
});

test('issue page must belong to project when creating an issue', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $otherProject = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $otherPage = $otherProject->pages()->create([
        'name' => 'Other page',
        'url' => 'https://other.example.test',
        'scope' => 'in_scope',
    ]);

    $this->actingAs($user)
        ->post(route('accessibility-issues.store', $project), [
            'title' => 'Cross-project issue',
            'page_id' => $otherPage->id,
            'severity' => 'major',
            'difficulty' => 'easy',
            'status' => 'open',
        ])
        ->assertSessionHasErrors('page_id');

    $this->assertDatabaseMissing('accessibility_issues', [
        'project_id' => $project->id,
        'title' => 'Cross-project issue',
    ]);
});

test('issues can only be assigned to members of the current project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $otherUser = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $otherProject = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);
    ProjectMember::create([
        'project_id' => $otherProject->id,
        'user_id' => $otherUser->id,
        'role' => 'owner',
    ]);

    $issue = $project->issues()->create([
        'title' => 'Unassigned issue',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $this->actingAs($user)
        ->put(route('accessibility-issues.assign', [$project, $issue]), [
            'assigned_to' => $otherUser->id,
        ])
        ->assertSessionHasErrors('assigned_to');

    expect($issue->refresh()->assigned_to)->toBeNull();
});

test('livewire issue forms reject pages from another project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $otherProject = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $otherPage = $otherProject->pages()->create([
        'name' => 'Other page',
        'url' => 'https://other.example.test',
        'scope' => 'in_scope',
    ]);
    $issue = $project->issues()->create([
        'title' => 'Existing issue',
        'description' => 'Issue description',
        'component_area' => 'content',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'New issue')
        ->set('page_id', $otherPage->id)
        ->call('submit')
        ->assertHasErrors('page_id');

    Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->set('page_id', $otherPage->id)
        ->call('update')
        ->assertHasErrors('page_id');

    expect($issue->refresh()->page_id)->toBeNull();
});

test('viewers can view issues but cannot manage project members', function () {
    $team = Team::factory()->create();
    $viewer = User::factory()->create(['team_id' => $team->id]);
    $editor = User::factory()->create(['team_id' => $team->id]);
    $newMember = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);
    $member = ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $newMember->id,
        'role' => 'viewer',
    ]);
    $issue = $project->issues()->create([
        'title' => 'Visible issue',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $this->actingAs($viewer)
        ->get(route('accessibility-issues.show', [$project, $issue]))
        ->assertSuccessful()
        ->assertSee('Visible issue');

    $this->actingAs($editor)
        ->put(route('project-members.update', [$project, $member]), ['role' => 'owner'])
        ->assertForbidden();

    $this->actingAs($viewer)
        ->post(route('project-members.store', $project), [
            'email' => $newMember->email,
            'role' => 'editor',
        ])
        ->assertForbidden();

    $this->actingAs($viewer)
        ->delete(route('project-members.destroy', [$project, $member]))
        ->assertForbidden();
});
