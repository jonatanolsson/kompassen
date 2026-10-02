<?php

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\TestingMethodology;
use App\Models\User;

test('team owner can manage testing methodologies on a mobile viewport', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $team->update(['owner_id' => $user->id]);

    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $page = visit('/login')->on()->iPhoneSE()
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click(__('Log in'))
        ->assertSee(__('Dashboard'))
        ->navigate(route('testing-methodologies'))
        ->click(__('Add Testing Methodology'))
        ->fill('Name', 'Browser testing method')
        ->select('Category', 'testing_tool')
        ->fill('Description', 'Method created by browser test.')
        ->click(__('Save Methodology'))
        ->assertSee('Browser testing method')
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors();

    $methodology = TestingMethodology::query()
        ->where('name', 'Browser testing method')
        ->firstOrFail();

    $page->navigate(route('accessibility-projects.show', $project))
        ->click(__('Add Method'))
        ->select(__('Method / Tool'), 'Browser testing method')
        ->fill(__('Version / Device / Notes'), 'NVDA 2025')
        ->click(__('Add Method'))
        ->assertSee('NVDA 2025')
        ->click('[aria-label="Edit notes for Browser testing method"]:visible')
        ->fill(__('e.g. version 2024, Windows 11...'), 'NVDA 2026')
        ->click(__('Save'))
        ->assertSee('NVDA 2026')
        ->click('[aria-label="Remove Browser testing method from this project?"]:visible')
        ->click(__('Remove'))
        ->assertDontSee('NVDA 2026')
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors();

    expect($project->methodologies()->whereKey($methodology->id)->exists())->toBeFalse();

    $page->navigate(route('testing-methodologies'))
        ->click('button[aria-label="Delete"]:visible')
        ->click(__('Delete'))
        ->assertDontSee('Browser testing method')
        ->assertNoJavaScriptErrors();

    expect($methodology->fresh())->toBeNull();
});
