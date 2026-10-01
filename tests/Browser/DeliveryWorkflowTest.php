<?php

use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\ProjectMember;
use App\Models\ProjectShareLink;
use App\Models\Team;
use App\Models\User;

test('owner generates report and shares project with public reader', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'name' => 'Delivery browser audit',
    ]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    $project->issues()->create([
        'title' => 'Keyboard focus is obscured',
        'description' => 'The sticky header covers focused controls.',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $page = visit('/login')
        ->fill('email', $owner->email)
        ->fill('password', 'password')
        ->click(__('Log in'))
        ->assertSee(__('Dashboard'))
        ->navigate(route('accessibility-reports.create', $project))
        ->fill('title', 'Delivery audit report')
        ->fill('scope', 'Homepage and account pages.')
        ->click('form[action="'.route('accessibility-reports.store', $project).'"] button[type="submit"]')
        ->assertSee('Delivery audit report');

    $report = AccessibilityReport::where('project_id', $project->id)->firstOrFail();
    $page->navigate(route('accessibility-reports.show', [$project, $report]))
        ->fill('expires_at', now()->addDays(7)->format('Y-m-d\TH:i'))
        ->click('form[action="'.route('project-share-links.store', [$project, $report]).'"] button[type="submit"]')
        ->assertSee(__('Share link created successfully.'));

    $shareLink = ProjectShareLink::query()
        ->where('project_id', $project->id)
        ->firstOrFail();

    $page->navigate(route('projects.shared', $shareLink->token))
        ->assertSee('Delivery browser audit')
        ->assertSee('Keyboard focus is obscured')
        ->assertNoJavaScriptErrors();
});
