<?php

use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\EditAccessibilityIssue;
use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use App\Services\IssueDiagramSvg;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

const ISSUE_DIAGRAM_SOURCE = "flowchart LR\n    A[Issue] --> B[Impact]";

function issueDiagramSvg(string $inner = ''): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60" width="200" height="60" role="graphics-document document" aria-roledescription="flowchart-v2">'
        .'<defs><marker id="m-point" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="8" markerHeight="8" orient="auto"><path d="M 0 0 L 10 5 L 0 10 z" fill="rgb(71, 85, 105)"/></marker></defs>'
        .'<!-- rendered by Mermaid -->'
        .'<g transform="translate(10, 10)"><rect x="0" y="0" width="60" height="40" rx="4" fill="rgb(219, 234, 254)" stroke="#2563eb" stroke-width="1px"/>'
        .'<text x="30" y="24" text-anchor="middle" font-family="Figtree, sans-serif" font-size="16px"><tspan>Issue</tspan></text></g>'
        .'<path d="M70,30L130,30" stroke="rgb(71, 85, 105)" fill="none" marker-end="url(#m-point)"/>'
        .$inner
        .'</svg>';
}

function issueContentProject(): array
{
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    return [$user, $project];
}

function issueContentIssue(AccessibilityProject $project, array $attributes = []): AccessibilityIssue
{
    return $project->issues()->create(array_merge([
        'title' => 'Keyboard trap',
        'description' => 'Focus cannot leave the dialog.',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ], $attributes));
}

test('issue can be created with impact and a rendered Mermaid SVG', function () {
    [$user, $project] = issueContentProject();

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Keyboard trap')
        ->set('impact', '<p>Keyboard users cannot continue.</p>')
        ->set('mermaid_diagram', ISSUE_DIAGRAM_SOURCE)
        ->set('mermaid_svg', issueDiagramSvg())
        ->set('mermaid_source_hash', IssueDiagramSvg::hashSource(ISSUE_DIAGRAM_SOURCE))
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('accessibility-projects.show', $project));

    $issue = $project->issues()->firstOrFail();

    expect($issue->impact)->toBe('<p>Keyboard users cannot continue.</p>')
        ->and($issue->mermaid_source_hash)->toBe(hash('sha256', ISSUE_DIAGRAM_SOURCE))
        ->and($issue->mermaid_svg)->toStartWith('<svg')
        ->and($issue->mermaid_svg)->toContain('<tspan>Issue</tspan>')
        ->and($issue->mermaid_svg)->not->toContain('<!--');
});

test('blank impact and diagram are stored as null', function () {
    [$user, $project] = issueContentProject();

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'No diagram')
        ->set('impact', '<p></p>')
        ->call('submit')
        ->assertHasNoErrors();

    $issue = $project->issues()->firstOrFail();

    expect($issue->impact)->toBeNull()
        ->and($issue->mermaid_svg)->toBeNull()
        ->and($issue->mermaid_source_hash)->toBeNull();
});

test('mermaid source without a rendered SVG fails with an actionable error', function () {
    [$user, $project] = issueContentProject();

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Keyboard trap')
        ->set('mermaid_diagram', ISSUE_DIAGRAM_SOURCE)
        ->call('submit')
        ->assertHasErrors(['mermaid_diagram']);

    expect($project->issues()->count())->toBe(0);
});

test('mermaid SVG rendered from different source is rejected', function () {
    [$user, $project] = issueContentProject();

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Keyboard trap')
        ->set('mermaid_diagram', ISSUE_DIAGRAM_SOURCE)
        ->set('mermaid_svg', issueDiagramSvg())
        ->set('mermaid_source_hash', IssueDiagramSvg::hashSource('flowchart LR; X --> Y'))
        ->call('submit')
        ->assertHasErrors(['mermaid_diagram']);

    expect($project->issues()->count())->toBe(0);
});

test('unsafe or unsupported SVG content is rejected', function (string $svg) {
    [$user, $project] = issueContentProject();

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Keyboard trap')
        ->set('mermaid_diagram', ISSUE_DIAGRAM_SOURCE)
        ->set('mermaid_svg', $svg)
        ->set('mermaid_source_hash', IssueDiagramSvg::hashSource(ISSUE_DIAGRAM_SOURCE))
        ->call('submit')
        ->assertHasErrors(['mermaid_diagram']);

    expect($project->issues()->count())->toBe(0);
})->with([
    'script element' => fn () => issueDiagramSvg('<script>alert(1)</script>'),
    'style element' => fn () => issueDiagramSvg('<style>rect{fill:red}</style>'),
    'foreignObject' => fn () => issueDiagramSvg('<foreignObject width="10" height="10"><div xmlns="http://www.w3.org/1999/xhtml">x</div></foreignObject>'),
    'style attribute' => fn () => issueDiagramSvg('<rect width="1" height="1" style="fill:red"/>'),
    'class attribute' => fn () => issueDiagramSvg('<rect width="1" height="1" class="node"/>'),
    'event handler' => fn () => issueDiagramSvg('<rect width="1" height="1" onclick="alert(1)"/>'),
    'image element' => fn () => issueDiagramSvg('<image href="https://example.com/x.png" width="1" height="1"/>'),
    'link element' => fn () => issueDiagramSvg('<a href="javascript:alert(1)"><text>x</text></a>'),
    'xlink href' => fn () => str_replace('<svg ', '<svg xmlns:xlink="http://www.w3.org/1999/xlink" ', issueDiagramSvg('<use xlink:href="#m-point"/>')),
    'external paint reference' => fn () => issueDiagramSvg('<rect width="1" height="1" fill="url(https://example.com/x.svg#p)"/>'),
    'dangling local reference' => fn () => issueDiagramSvg('<rect width="1" height="1" fill="url(#missing)"/>'),
    'foreign namespace element' => fn () => issueDiagramSvg('<x:rect xmlns:x="urn:evil" width="1"/>'),
    'non-svg root' => fn () => '<html xmlns="http://www.w3.org/1999/xhtml"><body/></html>',
    'svg without namespace' => fn () => '<svg viewBox="0 0 1 1"><rect width="1" height="1"/></svg>',
    'doctype with entity' => fn () => '<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]>'.issueDiagramSvg('<text>&x;</text>'),
    'processing instruction' => fn () => '<?xml-stylesheet href="https://example.com/x.css"?>'.issueDiagramSvg(),
    'css in transform' => fn () => issueDiagramSvg('<g transform="translate(1,1);background:url(x)"/>'),
    'invalid xml' => fn () => '<svg xmlns="http://www.w3.org/2000/svg"><rect></svg>',
]);

test('edit keeps diagram when source is unchanged and requires rerender when source changes', function () {
    [$user, $project] = issueContentProject();
    $issue = issueContentIssue($project, [
        'mermaid_diagram' => ISSUE_DIAGRAM_SOURCE,
        'mermaid_svg' => issueDiagramSvg(),
        'mermaid_source_hash' => IssueDiagramSvg::hashSource(ISSUE_DIAGRAM_SOURCE),
    ]);

    Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->assertSet('mermaid_source_hash', IssueDiagramSvg::hashSource(ISSUE_DIAGRAM_SOURCE))
        ->set('impact', '<p>Blocks checkout.</p>')
        ->call('update')
        ->assertHasNoErrors();

    expect($issue->refresh()->impact)->toBe('<p>Blocks checkout.</p>')
        ->and($issue->mermaid_svg)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->set('mermaid_diagram', "flowchart TD\n    A --> B")
        ->assertSet('mermaid_svg', null)
        ->assertSet('mermaid_source_hash', null)
        ->call('update')
        ->assertHasErrors(['mermaid_diagram']);

    expect($issue->refresh()->mermaid_diagram)->toBe(ISSUE_DIAGRAM_SOURCE);
});

test('clearing mermaid source on edit removes stored SVG and hash', function () {
    [$user, $project] = issueContentProject();
    $issue = issueContentIssue($project, [
        'mermaid_diagram' => ISSUE_DIAGRAM_SOURCE,
        'mermaid_svg' => issueDiagramSvg(),
        'mermaid_source_hash' => IssueDiagramSvg::hashSource(ISSUE_DIAGRAM_SOURCE),
    ]);

    Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->set('mermaid_diagram', '')
        ->call('update')
        ->assertHasNoErrors();

    expect($issue->refresh()->mermaid_svg)->toBeNull()
        ->and($issue->mermaid_source_hash)->toBeNull();
});

test('changing mermaid source outside the forms clears the stale SVG', function () {
    [, $project] = issueContentProject();
    $issue = issueContentIssue($project, [
        'mermaid_diagram' => ISSUE_DIAGRAM_SOURCE,
        'mermaid_svg' => issueDiagramSvg(),
        'mermaid_source_hash' => IssueDiagramSvg::hashSource(ISSUE_DIAGRAM_SOURCE),
    ]);

    expect($issue->mermaid_svg)->not->toBeNull();

    $issue->update(['mermaid_diagram' => 'flowchart LR; X --> Y']);

    expect($issue->refresh()->mermaid_svg)->toBeNull()
        ->and($issue->mermaid_source_hash)->toBeNull();
});

test('assertCurrent returns sanitized SVG or throws actionable validation errors', function () {
    [, $project] = issueContentProject();

    $withoutDiagram = issueContentIssue($project);
    $current = issueContentIssue($project, [
        'mermaid_diagram' => "\r\n".ISSUE_DIAGRAM_SOURCE."\r\n",
        'mermaid_svg' => issueDiagramSvg(),
        'mermaid_source_hash' => IssueDiagramSvg::hashSource(ISSUE_DIAGRAM_SOURCE),
    ]);
    $missing = issueContentIssue($project, ['title' => 'Unrendered', 'mermaid_diagram' => ISSUE_DIAGRAM_SOURCE]);

    expect(IssueDiagramSvg::assertCurrent($withoutDiagram))->toBeNull()
        ->and(IssueDiagramSvg::assertCurrent($current))->toContain('<tspan>Issue</tspan>');

    expect(fn () => IssueDiagramSvg::assertCurrent($missing))
        ->toThrow(ValidationException::class, 'Unrendered');

    AccessibilityIssue::withoutEvents(fn () => $current->forceFill([
        'mermaid_svg' => issueDiagramSvg('<script>alert(1)</script>'),
    ])->saveQuietly());

    expect(fn () => IssueDiagramSvg::assertCurrent($current->refresh()))
        ->toThrow(ValidationException::class);
});

function issueContentCriterion(): WcagSuccessCriterion
{
    return WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '2.1.2',
        'level' => 'A',
        'name_en' => 'No Keyboard Trap',
        'name_sv' => 'Ingen tangentbordsfälla',
        'description_en' => 'Focus can be moved away.',
        'description_sv' => 'Fokus kan flyttas bort.',
    ]);
}

test('issue forms only list and accept global or own-team examples', function () {
    [$user, $project] = issueContentProject();
    $criterion = issueContentCriterion();
    $global = $criterion->examples()->create(['title' => 'Global trap example']);
    $own = $criterion->examples()->create(['title' => 'Own team example', 'team_id' => $user->team_id]);
    $foreign = $criterion->examples()->create(['title' => 'Foreign team example', 'team_id' => Team::factory()->create()->id]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Keyboard trap')
        ->set('wcag_criteria', [(string) $criterion->id])
        ->assertSee('Global trap example')
        ->assertSee('Own team example')
        ->assertDontSee('Foreign team example')
        ->set('example_ids', [(string) $foreign->id])
        ->call('submit')
        ->assertHasErrors(['example_ids.0']);

    expect($project->issues()->count())->toBe(0);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityIssue::class, ['project' => $project])
        ->set('title', 'Keyboard trap')
        ->set('wcag_criteria', [(string) $criterion->id])
        ->set('example_ids', [(string) $global->id, (string) $own->id])
        ->call('submit')
        ->assertHasNoErrors();

    expect($project->issues()->firstOrFail()->examples->pluck('id')->all())
        ->toBe([(string) $global->id, (string) $own->id]);
});

test('edit hides and drops existing cross-team example links', function () {
    [$user, $project] = issueContentProject();
    $criterion = issueContentCriterion();
    $global = $criterion->examples()->create(['title' => 'Global trap example']);
    $foreign = $criterion->examples()->create(['title' => 'Foreign team example', 'team_id' => Team::factory()->create()->id]);
    $issue = issueContentIssue($project);
    $issue->wcagCriteria()->attach($criterion);
    $issue->examples()->attach([$global->id, $foreign->id]);

    $component = Livewire::actingAs($user)
        ->test(EditAccessibilityIssue::class, ['project' => $project, 'issue' => $issue])
        ->assertSet('example_ids', [(string) $global->id])
        ->assertDontSee('Foreign team example');

    $component->set('example_ids', [(string) $global->id, (string) $foreign->id])
        ->call('update')
        ->assertHasErrors(['example_ids.1']);

    $component->set('example_ids', [(string) $global->id])
        ->call('update')
        ->assertHasNoErrors();

    expect($issue->refresh()->examples->pluck('id')->all())->toBe([(string) $global->id]);
});
