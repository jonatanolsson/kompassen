<?php

use App\Models\AccessibilityPage;
use App\Models\AccessibilityProject;
use App\Models\Team;
use App\Models\User;

test('project listing shows project domains and counts', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'name' => 'Website audit',
    ]);

    AccessibilityPage::create([
        'project_id' => $project->id,
        'name' => 'Homepage',
        'url' => 'https://www.example.com/',
    ]);

    $this->actingAs($user)
        ->get(route('accessibility-projects.index'))
        ->assertSuccessful()
        ->assertSee('Website audit')
        ->assertSee('example.com')
        ->assertSee(__('WCAG Level'))
        ->assertSee(__('Status'));
});

test('project listing filters by project name or page domain', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $matchingProject = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'name' => 'Public website',
    ]);
    $otherProject = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'name' => 'Internal portal',
    ]);

    AccessibilityPage::create([
        'project_id' => $matchingProject->id,
        'name' => 'Homepage',
        'url' => 'https://public.example.com/',
    ]);
    AccessibilityPage::create([
        'project_id' => $otherProject->id,
        'name' => 'Dashboard',
        'url' => 'https://internal.example.com/',
    ]);

    $this->actingAs($user)
        ->get(route('accessibility-projects.index', ['search' => 'public.example.com']))
        ->assertSuccessful()
        ->assertSee('Public website')
        ->assertDontSee('Internal portal');
});
