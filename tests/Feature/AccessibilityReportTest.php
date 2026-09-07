<?php

use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;

test('can view reports index', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports");

    $response->assertStatus(200);
    $response->assertSee('Reports for '.$project->name);
});

test('can view report creation form', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports/create");

    $response->assertStatus(200);
    $response->assertSee(__('Generate Report'));
});

test('can generate report with issues', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    // Create some issues
    $project->issues()->createMany([
        [
            'title' => 'Missing alt text',
            'description' => 'Images lack alt text',
            'severity' => 'critical',
            'difficulty' => 'easy',
            'status' => 'open',
        ],
        [
            'title' => 'Low contrast',
            'description' => 'Text has low contrast',
            'severity' => 'major',
            'difficulty' => 'medium',
            'status' => 'open',
        ],
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Initial Audit Report',
        'scope' => 'Tested all pages',
    ]);

    $response->assertRedirect();

    // Check that the report exists
    expect(AccessibilityReport::where([
        'project_id' => $project->id,
        'title' => 'Initial Audit Report',
        'scope' => 'Tested all pages',
        'total_issues' => 2,
        'critical_count' => 1,
        'major_count' => 1,
    ])->exists())->toBeTrue();
});

test('can view generated report', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports/{$report->id}");

    $response->assertStatus(200);
    $response->assertSee('Test Report');
});

test('can download report as pdf', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1><p>Test content</p>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports/{$report->id}/download");

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');
});

test('only members can view reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $other_user = User::factory()->create(['team_id' => $household->id]);

    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    // Non-member trying to access report
    $response = $this->actingAs($other_user)->get("/accessibility-projects/{$project->id}/reports/{$report->id}");
    $response->assertStatus(403);
});

test('editor can generate reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Editor Report',
    ]);

    $response->assertRedirect();
    expect(AccessibilityReport::where([
        'project_id' => $project->id,
        'title' => 'Editor Report',
    ])->exists())->toBeTrue();
});

test('viewer cannot generate reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'viewer',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Viewer Report',
    ]);

    $response->assertStatus(403);
    expect(AccessibilityReport::where('title', 'Viewer Report')->exists())->toBeFalse();
});

test('only owner can delete reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->delete("/accessibility-projects/{$project->id}/reports/{$report->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('accessibility_reports', [
        'id' => $report->id,
    ]);
});

test('editor cannot delete reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->delete("/accessibility-projects/{$project->id}/reports/{$report->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('accessibility_reports', [
        'id' => $report->id,
    ]);
});

test('report displays wcag criteria with localized descriptions', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    // Create a WCAG criterion
    $wcagCriterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehål',
        'description_en' => 'All non-text content that is presented to the user has a text alternative.',
        'description_sv' => 'Allt icke-textinnehål har ett textalternativ.',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.1.1.html',
    ]);

    // Create an issue and link it to WCAG criterion
    $issue = $project->issues()->create([
        'title' => 'Missing alt text on images',
        'description' => 'All images lack alternative text',
        'severity' => 'critical',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $issue->wcagCriteria()->attach($wcagCriterion->id);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Audit with WCAG Links',
        'scope' => 'Full website',
    ]);

    $response->assertRedirect();

    $report = AccessibilityReport::where('title', 'Audit with WCAG Links')->first();
    expect($report)->not->toBeNull();

    // Verify WCAG criterion number appears in report
    expect($report->html_content)->toContain('1.1.1');
    // Report may contain localized name_sv or name_en depending on app locale
    $this->assertTrue(str_contains($report->html_content, $wcagCriterion->name_en) || str_contains($report->html_content, $wcagCriterion->name_sv));
});
