<?php

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;

test('preview shows scope-aware metadata, statistics, and accessible modal structure', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'name' => 'Preview audit',
        'description' => '<p><strong>Important scope</strong></p><ul><li>Public website</li></ul>',
        'target_wcag_version' => '2.1',
        'target_wcag_level' => 'AA',
        'audit_date' => '2026-09-01',
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $inScopePage = $project->pages()->create([
        'name' => 'Homepage',
        'url' => 'https://example.com',
        'scope' => 'in_scope',
    ]);

    $outOfScopePage = $project->pages()->create([
        'name' => 'Legacy portal',
        'url' => 'https://example.com/legacy',
        'scope' => 'out_of_scope',
    ]);

    $wcagCriterion = WcagSuccessCriterion::create([
        'number' => '1.2.2',
        'level' => 'A',
        'name_en' => 'Captions (Prerecorded)',
        'name_sv' => 'Textning (inspelad)',
        'description_en' => 'Captions are provided for prerecorded audio content.',
        'description_sv' => 'Textning tillhandahålls för inspelat ljudinnehåll.',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/captions-prerecorded.html',
    ]);

    foreach (['critical', 'major', 'moderate', 'minor'] as $severity) {
        $issue = $project->issues()->create([
            'page_id' => $inScopePage->id,
            'title' => "{$severity} issue",
            'description' => '<p>Issue description</p>',
            'severity' => $severity,
            'difficulty' => 'easy',
            'status' => 'open',
        ]);

        if ($severity === 'critical') {
            $issue->wcagCriteria()->attach($wcagCriterion->id);
        }
    }

    $project->issues()->create([
        'page_id' => $outOfScopePage->id,
        'title' => 'Excluded issue',
        'description' => 'This issue is outside the audit scope.',
        'severity' => 'critical',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $response = $this->actingAs($user)
        ->get(route('accessibility-projects.preview', $project));

    $response
        ->assertSuccessful()
        ->assertSee(__('Accessibility Report'))
        ->assertSee(__('Executive summary'))
        ->assertSee(__('High risk'))
        ->assertSee(__('Prioritized actions'))
        ->assertSee(__('View details'))
        ->assertSee(__('Affected WCAG requirements'))
        ->assertSee('WCAG 2.1 nivå AA')
        ->assertSee('2026-09-01')
        ->assertSee(__('Moderate'))
        ->assertSee(__('Minor'))
        ->assertSee(__('Out of Scope'))
        ->assertSee('Legacy portal')
        ->assertDontSee('Excluded issue')
        ->assertSee('user-content', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false);
});
