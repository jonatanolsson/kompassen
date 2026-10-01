<?php

use App\Models\AccessibilityProject;
use App\Models\Team;
use App\Models\User;

test('auditor can create a project, page, and resolved issue in a browser', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    $page = visit('/login')->on()->iPhoneSE()
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click(__('Log in'))
        ->assertSee(__('Dashboard'))
        ->navigate('/accessibility-projects/create')
        ->assertSee(__('Create Project'))
        ->fill('name', 'Browser audit')
        ->keys('form[wire\\:submit="submit"] button[type="submit"][data-flux-button]', 'Enter')
        ->assertNoJavaScriptErrors()
        ->assertSee(__('Project created successfully.'));

    $project = AccessibilityProject::query()->where('name', 'Browser audit')->firstOrFail();

    $page->navigate(route('accessibility-pages.create', $project))
        ->assertSee(__('Add Page or Service'))
        ->fill('name', 'Homepage')
        ->fill('url', 'https://example.test')
        ->click('form[wire\\:submit="submit"] button[type="submit"][data-flux-button]')
        ->assertSee(__('Page or service created successfully.'));

    expect($project->pages()->where('name', 'Homepage')->exists())->toBeTrue();

    $page->navigate(route('accessibility-issues.create', $project))
        ->assertSee(__('Report Issue'))
        ->fill('title', 'Missing alternative text')
        ->click('form[wire\\:submit="submit"] button[type="submit"][data-flux-button]')
        ->assertSee(__('Issue created successfully.'));

    $issue = $project->issues()->where('title', 'Missing alternative text')->firstOrFail();

    $page->navigate(route('accessibility-issues.show', [$project, $issue]))
        ->click(__('Actions'))
        ->click(__('Change resolution'))
        ->select('resolution_status', 'fixed')
        ->fill('resolution_notes', 'Verified with keyboard navigation.')
        ->click(__('Save'))
        ->assertSee('Verified with keyboard navigation.')
        ->assertSee('Missing alternative text')
        ->assertNoJavaScriptErrors();

    $page->navigate(route('accessibility-projects.preview', $project))
        ->assertSee('Missing alternative text')
        ->assertScript("document.querySelector('details[data-print-expand]').open", false);

    $page->script("window.dispatchEvent(new Event('beforeprint'))");
    $page->assertScript("document.querySelector('details[data-print-expand]').open", true);
    $page->script("window.dispatchEvent(new Event('afterprint'))");
    $page->assertScript("document.querySelector('details[data-print-expand]').open", false)
        ->assertNoJavaScriptErrors();

    expect($issue->refresh()->resolution_status)->toBe('fixed')
        ->and($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
});
