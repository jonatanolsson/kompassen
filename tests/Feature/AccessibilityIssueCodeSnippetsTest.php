<?php

use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\EditAccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use Livewire\Livewire;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->user = User::factory()->create(['team_id' => $this->team->id]);
    $this->project = AccessibilityProject::factory()->create(['team_id' => $this->team->id]);

    ProjectMember::create([
        'project_id' => $this->project->id,
        'user_id' => $this->user->id,
        'role' => 'owner',
    ]);
});

test('issue-specific failing and corrected snippets coexist with WCAG examples', function () {
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
    $example = $criterion->examples()->create([
        'title' => 'WCAG image example',
        'code_language' => 'html',
        'code_snippet' => '<img src="example.jpg" alt="Example">',
    ]);
    $problemCode = '<img src="product.jpg">';
    $correctedCode = '<img src="product.jpg" alt="Product description">';

    Livewire::actingAs($this->user)
        ->test(CreateAccessibilityIssue::class, ['project' => $this->project])
        ->assertSee(__('Code causing the issue'))
        ->assertSee(__('Suggested corrected code'))
        ->set('title', 'Image lacks alternative text')
        ->set('problem_code_snippet', $problemCode)
        ->set('corrected_code_snippet', $correctedCode)
        ->set('wcag_criteria.0', (string) $criterion->id)
        ->set('example_ids.0', (string) $example->id)
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $this->project));

    $issue = $this->project->issues()->firstOrFail();

    expect($issue->problem_code_snippet)->toBe($problemCode)
        ->and($issue->corrected_code_snippet)->toBe($correctedCode)
        ->and($issue->examples->pluck('id')->all())->toBe([(string) $example->id])
        ->and($issue->examples->first()->code_snippet)->toBe($example->code_snippet);

    Livewire::actingAs($this->user)
        ->test(EditAccessibilityIssue::class, ['project' => $this->project, 'issue' => $issue])
        ->assertSet('problem_code_snippet', $problemCode)
        ->assertSet('corrected_code_snippet', $correctedCode)
        ->set('problem_code_snippet', '<img src="product.jpg" alt="">')
        ->set('corrected_code_snippet', '<img src="product.jpg" alt="A product">')
        ->call('update')
        ->assertRedirect(route('accessibility-issues.edit', [$this->project, $issue]));

    expect($issue->refresh()->problem_code_snippet)->toBe('<img src="product.jpg" alt="">')
        ->and($issue->corrected_code_snippet)->toBe('<img src="product.jpg" alt="A product">')
        ->and($issue->examples->pluck('id')->all())->toBe([(string) $example->id]);
});

test('HTTP issue creation stores issue-specific code snippets', function () {
    $problemCode = '<button>Click</button>';
    $correctedCode = '<button type="button">Click</button>';

    $this->actingAs($this->user)
        ->post(route('accessibility-issues.store', $this->project), [
            'title' => 'Button needs semantics',
            'severity' => 'major',
            'difficulty' => 'easy',
            'problem_code_snippet' => $problemCode,
            'corrected_code_snippet' => $correctedCode,
        ])
        ->assertRedirect(route('accessibility-projects.show', $this->project));

    $issue = $this->project->issues()->firstOrFail();

    expect($issue->problem_code_snippet)->toBe($problemCode)
        ->and($issue->corrected_code_snippet)->toBe($correctedCode);
});

test('issue detail and shared issue view escape issue-specific code', function () {
    $problemCode = '<script>alert("unsafe")</script>';
    $correctedCode = '<button type="button">Continue</button>';
    $issue = $this->project->issues()->create([
        'title' => 'Unsafe button',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
        'problem_code_snippet' => $problemCode,
        'corrected_code_snippet' => $correctedCode,
    ]);

    $this->actingAs($this->user)
        ->get(route('accessibility-issues.show', [$this->project, $issue]))
        ->assertSuccessful()
        ->assertSee(__('Issue-specific code'))
        ->assertSee(__('Estimated remediation effort'))
        ->assertSee('&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;', false)
        ->assertSee('&lt;button type=&quot;button&quot;&gt;Continue&lt;/button&gt;', false)
        ->assertDontSee('<script>alert', false);

    $sharedHtml = view('components.shared-project.issue-accordion', ['issue' => $issue])->render();

    expect($sharedHtml)
        ->toContain(__('Issue-specific code'))
        ->toContain(__('Estimated remediation effort'))
        ->toContain('&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;')
        ->not->toContain('<script>alert');
});

test('generated reports include both issue snippets and linked WCAG example code', function () {
    $criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternatives are provided.',
        'description_sv' => 'Textalternativ tillhandahålls.',
    ]);
    $example = $criterion->examples()->create([
        'title' => 'WCAG example snippet',
        'code_language' => 'html',
        'code_snippet' => '<img src="example.jpg" alt="Example">',
    ]);
    $issue = $this->project->issues()->create([
        'title' => 'Missing image alternative',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
        'problem_code_snippet' => '<img src="problem.jpg">',
        'corrected_code_snippet' => '<img src="problem.jpg" alt="Description">',
    ]);
    $issue->examples()->attach($example);

    $this->actingAs($this->user)
        ->post(route('accessibility-reports.store', $this->project), [
            'title' => 'Code examples report',
            'options' => ['code' => true, 'examples' => true],
        ])
        ->assertRedirect();

    $report = $this->project->reports()->where('title', 'Code examples report')->firstOrFail();

    expect($report->html_content)
        ->toContain(__('Code causing the issue'))
        ->toContain(__('Suggested corrected code'))
        ->toContain('&lt;img src=&quot;problem.jpg&quot;&gt;')
        ->toContain('&lt;img src=&quot;problem.jpg&quot; alt=&quot;Description&quot;&gt;')
        ->toContain('&lt;img src=&quot;example.jpg&quot; alt=&quot;Example&quot;&gt;');

    $this->actingAs($this->user)
        ->post(route('accessibility-reports.store', $this->project), [
            'title' => 'Without code examples',
            'options' => ['code' => false],
        ])
        ->assertRedirect();

    $reportWithoutCode = $this->project->reports()->where('title', 'Without code examples')->firstOrFail();

    expect($reportWithoutCode->html_content)
        ->not->toContain(__('Code causing the issue'))
        ->not->toContain('&lt;img src=&quot;problem.jpg&quot;&gt;')
        ->not->toContain('&lt;img src=&quot;example.jpg&quot; alt=&quot;Example&quot;&gt;');
});

test('issue snippet fields reject code longer than validation limit', function () {
    $issue = $this->project->issues()->create([
        'title' => 'Long code issue',
        'description' => 'A sample issue with code.',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $oversizedSnippet = str_repeat('x', 20001);

    Livewire::actingAs($this->user)
        ->test(CreateAccessibilityIssue::class, ['project' => $this->project])
        ->set('title', 'Long code')
        ->set('problem_code_snippet', $oversizedSnippet)
        ->call('submit')
        ->assertHasErrors('problem_code_snippet');

    Livewire::actingAs($this->user)
        ->test(EditAccessibilityIssue::class, ['project' => $this->project, 'issue' => $issue])
        ->set('corrected_code_snippet', $oversizedSnippet)
        ->call('update')
        ->assertHasErrors('corrected_code_snippet');
});
