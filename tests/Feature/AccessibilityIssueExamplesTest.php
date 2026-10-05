<?php

use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\EditAccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use App\Services\IssueDiagramSvg;
use Livewire\Livewire;

test('user can link multiple knowledge base examples when creating an issue', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternatives are provided.',
        'description_sv' => 'Textalternativ tillhandahålls.',
        'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/non-text-content',
    ]);
    $firstExample = $criterion->examples()->create(['title' => 'Image alternative']);
    $secondExample = $criterion->examples()->create(['title' => 'Decorative image']);
    $diagram = "flowchart LR\n    A[Issue] --> B[Impact]";

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->assertSee('1.1.1')
        ->set('title', 'Missing image alternatives')
        ->set('mermaid_diagram', $diagram)
        ->set('mermaid_svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10" fill="#dbeafe"/></svg>')
        ->set('mermaid_source_hash', IssueDiagramSvg::hashSource($diagram))
        ->set('wcag_criteria.0', (string) $criterion->id)
        ->assertSee('Image alternative')
        ->assertSee('open: true', false)
        ->set('example_ids.0', (string) $firstExample->id)
        ->set('example_ids.1', (string) $secondExample->id)
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    $issue = $project->issues()->latest()->firstOrFail();

    expect($issue->examples->pluck('id')->all())
        ->toBe([(string) $firstExample->id, (string) $secondExample->id])
        ->and($issue->mermaid_diagram)->toContain('flowchart LR');
});

test('user can change linked knowledge base examples when editing an issue', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternatives are provided.',
        'description_sv' => 'Textalternativ tillhandahålls.',
        'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/non-text-content',
    ]);
    $firstExample = $criterion->examples()->create(['title' => 'Image alternative']);
    $secondExample = $criterion->examples()->create(['title' => 'Decorative image']);
    $issue = $project->issues()->create([
        'title' => 'Missing image alternatives',
        'description' => 'Images need alternatives.',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $issue->wcagCriteria()->attach($criterion);
    $issue->examples()->attach($firstExample);
    $diagram = "flowchart TD\n    A[Issue] --> B[Resolution]";

    Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->assertSee('1.1.1')
        ->assertSet('example_ids', [(string) $firstExample->id])
        ->set('wcag_criteria', [])
        ->assertSet('example_ids', [])
        ->set('wcag_criteria.0', (string) $criterion->id)
        ->assertSee('Decorative image')
        ->assertSee('open: true', false)
        ->set('example_ids.0', (string) $secondExample->id)
        ->set('mermaid_diagram', $diagram)
        ->set('mermaid_svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10" fill="#dbeafe"/></svg>')
        ->set('mermaid_source_hash', IssueDiagramSvg::hashSource($diagram))
        ->call('update')
        ->assertRedirect(route('accessibility-issues.edit', [$project, $issue]));

    expect($issue->refresh()->examples->pluck('id')->all())
        ->toBe([(string) $secondExample->id])
        ->and($issue->mermaid_diagram)->toContain('flowchart TD');
});

test('issue example selection is limited to selected WCAG criteria', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternatives are provided.',
        'description_sv' => 'Textalternativ tillhandahålls.',
        'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/non-text-content',
    ]);
    $otherCriterion = WcagSuccessCriterion::create([
        'number' => '1.2.1',
        'level' => 'A',
        'name_en' => 'Audio-only and Video-only',
        'name_sv' => 'Enbart ljud och enbart video',
        'description_en' => 'Alternatives are provided.',
        'description_sv' => 'Alternativ tillhandahålls.',
        'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/audio-only-and-video-only-prerecorded',
    ]);
    $otherExample = $otherCriterion->examples()->create(['title' => 'Unrelated example']);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Invalid example link')
        ->set('wcag_criteria.0', (string) $criterion->id)
        ->set('example_ids.0', (string) $otherExample->id)
        ->call('submit')
        ->assertHasErrors(['example_ids.0']);

    expect($project->issues()->count())->toBe(0);
});

test('issue detail shows an overview of attached criteria and knowledge base examples', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternatives are provided.',
        'description_sv' => 'Textalternativ tillhandahålls.',
        'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/non-text-content',
    ]);
    $example = $criterion->examples()->create([
        'title' => 'Use meaningful alternative text',
        'description' => 'Describe the purpose of the image.',
        'code_snippet' => '<img src="hero.jpg" alt="Product overview">',
        'code_language' => 'html',
    ]);
    $issue = $project->issues()->create([
        'title' => 'Missing image alternatives',
        'description' => 'Images need alternatives.',
        'mermaid_diagram' => "flowchart LR\n    A[Issue] --> B[Impact]",
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $issue->examples()->attach($example);
    $issue->wcagCriteria()->attach($criterion);

    $this->actingAs($user)
        ->get(route('accessibility-issues.show', [$project, $issue]))
        ->assertSuccessful()
        ->assertSee(__('WCAG Success Criteria'))
        ->assertSee('<table', false)
        ->assertSee('<th', false)
        ->assertSee(__('Criterion'))
        ->assertSee(__('Level'))
        ->assertSee('1.1.1')
        ->assertSee('Icke-textinnehåll')
        ->assertSee('Textalternativ tillhandahålls.')
        ->assertSee('href="https://www.w3.org/WAI/WCAG22/Understanding/non-text-content"', false)
        ->assertSee(__('Knowledge Base Examples'))
        ->assertSee(__('Issue diagram'))
        ->assertSee('data-mermaid-diagram')
        ->assertSee('Use meaningful alternative text')
        ->assertSee('Describe the purpose of the image.');
});

test('viewer can see issue assignment but not change it', function () {
    $team = Team::factory()->create();
    $viewer = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
    $issue = $project->issues()->create([
        'title' => 'Unassigned issue',
        'severity' => 'major',
        'difficulty' => 'medium',
        'status' => 'open',
    ]);

    $this->actingAs($viewer)
        ->get(route('accessibility-issues.show', [$project, $issue]))
        ->assertSuccessful()
        ->assertSee(__('Unassigned'))
        ->assertDontSee('name="assigned_to"', false)
        ->assertDontSee(__('Change resolution'));
});

test('HTTP issue creation accepts WCAG 2.2 criteria', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    $criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '2.4.11',
        'level' => 'AA',
        'name_en' => 'Focus Not Obscured (Minimum)',
        'name_sv' => 'Fokus inte dolt (minimum)',
        'description_en' => 'Focused components are not entirely hidden.',
        'description_sv' => 'Fokuserade komponenter är inte helt dolda.',
    ]);

    $this->actingAs($owner)
        ->post(route('accessibility-issues.store', $project), [
            'title' => 'Keyboard focus is obscured',
            'severity' => 'major',
            'difficulty' => 'medium',
            'status' => 'open',
            'wcag_criteria' => [(string) $criterion->id],
        ])
        ->assertRedirect(route('accessibility-projects.show', $project));

    $issue = $project->issues()->firstOrFail();

    expect($issue->wcagCriteria()->whereKey($criterion->id)->exists())->toBeTrue();
});
