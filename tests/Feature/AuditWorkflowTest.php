<?php

use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\CreateAccessibilityPage;
use App\Livewire\CreateAccessibilityProject;
use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\ProjectMember;
use App\Models\ProjectShareLink;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use Livewire\Livewire;

test('auditor can create project, record and resolve issue, then share audit results', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $team->id]);
    $editor = User::factory()->create(['team_id' => $team->id]);

    Livewire::actingAs($owner)
        ->test(CreateAccessibilityProject::class)
        ->set('name', 'Public service audit')
        ->set('target_wcag_level', 'AA')
        ->call('submit');

    $project = AccessibilityProject::query()
        ->where('name', 'Public service audit')
        ->firstOrFail();

    expect($project->members()->where('user_id', $owner->id)->value('role'))->toBe('owner');

    Livewire::actingAs($owner)
        ->test(CreateAccessibilityPage::class, ['project' => $project])
        ->set('name', 'Account overview')
        ->set('url', 'https://example.test/account')
        ->set('scope', 'in_scope')
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    $page = $project->pages()->firstOrFail();
    $criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '2.4.11',
        'level' => 'AA',
        'name_en' => 'Focus Not Obscured (Minimum)',
        'name_sv' => 'Fokus inte dolt (minimum)',
        'description_en' => 'Focused components are not entirely hidden.',
        'description_sv' => 'Fokuserade komponenter är inte helt dolda.',
    ]);
    $example = $criterion->examples()->create([
        'title' => 'Visible keyboard focus',
        'description' => 'Keep the focused control visible.',
    ]);

    Livewire::actingAs($owner)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Focused control is hidden')
        ->set('description', 'A sticky header fully obscures the focused control.')
        ->set('page_id', (string) $page->id)
        ->set('severity', 'major')
        ->set('difficulty', 'easy')
        ->set('wcag_criteria', [(string) $criterion->id])
        ->set('example_ids', [(string) $example->id])
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    $issue = $project->issues()->firstOrFail();
    expect($issue->page_id)->toBe($page->id)
        ->and($issue->wcagCriteria->first()->standard)->toBe('WCAG 2.2')
        ->and($issue->examples->first()->id)->toBe((string) $example->id);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $this->actingAs($owner)
        ->put(route('accessibility-issues.assign', [$project, $issue]), [
            'assigned_to' => $editor->id,
        ])
        ->assertRedirect();

    $this->actingAs($owner)
        ->patch(route('accessibility-issues.resolve', [$project, $issue]), [
            'resolution_status' => 'fixed',
            'resolution_notes' => 'Verified with keyboard navigation.',
        ])
        ->assertRedirect();

    expect($issue->refresh()->assigned_to)->toBe($editor->id)
        ->and($issue->status)->toBe('resolved')
        ->and($issue->resolution_status)->toBe('fixed')
        ->and($issue->resolution_notes)->toBe('Verified with keyboard navigation.');

    $this->actingAs($owner)
        ->post(route('accessibility-reports.store', $project), [
            'title' => 'Account overview audit',
            'scope' => 'Account overview page',
        ])
        ->assertRedirect();

    expect(AccessibilityReport::query()
        ->where('project_id', $project->id)
        ->where('title', 'Account overview audit')
        ->exists())->toBeTrue();

    $report = $project->reports()->firstOrFail();
    $this->actingAs($owner)
        ->post(route('project-share-links.store', [$project, $report]), ['expires_at' => now()->addDay()->toDateTimeString()])
        ->assertRedirect();

    $shareLink = ProjectShareLink::query()
        ->where('project_id', $project->id)
        ->firstOrFail();

    $this->get(route('projects.shared', $shareLink->token))
        ->assertSuccessful()
        ->assertSee('Public service audit')
        ->assertSee('Focused control is hidden')
        ->assertSee(__('Report generated on :date', ['date' => ($report->generated_at ?? $report->created_at)->translatedFormat('j M Y, H:i')]));
});
