<?php

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\TestingMethodology;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        'audit_scope' => 'Test the public application and account registration.',
        'scope_limitations' => 'Only desktop browsers are available.',
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
        'scope_exclusion_reason' => 'The portal is scheduled for retirement.',
    ]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard-only test',
        'team_id' => $team->id,
        'category' => 'testing_tool',
        'description' => 'Check all controls using only the keyboard.',
        'is_custom' => true,
    ]);
    $project->methodologies()->attach($methodology->id, [
        'id' => (string) Str::ulid(),
        'notes' => 'Repeat on every included page.',
    ]);

    $wcagCriterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.1',
        'number' => '1.2.2',
        'level' => 'A',
        'name_en' => 'Captions (Prerecorded)',
        'name_sv' => 'Textning (inspelad)',
        'description_en' => 'Captions are provided for prerecorded audio content.',
        'description_sv' => 'Textning tillhandahålls för inspelat ljudinnehåll.',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/captions-prerecorded.html',
    ]);
    $wcag22Criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '1.2.2',
        'level' => 'A',
        'name_en' => 'Captions (Prerecorded), updated',
        'name_sv' => 'Textning (inspelad), uppdaterad',
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
            $issue->wcagCriteria()->attach($wcagCriterion->id, ['failure_type' => 'manual']);
            $issue->wcagCriteria()->attach($wcag22Criterion->id);
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

    $projectWideIssue = $project->issues()->create([
        'page_id' => null,
        'title' => 'Project-wide issue',
        'description' => 'This issue affects the entire project.',
        'solution_suggestions' => '<p>Apply this fix across the project.</p>',
        'severity' => 'major',
        'difficulty' => 'medium',
        'status' => 'open',
    ]);
    $criticalIssue = $project->issues()->where('title', 'critical issue')->firstOrFail();

    $response = $this->actingAs($user)
        ->get(route('accessibility-projects.preview', $project));

    expect($response->getContent())->toMatch(
        '/<section(?=[^>]*aria-labelledby="page-title-'.$inScopePage->id.'")(?=[^>]*class="(?![^"]*(?:rounded|border|bg-))[^"]*space-y-3[^"]*")[^>]*>/'
    );

    $response
        ->assertSuccessful()
        ->assertSee(__('Current project preview'))
        ->assertSee(__('This preview reflects current project data. It is not a saved report version.'))
        ->assertSee(__('Review readiness and generate report'))
        ->assertSee(route('accessibility-reports.create', $project), false)
        ->assertSee(route('accessibility-projects.edit', $project), false)
        ->assertSee(route('accessibility-pages.edit', [$project, $outOfScopePage]), false)
        ->assertSee(route('accessibility-issues.edit', [$project, $criticalIssue]), false)
        ->assertSee('Keyboard-only test')
        ->assertSee('Check all controls using only the keyboard.')
        ->assertSee('Repeat on every included page.')
        ->assertSee(__('Executive summary'))
        ->assertSee(__('High risk'))
        ->assertSee(__('Prioritized actions'))
        ->assertSee(__('View details'))
        ->assertSee(__('Affected WCAG requirements'))
        ->assertSee('WCAG 2.1 · 1.2.2')
        ->assertSee('WCAG 2.2 · 1.2.2')
        ->assertSee(__('Assessment method'))
        ->assertSee(__('Manual assessment'))
        ->assertSee(__('Nice to have'))
        ->assertSee('WCAG 2.1 nivå AA')
        ->assertSee(Carbon::parse('2026-09-01')->locale('sv')->translatedFormat('j M Y'))
        ->assertSee(__('Moderate'))
        ->assertSee(__('Minor'))
        ->assertSee(__('Out of Scope'))
        ->assertSee('Legacy portal')
        ->assertSee('Test the public application and account registration.')
        ->assertSee('Only desktop browsers are available.')
        ->assertSee('The portal is scheduled for retirement.')
        ->assertDontSee('Excluded issue')
        ->assertSee('id="page-title-'.$inScopePage->id.'"', false)
        ->assertSee('id="page-issues-title-'.$inScopePage->id.'"', false)
        ->assertSee(__('Issues').' (4)')
        ->assertSee('rounded-lg border border-zinc-200 bg-white p-0 dark:border-zinc-700 dark:bg-zinc-900', false)
        ->assertSee('aria-level="5"', false)
        ->assertSee('aria-level="6"', false)
        ->assertSee('id="issue-'.$projectWideIssue->id.'"', false)
        ->assertSee('aria-level="3"', false)
        ->assertSee('aria-level="4"', false)
        ->assertSee('Apply this fix across the project.')
        ->assertSee('<h3', false)
        ->assertSee('<h4', false)
        ->assertSee('leading-[1.75]', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false);

    expect($response->getContent())
        ->toContain(__('View criterion details: :criterion', ['criterion' => 'WCAG 2.1 1.2.2']))
        ->toContain(__('View criterion details: :criterion', ['criterion' => 'WCAG 2.2 1.2.2']));
});

test('current project preview flags scope gaps and limits repair links to editors', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $team->id]);
    $viewer = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'audit_scope' => null,
        'scope_limitations' => null,
    ]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $owner->id, 'role' => 'owner']);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $viewer->id, 'role' => 'viewer']);
    $excludedPage = $project->pages()->create([
        'name' => 'Legacy service',
        'url' => 'https://example.com/legacy',
        'scope' => 'out_of_scope',
        'scope_exclusion_reason' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('accessibility-projects.preview', $project))
        ->assertSuccessful()
        ->assertSee(__('Scope needs review'))
        ->assertSee(route('accessibility-projects.edit', $project), false)
        ->assertSee(route('accessibility-pages.edit', [$project, $excludedPage]), false)
        ->assertSee(route('accessibility-reports.create', $project), false);

    $this->actingAs($viewer)
        ->get(route('accessibility-projects.preview', $project))
        ->assertSuccessful()
        ->assertSee(__('Scope needs review'))
        ->assertDontSee(route('accessibility-projects.edit', $project), false)
        ->assertDontSee(route('accessibility-pages.edit', [$project, $excludedPage]), false)
        ->assertDontSee(route('accessibility-reports.create', $project), false);
});
