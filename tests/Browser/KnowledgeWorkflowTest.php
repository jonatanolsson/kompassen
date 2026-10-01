<?php

use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;

test('auditor can open knowledge base and see WCAG criteria in browser', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '2.4.11',
        'level' => 'AA',
        'name_en' => 'Focus Not Obscured (Minimum)',
        'name_sv' => 'Fokus inte dolt (minimum)',
        'description_en' => 'Focused components are not entirely hidden.',
        'description_sv' => 'Fokuserade komponenter är inte helt dolda.',
    ]);
    $criterion->examples()->create([
        'title' => 'Keep focus visible',
        'description' => 'Do not cover focused controls with sticky content.',
        'code_snippet' => 'flowchart LR; A[Start] --> B[Review]',
        'code_language' => 'mermaid',
    ]);

    $page = visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click(__('Log in'))
        ->assertSee(__('Dashboard'))
        ->navigate(route('wcag.knowledge-base'))
        ->assertSee('2.4.11')
        ->click('2.4.11')
        ->assertSee('Keep focus visible')
        ->wait(2)
        ->assertNoConsoleLogs()
        ->assertNoJavaScriptErrors()
        ->assertScript("document.querySelector('[data-mermaid-diagram][data-rendered=\"true\"] svg') !== null")
        ->assertScript("getComputedStyle(document.querySelector('[data-mermaid-diagram]')).backgroundColor", 'rgb(255, 255, 255)')
        ->assertScript("getComputedStyle(document.querySelector('[data-mermaid-diagram] svg .node rect')).fill", 'rgb(219, 234, 254)');

    $page->script("document.documentElement.classList.add('dark')");
    $page->wait(1)
        ->assertScript("document.querySelector('[data-mermaid-diagram][data-theme=\"dark\"] svg') !== null")
        ->assertScript("getComputedStyle(document.querySelector('[data-mermaid-diagram]')).backgroundColor", 'rgb(24, 24, 27)')
        ->assertScript("getComputedStyle(document.querySelector('[data-mermaid-diagram] svg .node rect')).fill", 'rgb(30, 58, 138)');

    $page->script("document.documentElement.classList.remove('dark')");
    $page->wait(1)
        ->assertScript("document.querySelector('[data-mermaid-diagram][data-theme=\"light\"] svg') !== null")
        ->assertScript("getComputedStyle(document.querySelector('[data-mermaid-diagram] svg .node rect')).fill", 'rgb(219, 234, 254)')
        ->resize(390, 844)
        ->assertNoJavaScriptErrors();
});
