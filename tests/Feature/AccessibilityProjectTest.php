<?php

use App\Livewire\CreateAccessibilityPage;
use App\Livewire\CreateAccessibilityProject;
use App\Livewire\EditAccessibilityPage;
use App\Livewire\EditAccessibilityProject;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\ProjectShareLink;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use App\Services\ReportSiteIcon;
use Livewire\Livewire;

test('can view accessibility projects index', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    $response = $this->actingAs($user)->get('/accessibility-projects');

    $response->assertStatus(200);
});

test('nested project pages link to their logical parent pages', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);
    $page = $project->pages()->create(['name' => 'Homepage']);

    $this->actingAs($user)
        ->get(route('accessibility-projects.show', $project))
        ->assertSuccessful()
        ->assertSee('href="'.route('accessibility-projects.index').'"', false)
        ->assertSee(__('Back'));

    $this->actingAs($user)
        ->get(route('accessibility-pages.edit', [$project, $page]))
        ->assertSuccessful()
        ->assertSee('href="'.route('accessibility-pages.show', [$project, $page]).'"', false)
        ->assertSee(__('Cancel'));
});

test('can create accessibility project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    $response = $this->actingAs($user)->post('/accessibility-projects', [
        'name' => 'Test Project',
        'description' => 'Test description',
        'audit_scope' => 'Test account and payment journeys.',
        'scope_limitations' => 'Desktop browsers only.',
        'target_wcag_level' => 'AA',
        'target_wcag_version' => '2.2',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accessibility_projects', [
        'name' => 'Test Project',
        'team_id' => $team->id,
        'target_wcag_version' => '2.2',
        'audit_scope' => 'Test account and payment journeys.',
        'scope_limitations' => 'Desktop browsers only.',
    ]);
});

test('page scope uses yes and no radio options on create and edit forms', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityPage::class, ['project' => $project])
        ->assertSee(__('Yes'))
        ->assertSee(__('No'))
        ->assertSeeHtml('data-flux-radio-group')
        ->assertSeeHtml('value="in_scope"')
        ->assertSeeHtml('value="out_of_scope"')
        ->assertSeeHtml('wire:model.live="scope"')
        ->assertDontSee(__('Reason for exclusion'))
        ->assertDontSeeHtml('<select wire:model="scope"');

    $page = $project->pages()->create([
        'name' => 'Out of scope service',
        'resource_type' => 'service',
        'scope' => 'out_of_scope',
        'scope_exclusion_reason' => 'This service is being replaced.',
    ]);

    $this->actingAs($user)
        ->get(route('accessibility-pages.edit', [$project, $page]))
        ->assertSuccessful()
        ->assertSeeLivewire(EditAccessibilityPage::class)
        ->assertSeeHtml('data-flux-editor')
        ->assertSeeHtml('wire:model="description"')
        ->assertSee(__('Yes'))
        ->assertSee(__('No'))
        ->assertSeeHtml('data-flux-radio-group')
        ->assertSeeHtml('wire:model.live="scope"')
        ->assertSeeHtml('value="in_scope"')
        ->assertSeeHtml('value="out_of_scope"')
        ->assertSeeHtml('checked')
        ->assertSee(__('Reason for exclusion'))
        ->assertSee('This service is being replaced.')
        ->assertDontSeeHtml('<select wire:model="scope"');
});

test('project scope is stored separately from its background description', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityProject::class)
        ->set('name', 'Scope audit')
        ->set('description', '<p>Project background</p>')
        ->set('audit_scope', 'Test account creation and payment journeys.')
        ->set('scope_limitations', 'Only desktop Chrome is available.')
        ->call('submit')
        ->assertRedirect();

    $project = AccessibilityProject::query()->where('name', 'Scope audit')->firstOrFail();
    expect($project->description)->toBe('<p>Project background</p>')
        ->and($project->audit_scope)->toBe('Test account creation and payment journeys.')
        ->and($project->scope_limitations)->toBe('Only desktop Chrome is available.');

    Livewire::actingAs($user)
        ->test(EditAccessibilityProject::class, ['project' => $project])
        ->assertSet('audit_scope', 'Test account creation and payment journeys.')
        ->set('audit_scope', 'Test the account creation journey.')
        ->call('update')
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($project->refresh()->description)->toBe('<p>Project background</p>')
        ->and($project->audit_scope)->toBe('Test the account creation journey.');
});

test('excluding a page requires a reason and removing it from scope clears the reason', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityPage::class, ['project' => $project])
        ->set('name', 'Legacy service')
        ->set('scope', 'out_of_scope')
        ->call('submit')
        ->assertHasErrors(['scopeExclusionReason' => 'required_if']);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityPage::class, ['project' => $project])
        ->set('name', 'Legacy service')
        ->set('scope', 'out_of_scope')
        ->set('scopeExclusionReason', 'This service has been retired.')
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    $page = $project->pages()->where('name', 'Legacy service')->firstOrFail();
    expect($page->scope_exclusion_reason)->toBe('This service has been retired.');

    Livewire::actingAs($user)
        ->test(EditAccessibilityPage::class, ['project' => $project, 'page' => $page])
        ->set('scope', 'in_scope')
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($page->refresh()->scope_exclusion_reason)->toBeNull();

    $controllerPageData = [
        'name' => 'Legacy account',
        'scope' => 'out_of_scope',
    ];
    $this->actingAs($user)
        ->post(route('accessibility-pages.store', $project), $controllerPageData)
        ->assertSessionHasErrors('scope_exclusion_reason');

    $this->actingAs($user)
        ->post(route('accessibility-pages.store', $project), [
            ...$controllerPageData,
            'scope_exclusion_reason' => 'This resource is no longer maintained.',
        ])
        ->assertRedirect(route('accessibility-projects.show', $project));

    $controllerPage = $project->pages()->where('name', 'Legacy account')->firstOrFail();
    $this->actingAs($user)
        ->put(route('accessibility-pages.update', [$project, $controllerPage]), [
            'name' => 'Legacy account',
            'scope' => 'in_scope',
        ])
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($controllerPage->refresh()->scope_exclusion_reason)->toBeNull();
});

test('project editor can update page description with Flux rich text', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);
    $page = $project->pages()->create([
        'name' => 'Homepage',
        'url' => 'https://example.com/',
        'description' => '<p>Original description</p>',
        'scope' => 'in_scope',
    ]);

    Livewire::actingAs($user)
        ->test(EditAccessibilityPage::class, ['project' => $project, 'page' => $page])
        ->assertSet('description', '<p>Original description</p>')
        ->assertSeeHtml('data-flux-editor')
        ->set('description', '<p>Updated <strong>description</strong></p>')
        ->set('url', 'not-a-valid-url')
        ->call('submit')
        ->assertHasErrors(['url' => 'url'])
        ->assertSet('description', '<p>Updated <strong>description</strong></p>');

    expect($page->refresh()->description)->toBe('<p>Original description</p>');

    Livewire::actingAs($user)
        ->test(EditAccessibilityPage::class, ['project' => $project, 'page' => $page])
        ->set('description', '<p>Updated <strong>description</strong></p>')
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($page->refresh()->description)->toBe('<p>Updated <strong>description</strong></p>');

    $this->actingAs($user)
        ->get(route('accessibility-pages.show', [$project, $page]))
        ->assertSuccessful()
        ->assertSee('<strong>description</strong>', false)
        ->assertDontSee('&lt;p&gt;Updated', false);
});

test('project viewers cannot access page editing form', function () {
    $team = Team::factory()->create();
    $viewer = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
    $page = $project->pages()->create(['name' => 'Homepage']);

    $this->actingAs($viewer)
        ->get(route('accessibility-pages.edit', [$project, $page]))
        ->assertForbidden();
});

test('project form explains validation errors and keeps entered content', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityProject::class)
        ->set('name', '')
        ->set('description', 'Draft audit covering the public website.')
        ->call('submit')
        ->assertHasErrors(['name' => 'required'])
        ->assertSet('description', 'Draft audit covering the public website.')
        ->assertSee('projektnamn måste fyllas i.');
});

test('project forms create and update WCAG 2.2 target versions', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityProject::class)
        ->assertSet('target_wcag_version', '2.1')
        ->assertSee('WCAG 2.2')
        ->set('name', 'WCAG 2.2 audit')
        ->set('target_wcag_version', '2.2')
        ->call('submit')
        ->assertRedirect();

    $project = AccessibilityProject::query()->where('name', 'WCAG 2.2 audit')->firstOrFail();
    expect($project->target_wcag_version)->toBe('2.2');

    Livewire::actingAs($user)
        ->test(EditAccessibilityProject::class, ['project' => $project])
        ->assertSet('target_wcag_version', '2.2')
        ->set('target_wcag_version', '2.1')
        ->call('update')
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($project->refresh()->target_wcag_version)->toBe('2.1');
});

test('can update project wcag level from edit form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'target_wcag_level' => 'AA',
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    Livewire::actingAs($user)
        ->test(EditAccessibilityProject::class, ['project' => $project])
        ->set('target_wcag_level', 'AAA')
        ->set('target_wcag_version', '2.2')
        ->call('update')
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($project->refresh()->target_wcag_level)->toBe('AAA')
        ->and($project->target_wcag_version)->toBe('2.2');
});

test('project primary website can be edited without changing page URLs', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $page = $project->pages()->create(['name' => 'Other service', 'url' => 'https://service.example.org/path', 'scope' => 'in_scope']);

    Livewire::actingAs($user)->test(EditAccessibilityProject::class, ['project' => $project])
        ->assertSee(__('Primary website'))
        ->set('report_site_url', 'https://example.com/some/page')
        ->call('update')
        ->assertHasNoErrors();

    expect($project->fresh()->report_site_url)->toBe('https://example.com')
        ->and($page->fresh()->url)->toBe('https://service.example.org/path')
        ->and($project->fresh()->load('pages')->domains)->toContain('example.com', 'service.example.org')
        ->and(ReportSiteIcon::sitesFor($project->fresh()))->toHaveKey('https://example.com');

    $this->actingAs($user)->get(route('accessibility-reports.create', $project))
        ->assertSuccessful()
        ->assertSee('value="https://example.com" selected', false);

    Livewire::actingAs($user)->test(EditAccessibilityProject::class, ['project' => $project->fresh()])
        ->set('report_site_url', 'http://localhost')
        ->call('update')
        ->assertHasErrors('report_site_url');
    expect($project->fresh()->report_site_url)->toBe('https://example.com');
});

test('project primary website can be set on creation and removed', function () {
    $user = User::factory()->create();
    $component = Livewire::actingAs($user)->test(CreateAccessibilityProject::class)
        ->assertSee(__('Primary website'))
        ->set('name', 'Website audit')
        ->set('report_site_url', 'https://example.com/')
        ->call('submit')
        ->assertHasNoErrors();
    $project = AccessibilityProject::query()->where('name', 'Website audit')->firstOrFail();
    expect($project->report_site_url)->toBe('https://example.com');

    Livewire::actingAs($user)->test(EditAccessibilityProject::class, ['project' => $project])
        ->set('report_site_url', '')
        ->call('update')
        ->assertHasNoErrors();
    expect($project->fresh()->report_site_url)->toBeNull();
});

test('project HTTP endpoints retain and validate primary website', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $this->actingAs($user)->post(route('accessibility-projects.store'), [
        'name' => 'HTTP project',
        'target_wcag_level' => 'AA',
        'report_site_url' => 'https://example.com/home',
    ])->assertRedirect();
    $project = AccessibilityProject::query()->where('name', 'HTTP project')->firstOrFail();
    expect($project->report_site_url)->toBe('https://example.com');

    $this->actingAs($user)->get(route('accessibility-projects.index', ['search' => 'example.com']))
        ->assertSuccessful()->assertSee('HTTP project');
    $this->actingAs($user)->put(route('accessibility-projects.update', $project), [
        'name' => 'HTTP project',
        'target_wcag_level' => 'AA',
        'status' => 'planning',
        'report_site_url' => 'http://localhost',
    ])->assertSessionHasErrors('report_site_url');
    expect($project->fresh()->report_site_url)->toBe('https://example.com');
});

test('can view project details', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
    ]);
    $page = $project->pages()->create([
        'name' => 'Homepage',
        'url' => 'https://example.com',
        'scope' => 'in_scope',
    ]);
    $issue = $project->issues()->create([
        'page_id' => $page->id,
        'title' => 'Missing alternative text',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $shareLink = ProjectShareLink::create([
        'project_id' => $project->id,
        'created_by' => $user->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertSee($project->name);
    $response->assertSee(route('accessibility-reports.create', $project));
    $response->assertSee(__('Current project preview'));
    $response->assertSee(__('Actions'));
    $response->assertSee('delete-project-modal', false);
    $response->assertSee('delete-page-'.$page->id, false);
    $response->assertSee('delete-issue-'.$issue->id, false);
    $response->assertSee('delete-share-link-'.$shareLink->id, false);
    $response->assertSee(__('Legacy links'));
    $response->assertDontSee('confirm(', false);
});

test('project issue list warns only for pages outside the audit scope', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $inScopePage = $project->pages()->create([
        'name' => 'Included page',
        'url' => 'https://example.com/included',
        'scope' => 'in_scope',
    ]);
    $outOfScopePage = $project->pages()->create([
        'name' => 'Excluded page',
        'url' => 'https://example.com/excluded',
        'scope' => 'out_of_scope',
    ]);
    $description = 'The full issue description stays in expanded details.';
    foreach ([$inScopePage, $outOfScopePage] as $page) {
        $project->issues()->create([
            'page_id' => $page->id,
            'title' => $page->name.' issue',
            'description' => $description,
            'severity' => 'major',
            'difficulty' => 'easy',
            'status' => 'open',
        ]);
    }

    $response = $this->actingAs($user)->get(route('accessibility-projects.show', $project));
    $response->assertSuccessful();

    $content = $response->getContent();
    preg_match_all('/<tr[^>]*data-issue-row[^>]*>(.*?)<\/tr>/s', $content, $rows);
    $warning = __('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.');
    expect($rows[0])->toHaveCount(2)
        ->and(substr_count($content, '<tbody'))->toBe(substr_count($content, '</tbody>'));

    $response->assertSee('data-desc="'.strtolower($description).'"', false)
        ->assertSee('Included page')
        ->assertSee('Excluded page')
        ->assertSee(__('Page'))
        ->assertSee(__('In Scope'))
        ->assertSee(__('Out of Scope'))
        ->assertSee(route('accessibility-pages.show', [$project, $inScopePage]));

    foreach ($rows[1] as $row) {
        preg_match_all('/<td\b[^>]*>(.*?)<\/td>/s', $row, $cells);
        expect(strip_tags(implode($cells[1])))->not->toContain($description);
    }

    $included = collect($rows[0])->first(fn (string $row): bool => str_contains($row, 'Included page issue'));
    $excluded = collect($rows[0])->first(fn (string $row): bool => str_contains($row, 'Excluded page issue'));
    expect($included)->not->toContain($warning)
        ->and($excluded)->toContain($warning);

    $response->assertDontSee('data-issue-details', false)
        ->assertDontSee('toggleIssueDetails', false)
        ->assertSee(route('accessibility-issues.show', [
            $project,
            $project->issues()->where('title', 'Included page issue')->firstOrFail(),
        ]));
});

test('project resolution modal explains required notes and shows validation errors', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $issue = $project->issues()->create([
        'title' => 'Keyboard trap',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $url = route('accessibility-projects.show', $project);
    $this->actingAs($user)->from($url)
        ->followingRedirects()
        ->patch(route('accessibility-issues.resolve', [$project, $issue]), [
            'resolution_issue_id' => $issue->id,
            'resolution_status' => 'fixed',
            'resolution_notes' => '',
        ])
        ->assertSuccessful()
        ->assertSee(__('A resolution note is required when marking an issue fixed or not planned for fixing.'))
        ->assertSee(__('Could not change status. Open the resolution action and correct the highlighted fields.'))
        ->assertSee('resolve-project-issue-'.$issue->id, false);
});

test('renders project description as formatted user content', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'description' => '<p><strong>Important</strong> project details.</p><ol><li>First step</li></ol>',
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}");

    $response
        ->assertSee('leading-[1.75]', false)
        ->assertSee('<strong>Important</strong>', false)
        ->assertSee('<ol>', false)
        ->assertSee('First step');
});

test('project preview renders collapsed Flux issue accordions with linked examples and diagrams', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $page = $project->pages()->create([
        'name' => 'Homepage',
        'url' => 'https://example.test',
        'scope' => 'in_scope',
    ]);
    $issue = $project->issues()->create([
        'page_id' => $page->id,
        'title' => 'Navigation cannot be used with keyboard',
        'summary' => 'Keyboard focus is hidden by navigation.',
        'description' => 'Tab focus disappears behind sticky navigation.',
        'solution_suggestions' => '<p>Replace generic link labels.</p>',
        'mermaid_diagram' => 'flowchart LR; A[Start] --> B[Fix]',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
        'component_area' => 'Navigation',
    ]);
    $legacyIssue = $project->issues()->create([
        'page_id' => $page->id,
        'title' => 'Legacy issue without a summary',
        'description' => 'Existing detailed content is not auto-summarized.',
        'severity' => 'minor',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $criterion = WcagSuccessCriterion::create([
        'standard' => 'WCAG 2.2',
        'number' => '2.4.11',
        'level' => 'AA',
        'name_en' => 'Focus Not Obscured',
        'name_sv' => 'Fokus inte dolt',
        'description_en' => 'Focused components are not entirely hidden.',
        'description_sv' => 'Fokuserade komponenter är inte helt dolda.',
    ]);
    $issue->wcagCriteria()->attach($criterion->id, [
        'failure_type' => 'focus_obscured',
        'comment' => 'Focus is covered by the fixed header.',
        'code_snippet' => 'position: fixed;',
    ]);
    $example = $criterion->examples()->create([
        'title' => 'Keep focus visible',
        'description' => 'Move focused controls clear of sticky content.',
        'code_snippet' => 'flowchart LR; A[Focus] --> B[Visible]',
        'code_language' => 'mermaid',
    ]);
    $issue->examples()->attach($example->id);
    $example->attachments()->create([
        'filename' => 'focus.png',
        'original_filename' => 'focus-example.png',
        'mime_type' => 'image/png',
        'size' => 128,
        'path' => 'wcag-examples/focus.png',
        'alt_text' => 'Visible focus indicator',
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get(route('accessibility-projects.preview', $project));

    expect(substr_count($response->getContent(), 'Tab focus disappears behind sticky navigation.'))->toBe(1);
    expect(substr_count($response->getContent(), 'Existing detailed content is not auto-summarized.'))->toBe(1);

    $response->assertSuccessful()
        ->assertSee('id="issue-'.$issue->id.'"', false)
        ->assertSee('id="issue-'.$legacyIssue->id.'"', false)
        ->assertSee(__('Summary'))
        ->assertSee('Keyboard focus is hidden by navigation.')
        ->assertSee(__('Description'))
        ->assertSee('Tab focus disappears behind sticky navigation.')
        ->assertSee('data-flux-accordion-item', false)
        ->assertSee('data-flux-accordion-content', false)
        ->assertSee('data-print-accordion', false)
        ->assertSee('x-data="{ open: false }"', false)
        ->assertDontSee('x-show="!open"', false)
        ->assertDontSee(__('Show details'))
        ->assertDontSee(__('Hide details'))
        ->assertDontSee(__('Facts'))
        ->assertSee(__('Recommended action'))
        ->assertSee('overflow-hidden rounded-lg border border-zinc-200 bg-white p-0 dark:border-zinc-700 dark:bg-zinc-900', false)
        ->assertDontSee('rounded-md border border-zinc-200 bg-white p-4', false)
        ->assertSee('divide-y divide-zinc-200 dark:divide-zinc-700', false)
        ->assertSee('aria-level="5"', false)
        ->assertSee('aria-level="6"', false)
        ->assertSee(__('Failure type'))
        ->assertSee(__('Level'))
        ->assertSee('Replace generic link labels.')
        ->assertSee('position: fixed;')
        ->assertSee(__('Component Area'))
        ->assertSee('Navigation')
        ->assertDontSee('<details', false)
        ->assertSee('bg-amber-400/25', false)
        ->assertSee('dark:bg-emerald-400/40', false)
        ->assertSee('data-mermaid-source=', false)
        ->assertSee('flowchart LR; A[Focus] --&gt; B[Visible]', false)
        ->assertSee('Keep focus visible')
        ->assertSee('Focus is covered by the fixed header.')
        ->assertSee('focus-example.png')
        ->assertSee('Visible focus indicator')
        ->assertSee(__('Show source code'));
});

test('page creation uses the application layout', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get(route('accessibility-pages.create', $project));

    $response->assertSuccessful()
        ->assertSee(__('Knowledge Base'))
        ->assertSee(__('Page or Service Information'));
});

test('can add page to project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/pages", [
        'name' => 'Homepage',
        'url' => 'https://example.com/',
        'scope' => 'out_of_scope',
        'scope_exclusion_reason' => 'This page is outside the agreed audit sample.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accessibility_pages', [
        'name' => 'Homepage',
        'project_id' => $project->id,
        'scope' => 'out_of_scope',
    ]);
});

test('can add an out of scope page from the livewire form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityPage::class, ['project' => $project])
        ->set('name', 'External service')
        ->set('url', 'https://external.example.com/')
        ->set('scope', 'out_of_scope')
        ->set('scopeExclusionReason', 'This service is owned by another organization.')
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    $this->assertDatabaseHas('accessibility_pages', [
        'project_id' => $project->id,
        'name' => 'External service',
        'scope' => 'out_of_scope',
    ]);
});

test('can add an authenticated service from the livewire form', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    Livewire::actingAs($user)
        ->test(CreateAccessibilityPage::class, ['project' => $project])
        ->set('name', 'Mina sidor')
        ->set('resourceType', 'service')
        ->set('url', 'https://example.com/login')
        ->set('accessContext', 'authenticated')
        ->call('submit')
        ->assertRedirect(route('accessibility-projects.show', $project));

    $this->assertDatabaseHas('accessibility_pages', [
        'project_id' => $project->id,
        'name' => 'Mina sidor',
        'resource_type' => 'service',
        'access_context' => 'authenticated',
    ]);
});

test('project overview links services to access context details', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $page = $project->pages()->create([
        'name' => 'Mina sidor',
        'resource_type' => 'service',
        'access_context' => 'authenticated',
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->get(route('accessibility-projects.show', $project))
        ->assertSuccessful()
        ->assertSee(__('Service'))
        ->assertSee(route('accessibility-pages.show', [$project, $page]))
        ->assertDontSee(__('Authenticated'));

    $this->get(route('accessibility-pages.show', [$project, $page]))
        ->assertSuccessful()
        ->assertSee(__('Authenticated'));
});

test('can update page scope', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $page = $project->pages()->create([
        'name' => 'Homepage',
        'url' => 'https://example.com/',
        'scope' => 'in_scope',
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->put(route('accessibility-pages.update', [$project, $page]), [
        'name' => $page->name,
        'url' => $page->url,
        'description' => $page->description,
        'scope' => 'out_of_scope',
        'scope_exclusion_reason' => 'This page is outside the agreed audit sample.',
    ]);

    $response->assertRedirect();
    expect($page->refresh()->scope)->toBe('out_of_scope');
});

test('can report issue', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/issues", [
        'title' => 'Missing alt text',
        'description' => 'Images lack alt text',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accessibility_issues', [
        'title' => 'Missing alt text',
        'project_id' => $project->id,
    ]);
});
