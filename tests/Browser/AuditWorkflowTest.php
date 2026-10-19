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
    $project->issues()->create([
        'title' => 'Keyboard order is unclear',
        'severity' => 'minor',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

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
        ->assertSee(__('Current project preview'))
        ->assertSee(__('This preview reflects current project data. It is not a saved report version.'))
        ->assertSee('Missing alternative text')
        ->assertScript("document.querySelectorAll('button[data-flux-accordion-heading]').length", 2)
        ->assertScript("document.querySelector('button[data-flux-accordion-heading]').getAttribute('aria-expanded')", 'false')
        ->click('button[data-flux-accordion-heading]')
        ->assertScript("document.querySelector('button[data-flux-accordion-heading]').getAttribute('aria-expanded')", 'true');

    $page->script("document.querySelectorAll('button[data-flux-accordion-heading]')[1].click()");
    $page
        ->assertScript("document.querySelectorAll('button[data-flux-accordion-heading]')[0].getAttribute('aria-expanded')", 'true')
        ->assertScript("document.querySelectorAll('button[data-flux-accordion-heading]')[1].getAttribute('aria-expanded')", 'true')
        ->assertNoJavaScriptErrors();

    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();

    $page->click(__('Review readiness and generate report'))
        ->assertSee(__('Review report readiness'))
        ->fill('title', 'Browser audit report')
        ->click(__('Generate Report'))
        ->assertSee('Browser audit report')
        ->assertSee(__('Version :version', ['version' => 1]))
        ->assertSee(__('No tracked project changes have been detected since this version.'))
        ->assertNoJavaScriptErrors();

    expect($issue->refresh()->resolution_status)->toBe('fixed');
});
