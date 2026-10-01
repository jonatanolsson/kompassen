<?php

use App\Livewire\CreateAccessibilityPage;
use App\Livewire\CreateAccessibilityProject;
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

test('can create accessibility project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);

    $response = $this->actingAs($user)->post('/accessibility-projects', [
        'name' => 'Test Project',
        'description' => 'Test description',
        'target_wcag_level' => 'AA',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('accessibility_projects', [
        'name' => 'Test Project',
        'team_id' => $team->id,
    ]);
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
        ->call('update')
        ->assertRedirect(route('accessibility-projects.show', $project));

    expect($project->refresh()->target_wcag_level)->toBe('AAA');
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
    foreach ([$inScopePage, $outOfScopePage] as $page) {
        $project->issues()->create([
            'page_id' => $page->id,
            'title' => $page->name.' issue',
            'severity' => 'major',
            'difficulty' => 'easy',
            'status' => 'open',
        ]);
    }

    $response = $this->actingAs($user)->get(route('accessibility-projects.show', $project));
    $response->assertSuccessful();

    preg_match_all('/<tr[^>]*data-issue-row[^>]*>.*?<\/tr>/s', $response->getContent(), $rows);
    $warning = __('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.');
    expect($rows[0])->toHaveCount(2);

    $included = collect($rows[0])->first(fn (string $row): bool => str_contains($row, 'Included page issue'));
    $excluded = collect($rows[0])->first(fn (string $row): bool => str_contains($row, 'Excluded page issue'));
    expect($included)->not->toContain($warning)
        ->and($excluded)->toContain($warning);
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
        ->assertSee('class="user-content"', false)
        ->assertSee('<strong>Important</strong>', false)
        ->assertSee('<ol>', false)
        ->assertSee('First step');
});

test('project preview expands issue details with linked examples and diagrams', function () {
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
        'description' => 'Tab focus disappears behind sticky navigation.',
        'mermaid_diagram' => 'flowchart LR; A[Start] --> B[Fix]',
        'severity' => 'major',
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

    $response->assertSuccessful()
        ->assertSee('id="issue-'.$issue->id.'"', false)
        ->assertSee('data-print-expand', false)
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

test('project details show service type and access context', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $project->pages()->create([
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
