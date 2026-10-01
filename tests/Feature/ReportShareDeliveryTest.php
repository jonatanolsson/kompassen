<?php

use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\ProjectMember;
use App\Models\ProjectShareLink;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $team = Team::factory()->create();
    $this->owner = User::factory()->create(['team_id' => $team->id]);
    $this->project = AccessibilityProject::factory()->create(['team_id' => $team->id, 'name' => 'Original project']);
    ProjectMember::create(['project_id' => $this->project->id, 'user_id' => $this->owner->id, 'role' => 'owner']);
    $this->report = AccessibilityReport::create([
        'project_id' => $this->project->id,
        'title' => 'Frozen report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<!DOCTYPE html><html><head><title>Frozen report</title></head><body>Original finding</body></html>',
        'created_by' => $this->owner->id,
    ]);
});

test('customer receives exactly saved report HTML after project changes', function () {
    $this->actingAs($this->owner)->post(route('project-share-links.store', [$this->project, $this->report]), [
        'expires_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
    ])->assertRedirect()->assertSessionHas('share_link_token');

    $link = ProjectShareLink::firstOrFail();
    expect($link->report_id)->toBe($this->report->id);

    AccessibilityReport::create([
        'project_id' => $this->project->id,
        'title' => 'Updated report',
        'target_wcag_level' => 'AA',
        'total_issues' => 1,
        'html_content' => '<html><body>New version only</body></html>',
        'created_by' => $this->owner->id,
    ]);
    $this->project->update(['name' => 'Private new project name']);
    $this->project->issues()->create([
        'title' => 'Unpublished issue',
        'severity' => 'critical',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    auth()->logout();
    $this->get(route('projects.shared', $link->token))
        ->assertSuccessful()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('Content-Security-Policy')
        ->assertSee('Original finding')
        ->assertDontSee('Unpublished issue')
        ->assertDontSee('New version only')
        ->assertDontSee('Private new project name');
    expect($this->get(route('projects.shared', $link->token))->getContent())->toBe($this->report->html_content);
});

test('customer link requires future expiry and a report in the authorized project', function () {
    $this->actingAs($this->owner)
        ->post(route('project-share-links.store', [$this->project, $this->report]), [])
        ->assertSessionHasErrors('expires_at');
    $this->post(route('project-share-links.store', [$this->project, $this->report]), [
        'expires_at' => now()->subDay()->toDateTimeString(),
    ])->assertSessionHasErrors('expires_at');

    $other = AccessibilityProject::factory()->create(['team_id' => $this->project->team_id]);
    $this->post(route('project-share-links.store', [$this->project, $this->report]), [
        'expires_at' => 'not a date',
    ])->assertSessionHasErrors('expires_at');
    $this->post(route('project-share-links.store', [$other, $this->report]), [
        'expires_at' => now()->addDay()->toDateTimeString(),
    ])->assertNotFound();
    expect(ProjectShareLink::count())->toBe(0);
});

test('expired and revoked customer links cannot be used', function () {
    $link = ProjectShareLink::create([
        'project_id' => $this->project->id,
        'report_id' => $this->report->id,
        'created_by' => $this->owner->id,
        'expires_at' => now()->subMinute(),
    ]);
    $this->get(route('projects.shared', $link->token))->assertGone();

    $link->update(['expires_at' => now()->addDay()]);
    $this->actingAs($this->owner)
        ->delete(route('project-share-links.destroy', [$this->project, $link]))
        ->assertRedirect();
    auth()->logout();
    $this->get(route('projects.shared', $link->token))->assertNotFound();
});

test('legacy and orphaned links never expose current project data', function () {
    $legacy = ProjectShareLink::create([
        'project_id' => $this->project->id,
        'created_by' => $this->owner->id,
    ]);
    $this->get(route('projects.shared', $legacy->token))->assertGone();
    expect($legacy->fresh())->not->toBeNull();

    $link = ProjectShareLink::create([
        'project_id' => $this->project->id,
        'report_id' => $this->report->id,
        'created_by' => $this->owner->id,
        'expires_at' => now()->addDay(),
    ]);
    $this->report->delete();
    $this->get(route('projects.shared', $link->token))->assertGone();
});

test('only authorized members can create or revoke links in their project', function () {
    $other = AccessibilityProject::factory()->create(['team_id' => $this->project->team_id]);
    $viewer = User::factory()->create(['team_id' => $this->project->team_id]);
    ProjectMember::create(['project_id' => $this->project->id, 'user_id' => $viewer->id, 'role' => 'viewer']);
    $expires = ['expires_at' => now()->addDay()->toDateTimeString()];

    $this->actingAs($viewer)
        ->post(route('project-share-links.store', [$this->project, $this->report]), $expires)
        ->assertForbidden();
    $this->actingAs($this->owner)
        ->post(route('project-share-links.store', [$other, $this->report]), $expires)
        ->assertNotFound();

    $link = ProjectShareLink::create([
        'project_id' => $other->id,
        'created_by' => $this->owner->id,
        'expires_at' => now()->addDay(),
    ]);
    $this->delete(route('project-share-links.destroy', [$this->project, $link]))->assertNotFound();
    $this->actingAs($viewer)
        ->delete(route('project-share-links.destroy', [$other, $link]))
        ->assertForbidden();
    expect($link->fresh())->not->toBeNull();
});

test('report UI distinguishes saved customer artifact from authenticated live preview', function () {
    $link = ProjectShareLink::create([
        'project_id' => $this->project->id,
        'report_id' => $this->report->id,
        'created_by' => $this->owner->id,
        'expires_at' => now()->addDay(),
    ]);
    $this->actingAs($this->owner)
        ->get(route('accessibility-reports.show', [$this->project, $this->report]))
        ->assertSuccessful()
        ->assertSee(__('Customer delivery'))
        ->assertSee(route('project-share-links.store', [$this->project, $this->report]), false)
        ->assertSee(route('projects.shared', $link->token), false);
    $this->get(route('accessibility-projects.show', $this->project))
        ->assertSee(__('Current project preview'))
        ->assertDontSee(route('projects.shared', $link->token), false);

    $viewer = User::factory()->create(['team_id' => $this->project->team_id]);
    ProjectMember::create(['project_id' => $this->project->id, 'user_id' => $viewer->id, 'role' => 'viewer']);
    $this->actingAs($viewer)->get(route('accessibility-reports.show', [$this->project, $this->report]))
        ->assertSuccessful()
        ->assertDontSee(route('projects.shared', $link->token), false)
        ->assertDontSee(route('project-share-links.store', [$this->project, $this->report]), false);
});
