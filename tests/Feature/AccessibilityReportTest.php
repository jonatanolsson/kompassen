<?php

use App\Livewire\AppSettings;
use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\AppSetting;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use App\Services\IssueDiagramSvg;
use App\Services\ReportSiteIcon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('can view reports index', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports");

    $response->assertStatus(200);
    $response->assertSee(__('Reports for :name', ['name' => $project->name]));
});

test('report deletion requires an accessible confirmation modal', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Initial audit',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Initial audit</h1>',
        'created_by' => $user->id,
    ]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->get(route('accessibility-reports.index', $project))
        ->assertSuccessful()
        ->assertSee('delete-report-'.$report->id, false)
        ->assertSee(__('Delete this report?'))
        ->assertSee(__('This will permanently delete the report.'))
        ->assertDontSee('confirm(', false);
});

test('report title validation explains how to correct the error and preserves scope', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $this->actingAs($user)
        ->from(route('accessibility-reports.create', $project))
        ->followingRedirects()
        ->post(route('accessibility-reports.store', $project), [
            'title' => '',
            'scope' => 'Homepage and account settings',
        ])
        ->assertSuccessful()
        ->assertSee(__('Report title is required.'))
        ->assertSee('Homepage and account settings');
});

test('can view report creation form', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports/create");

    $response->assertStatus(200);
    $response->assertSee(__('Generate Report'));
    $response->assertSee('<html', false);
    $response->assertSee('data-flux-sidebar', false);
    $response->assertSee('/livewire/livewire.js', false);
});

test('can generate report with issues', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    // Create some issues
    $project->issues()->createMany([
        [
            'title' => 'Missing alt text',
            'description' => 'Images lack alt text',
            'severity' => 'critical',
            'difficulty' => 'easy',
            'status' => 'open',
        ],
        [
            'title' => 'Low contrast',
            'description' => 'Text has low contrast',
            'severity' => 'major',
            'difficulty' => 'medium',
            'status' => 'open',
        ],
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Initial Audit Report',
        'scope' => 'Tested all pages',
    ]);

    $response->assertRedirect();

    // Check that the report exists
    expect(AccessibilityReport::where([
        'project_id' => $project->id,
        'title' => 'Initial Audit Report',
        'scope' => 'Tested all pages',
        'total_issues' => 2,
        'critical_count' => 1,
        'major_count' => 1,
    ])->exists())->toBeTrue();
});

test('updating a report creates a new immutable version with saved configuration', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Audit',
        'options' => ['images' => false, 'evidence' => true],
    ])->assertRedirect();

    $original = $project->reports()->firstOrFail();
    $originalHtml = $original->html_content;
    expect($original->series_id)->toBe($original->id);
    $project->issues()->create([
        'title' => 'New finding',
        'description' => 'Missing label',
        'severity' => 'critical',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $response = $this->actingAs($user)->post(route('accessibility-reports.regenerate', [$project, $original]));
    $updated = $project->reports()->where('version', 2)->firstOrFail();

    $response->assertRedirect(route('accessibility-reports.show', [$project, $updated]));
    expect($original->fresh()->html_content)->toBe($originalHtml)
        ->and($original->fresh()->total_issues)->toBe(0)
        ->and($updated->total_issues)->toBe(1)
        ->and($updated->previous_version_id)->toBe($original->id)
        ->and($updated->series_id)->toBe($original->id)
        ->and($updated->report_options['images'])->toBeFalse()
        ->and($project->fresh()->report_options['images'])->toBeFalse();

    $versionPage = $this->actingAs($user)->get(route('accessibility-reports.show', [$project, $updated]));
    $versionPage
        ->assertSuccessful()
        ->assertSee('aria-label="'.__('Report versions').'"', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee(route('accessibility-reports.show', [$project, $original]), false)
        ->assertSee(__('Version :version', ['version' => 1]))
        ->assertSee(__('Version :version', ['version' => 2]));
    preg_match('/<nav\b[^>]*aria-label="'.preg_quote(__('Report versions'), '/').'"[^>]*>(.*?)<\/nav>/s', $versionPage->getContent(), $historyMenu);
    expect($historyMenu[1] ?? '')->toContain('data-flux-icon')
        ->toContain('data-flux-menu');

    $this->actingAs($user)->post(route('accessibility-reports.regenerate', [$project, $original]))->assertRedirect();
    $third = $project->reports()->where('version', 3)->firstOrFail();
    expect($third->previous_version_id)->toBe($updated->id)
        ->and($project->reports()->count())->toBe(3);
});

test('report content options reject unrecognized fields and keep project defaults intact', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Audit',
        'options' => ['unknown' => true],
    ])->assertSessionHasErrors('options');

    expect($project->fresh()->report_options)->toBeNull()
        ->and($project->reports()->count())->toBe(0);
});

test('previous report design remains saved and offers a new version', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Earlier audit',
        'target_wcag_level' => 'AA',
        'html_content' => '<main data-report-template="3">Saved earlier report</main>',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)->get(route('accessibility-reports.show', [$project, $report]))
        ->assertSuccessful()
        ->assertSee(__('This report uses an older template'))
        ->assertSee(__('Create new version'))
        ->assertSee('bg-white dark:border-zinc-700 dark:bg-white', false)
        ->assertSee('title="'.__('Saved report preview').'"', false);
    expect($report->fresh()->html_content)->toBe('<main data-report-template="3">Saved earlier report</main>');
});

test('report creation form uses project defaults and unchecked choices remain disabled', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'report_options' => ['images' => false, 'evidence' => true],
    ]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);

    $this->actingAs($user)->get(route('accessibility-reports.create', $project))
        ->assertSuccessful()
        ->assertSee(__('Advanced report content'))
        ->assertSee('name="options[images]" value="0"', false);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Audit',
        'options' => ['images' => '0', 'evidence' => '1'],
    ])->assertRedirect();

    expect($project->reports()->firstOrFail()->report_options['images'])->toBeFalse()
        ->and($project->fresh()->report_options['images'])->toBeFalse();
});

test('report generation rejects stale Mermaid diagrams without saving a version', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $project->issues()->create([
        'title' => 'Keyboard trap',
        'mermaid_diagram' => 'flowchart LR; A --> B',
        'mermaid_svg' => '<svg xmlns="http://www.w3.org/2000/svg"><text>Old diagram</text></svg>',
        'mermaid_source_hash' => IssueDiagramSvg::hashSource('flowchart LR; X --> Y'),
        'severity' => 'critical',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Audit',
    ])->assertSessionHasErrors('report');

    expect($project->reports()->count())->toBe(0)
        ->and($project->fresh()->report_options)->toBeNull();
});

test('failed report update preserves previous version and profile', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $this->actingAs($user)->post(route('accessibility-reports.store', $project), ['title' => 'Before invalid diagram'])->assertRedirect();
    $original = $project->reports()->firstOrFail();

    $project->issues()->create([
        'title' => 'New diagram',
        'mermaid_diagram' => 'flowchart LR; A --> B',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $this->actingAs($user)->post(route('accessibility-reports.regenerate', [$project, $original]))
        ->assertSessionHasErrors('report');

    expect($project->reports()->count())->toBe(1)
        ->and($original->fresh()->total_issues)->toBe(0)
        ->and($original->html_content)->not->toContain('New diagram');
});

test('missing report evidence blocks generation unless images are excluded', function () {
    Storage::fake('public');
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $issue = $project->issues()->create(['title' => 'Missing image', 'severity' => 'major', 'difficulty' => 'easy', 'status' => 'open']);
    $issue->attachments()->create([
        'filename' => 'missing.png',
        'original_filename' => 'missing.png',
        'mime_type' => 'image/png',
        'size' => 8,
        'path' => 'accessibility-issues/missing.png',
    ]);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), ['title' => 'Audit'])
        ->assertSessionHasErrors('report');
    expect($project->reports()->count())->toBe(0);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Without images',
        'options' => ['images' => false],
    ])->assertRedirect();
    expect($project->reports()->count())->toBe(1);
});

test('report orders project-wide findings then pages by highest severity and findings by status', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $minorPage = $project->pages()->create(['name' => 'A page', 'scope' => 'in_scope']);
    $criticalPage = $project->pages()->create(['name' => 'Z page', 'scope' => 'in_scope']);

    foreach ([
        ['title' => 'Project finding', 'severity' => 'moderate', 'status' => 'open', 'page_id' => null],
        ['title' => 'Minor page finding', 'severity' => 'minor', 'status' => 'open', 'page_id' => $minorPage->id],
        ['title' => 'Resolved critical', 'severity' => 'critical', 'status' => 'resolved', 'page_id' => $criticalPage->id],
        ['title' => 'Open major', 'severity' => 'major', 'status' => 'open', 'page_id' => $criticalPage->id],
    ] as $finding) {
        $project->issues()->create($finding + ['difficulty' => 'easy']);
    }

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), ['title' => 'Ordered audit'])->assertRedirect();
    $html = $project->reports()->firstOrFail()->html_content;
    $main = mb_substr($html, mb_strpos($html, '<section id="project-wide-issues"'));

    expect(mb_strpos($main, 'Project finding'))->toBeLessThan(mb_strpos($main, 'id="page-'.mb_strtolower($criticalPage->id).'"'))
        ->and(mb_strpos($main, 'id="page-'.mb_strtolower($criticalPage->id).'"'))->toBeLessThan(mb_strpos($main, 'id="page-'.mb_strtolower($minorPage->id).'"'))
        ->and(mb_strpos($main, 'Open major'))->toBeLessThan(mb_strpos($main, 'Resolved critical'));
});

test('report version survives deletion of its creator', function () {
    $team = Team::factory()->create();
    $creator = User::factory()->create(['team_id' => $team->id]);
    $viewer = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $creator->id, 'role' => 'owner']);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $viewer->id, 'role' => 'viewer']);

    $this->actingAs($creator)->post(route('accessibility-reports.store', $project), ['title' => 'Retained audit'])->assertRedirect();
    $report = $project->reports()->firstOrFail();
    $creatorName = $creator->name;
    $creator->delete();

    expect($report->fresh()->created_by)->toBeNull();
    $this->actingAs($viewer)->get(route('accessibility-reports.show', [$project, $report]))
        ->assertSuccessful()
        ->assertSee($creatorName)
        ->assertDontSee(__('Delete Report'));
    $this->actingAs($viewer)->get(route('accessibility-reports.index', $project))
        ->assertSuccessful()
        ->assertSee($creatorName)
        ->assertDontSee('delete-report-'.$report->id, false);
});

test('report embeds its project logo as a snapshot independent of later changes', function () {
    Storage::fake('public');
    Storage::disk('public')->put('project-logos/logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jB1kAAAAASUVORK5CYII='));
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id, 'client_logo' => 'project-logos/logo.png']);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), ['title' => 'Branded audit'])->assertRedirect();
    $report = $project->reports()->firstOrFail();
    expect($report->html_content)->toContain('data:image/png;base64,');

    $original = $report->html_content;
    Storage::disk('public')->delete('project-logos/logo.png');
    $project->update(['name' => 'Renamed project', 'client_logo' => null]);
    expect($report->fresh()->html_content)->toBe($original);
});

test('report snapshots company branding creator date and selected website icon', function () {
    Storage::fake('public');
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jB1kAAAAASUVORK5CYII=');
    Storage::disk('public')->put('brand/company.png', $png);
    AppSetting::set('brand_logo', 'brand/company.png');
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $project->pages()->create([
        'name' => 'Start',
        'url' => 'https://example.com/home',
        'scope' => 'in_scope',
    ]);
    $project->pages()->create([
        'name' => 'Other site',
        'url' => 'https://other.example.org/',
        'scope' => 'out_of_scope',
    ]);
    app()->instance(ReportSiteIcon::class, new class extends ReportSiteIcon
    {
        protected function publicAddress(string $host): string
        {
            return '93.184.215.14';
        }
    });
    Http::preventStrayRequests();
    Http::fake([
        'https://example.com/' => Http::response('<html><head><link rel="icon" href="/icon.png"></head></html>', 200, ['Content-Type' => 'text/html']),
        'https://example.com/icon.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
    ]);

    $this->actingAs($user)->get(route('accessibility-reports.create', $project))
        ->assertSuccessful()
        ->assertSee('https://example.com', false)
        ->assertDontSee('https://other.example.org', false);
    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Branded report',
        'site_url' => 'https://other.example.org',
    ])->assertSessionHasErrors('site_url');

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Branded report',
        'site_url' => 'https://example.com',
    ])->assertRedirect();
    $report = $project->reports()->firstOrFail();
    expect($project->fresh()->report_site_url)->toBeNull()
        ->and($report->document_metadata['site_url'])->toBe('https://example.com')
        ->and($report->html_content)->toContain(__('Company logo'))
        ->toContain(__('Website icon'))
        ->toContain($user->name)
        ->toContain(__('Generated'))
        ->toContain('data:image/png;base64,'.base64_encode($png));
    expect(substr_count($report->html_content, 'data:image/png;base64,'.base64_encode($png)))->toBe(2);
    $originalHtml = $report->html_content;
    AppSetting::set('brand_logo', null);
    Storage::disk('public')->delete('brand/company.png');
    expect($report->fresh()->html_content)->toBe($originalHtml);
    $this->actingAs($user)->get(route('accessibility-reports.download', [$project, $report]))->assertSuccessful();
    Http::assertSentCount(2);
});

test('project primary website is selectable for the report without test pages', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create([
        'team_id' => $team->id,
        'report_site_url' => 'https://example.com',
    ]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $this->actingAs($user)->get(route('accessibility-reports.create', $project))
        ->assertSuccessful()
        ->assertSee('value="https://example.com" selected', false);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'No icon',
        'site_url' => '',
    ])->assertRedirect();
    expect($project->fresh()->report_site_url)->toBe('https://example.com')
        ->and($project->reports()->firstOrFail()->document_metadata['site_url'])->toBe('');
});

test('uploaded company logo appears in new reports', function () {
    Storage::fake('public');
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);

    Livewire::actingAs($user)->test(AppSettings::class)
        ->set('logo', UploadedFile::fake()->image('company.png'))
        ->call('save')
        ->assertHasNoErrors();
    $path = AppSetting::get('brand_logo');
    Storage::disk('public')->assertExists($path);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), ['title' => 'Company report'])->assertRedirect();
    $report = $project->reports()->firstOrFail();
    expect($report->html_content)->toContain('src="data:image/png;base64,')
        ->toContain('alt="'.__('Company logo').'"')
        ->toContain($user->name);
});

test('missing website icon stops generation without changing saved site selection', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $project->pages()->create(['name' => 'Site', 'url' => 'https://example.com/', 'scope' => 'in_scope']);
    app()->instance(ReportSiteIcon::class, new class extends ReportSiteIcon
    {
        protected function publicAddress(string $host): string
        {
            return '93.184.215.14';
        }
    });
    Http::preventStrayRequests();
    Http::fake([
        'https://example.com/' => Http::response('<html><head><link rel="icon" href="https://private.example/icon.png"></head></html>', 200, ['Content-Type' => 'text/html']),
        'https://example.com/favicon.ico' => Http::response('', 404),
    ]);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'No icon available',
        'site_url' => 'https://example.com',
    ])->assertSessionHasErrors('report');
    expect($project->reports()->count())->toBe(0)
        ->and($project->fresh()->report_site_url)->toBeNull();
    Http::assertSentCount(2);
});

test('website icon reader accepts PNG embedded in an ICO and rejects private addresses', function () {
    $team = Team::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $project->pages()->create(['name' => 'Site', 'url' => 'https://example.com/', 'scope' => 'in_scope']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jB1kAAAAASUVORK5CYII=');
    $ico = pack('vvv', 0, 1, 1).pack('CCCCvvVV', 1, 1, 0, 0, 1, 32, strlen($png), 22).$png;
    app()->instance(ReportSiteIcon::class, new class extends ReportSiteIcon
    {
        protected function publicAddress(string $host): string
        {
            return '93.184.215.14';
        }

        public function checkPrivateAddress(): string
        {
            return parent::publicAddress('localhost');
        }
    });
    Http::preventStrayRequests();
    Http::fake([
        'https://example.com/' => Http::response('<html></html>', 200, ['Content-Type' => 'text/html']),
        'https://example.com/favicon.ico' => Http::response($ico, 200, ['Content-Type' => 'image/x-icon']),
    ]);

    expect(app(ReportSiteIcon::class)->fetch($project, 'https://example.com'))
        ->toBe('data:image/png;base64,'.base64_encode($png));
    expect(fn () => app(ReportSiteIcon::class)->checkPrivateAddress())
        ->toThrow(ValidationException::class);
});

test('report PDF includes a saved Mermaid diagram without its source', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $source = 'flowchart LR; A --> B';
    $project->issues()->create([
        'title' => 'Diagram finding',
        'description' => 'Diagram description',
        'mermaid_diagram' => $source,
        'mermaid_svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="50" viewBox="0 0 100 50"><path d="M 0 0 L 100 50" fill="none" stroke="#000000" stroke-dasharray="5 3" stroke-dashoffset="0px"/><text x="4" y="20" font-family="Figtree, sans-serif">Diagram</text></svg>',
        'mermaid_source_hash' => IssueDiagramSvg::hashSource($source),
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), ['title' => 'Diagram audit'])->assertRedirect();
    $report = $project->reports()->firstOrFail();
    preg_match('/data:image\/svg\+xml;base64,([A-Za-z0-9+\/=]+)/', $report->html_content, $embeddedDiagram);
    expect(base64_decode($embeddedDiagram[1], true))->toContain('stroke-dashoffset="0"');
    expect($report->html_content)->toContain('data:image/svg+xml;base64,')
        ->not->toContain('flowchart LR; A --&gt; B');

    $response = $this->actingAs($user)->get(route('accessibility-reports.download', [$project, $report]));
    $response->assertSuccessful()->assertHeader('Content-Type', 'application/pdf');
    $diagramPdf = $response->streamedContent();
    expect($diagramPdf)->toStartWith('%PDF-')
        ->toContain('DejaVuSans')
        ->not->toContain('/BaseFont /MPDFAA+DejaVuSansCondensed')
        ->not->toContain('/BaseFont /MPDFAA+DejaVuSerif')
        ->toContain('/Subtype /Form');

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Without diagram',
        'options' => ['diagrams' => false],
    ])->assertRedirect();
    $withoutDiagram = $project->reports()->where('title', 'Without diagram')->firstOrFail();
    $plainPdf = $this->actingAs($user)
        ->get(route('accessibility-reports.download', [$project, $withoutDiagram]))
        ->streamedContent();
    expect(substr_count($diagramPdf, '/Subtype /Form'))
        ->toBeGreaterThan(substr_count($plainPdf, '/Subtype /Form'));

    $legacyHtml = preg_replace_callback(
        '/data:image\/svg\+xml;base64,([A-Za-z0-9+\/=]+)/',
        fn (array $match): string => 'data:image/svg+xml;base64,'.base64_encode(
            preg_replace('/stroke-dashoffset="[^"]*"/', 'stroke-dashoffset="0px"', base64_decode($match[1], true))
        ),
        $report->html_content,
    );
    expect($legacyHtml)->not->toBe($report->html_content);
    $report->update(['html_content' => $legacyHtml]);
    expect($report->html_content)->toBe($legacyHtml);
    $this->actingAs($user)->get(route('accessibility-reports.download', [$project, $report]))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/pdf');
    expect($report->fresh()->html_content)->toBe($legacyHtml);
});

test('report embeds linked example diagrams as PDF vectors without their source', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    ProjectMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'owner']);
    $issue = $project->issues()->create([
        'title' => 'Example diagram finding',
        'description' => 'A diagram explains this problem',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternative',
        'description_sv' => 'Textalternativ',
    ]);
    $source = 'flowchart LR; A --> B';
    $example = $criterion->examples()->create([
        'title' => 'Review flow',
        'code_language' => 'mermaid',
        'code_snippet' => $source,
        'mermaid_svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="50" viewBox="0 0 100 50"><path d="M 0 0 L 100 50" fill="none" stroke="#000000" stroke-dasharray="5 3" stroke-dashoffset="0px"/><text x="4" y="20" font-family="Figtree, sans-serif">Flow</text></svg>',
        'mermaid_source_hash' => IssueDiagramSvg::hashSource($source),
    ]);
    $issue->examples()->attach($example->id);

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), ['title' => 'With example'])->assertRedirect();
    $report = $project->reports()->where('title', 'With example')->firstOrFail();
    expect($report->html_content)->toContain('data:image/svg+xml;base64,')
        ->not->toContain('flowchart LR; A --&gt; B')
        ->toContain('Review flow');
    $pdf = $this->actingAs($user)->get(route('accessibility-reports.download', [$project, $report]))->streamedContent();
    expect($pdf)->toStartWith('%PDF-')
        ->toContain('/Subtype /Form')
        ->toContain('/BaseFont /MPDFAA+DejaVuSans')
        ->not->toContain('/BaseFont /MPDFAA+DejaVuSansCondensed')
        ->not->toContain('/BaseFont /MPDFAA+DejaVuSerif');

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Without example diagram',
        'options' => ['diagrams' => false],
    ])->assertRedirect();
    $without = $project->reports()->where('title', 'Without example diagram')->firstOrFail();
    expect($without->html_content)->not->toContain('data:image/svg+xml;base64,')
        ->not->toContain('flowchart LR; A --&gt; B');
    $plainPdf = $this->actingAs($user)->get(route('accessibility-reports.download', [$project, $without]))->streamedContent();
    expect(substr_count($pdf, '/Subtype /Form'))->toBeGreaterThan(substr_count($plainPdf, '/Subtype /Form'));

    $example->update(['code_snippet' => 'flowchart LR; B --> C']);
    expect($example->fresh()->mermaid_svg)->toBeNull();
    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Outdated example',
        'options' => ['diagrams' => true],
    ])
        ->assertSessionHasErrors(['report' => __('The diagram for example ":title" is not ready for the report. Edit and save the example in the knowledge base to render it.', ['title' => $example->title])]);
    expect($project->reports()->where('title', 'Outdated example')->exists())->toBeFalse();

    $this->actingAs($user)->post(route('accessibility-reports.store', $project), [
        'title' => 'Without examples',
        'options' => ['examples' => false, 'diagrams' => true],
    ])->assertRedirect();
    expect($project->reports()->where('title', 'Without examples')->exists())->toBeTrue();
});

test('generated report includes page and service access context', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $project->pages()->create([
        'name' => 'Mina sidor',
        'resource_type' => 'service',
        'access_context' => 'authenticated',
        'url' => 'https://example.com/login',
        'description' => '<p>Inloggat flöde.</p>',
    ]);

    $this->actingAs($user)
        ->post(route('accessibility-reports.store', $project), [
            'title' => 'Service audit',
        ])
        ->assertRedirect();

    $report = AccessibilityReport::query()
        ->where('project_id', $project->id)
        ->where('title', 'Service audit')
        ->firstOrFail();

    expect($report->html_content)
        ->toContain('Mina sidor')
        ->toContain('class="report-document"')
        ->toContain('class="report-document__summary"')
        ->not->toContain('linear-gradient')
        ->toContain('Service')
        ->toContain(__('Authenticated'))
        ->toContain('Inloggat flöde.');

    $this->actingAs($user)
        ->get(route('accessibility-reports.show', ['project' => $project, 'report' => $report]))
        ->assertSuccessful()
        ->assertSee('report-document__paper')
        ->assertSee('bg-white dark:border-zinc-700 dark:bg-white', false)
        ->assertSee('title="'.__('Saved report preview').'"', false)
        ->assertSee(__('Report generated on :date', ['date' => $report->created_at->translatedFormat('j M Y, H:i')]))
        ->assertSee(__('Download PDF'))
        ->assertDontSee(__('This report uses an older template'))
        ->assertDontSee('<main class="report-document" data-report-template', false);
});

test('generated report excludes out-of-scope pages and issues but keeps project-wide issues', function () {
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
        'scope' => 'in_scope',
    ]);
    $outOfScopePage = $project->pages()->create([
        'name' => 'Excluded page',
        'scope' => 'out_of_scope',
    ]);

    $project->issues()->createMany([
        [
            'page_id' => $inScopePage->id,
            'title' => 'Included issue',
            'description' => 'This issue belongs in the report.',
            'severity' => 'critical',
            'difficulty' => 'easy',
            'status' => 'open',
        ],
        [
            'page_id' => $inScopePage->id,
            'title' => 'Moderate included issue',
            'description' => 'This moderate issue belongs in the report.',
            'severity' => 'moderate',
            'difficulty' => 'medium',
            'status' => 'open',
        ],
        [
            'page_id' => $outOfScopePage->id,
            'title' => 'Excluded issue',
            'description' => 'This issue must not appear in the report.',
            'severity' => 'minor',
            'difficulty' => 'easy',
            'status' => 'open',
        ],
        [
            'page_id' => null,
            'title' => 'Project-wide issue',
            'description' => 'This issue applies across the project.',
            'severity' => 'major',
            'difficulty' => 'medium',
            'status' => 'open',
        ],
    ]);

    $this->actingAs($user)
        ->post(route('accessibility-reports.store', $project), [
            'title' => 'Scoped report',
        ])
        ->assertRedirect();

    $report = AccessibilityReport::query()
        ->where('project_id', $project->id)
        ->where('title', 'Scoped report')
        ->firstOrFail();

    expect($report->total_issues)->toBe(3)
        ->and($report->critical_count)->toBe(1)
        ->and($report->major_count)->toBe(1)
        ->and($report->moderate_count)->toBe(1)
        ->and($report->minor_count)->toBe(0)
        ->and($report->html_content)
        ->toContain('Included page')
        ->toContain('Included issue')
        ->toContain('Moderate included issue')
        ->toContain('Project-wide issue')
        ->not->toContain('Excluded page')
        ->not->toContain('Excluded issue')
        ->toContain(__('Moderate'))
        ->toContain(__('Minor'));
});

test('can view generated report', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports/{$report->id}");

    $response->assertStatus(200);
    $response->assertSee('Test Report')
        ->assertSee('delete-report-modal', false)
        ->assertSee(__('This report uses an older template'))
        ->assertSee('title="'.__('Saved report preview').'"', false)
        ->assertSee(route('accessibility-reports.regenerate', ['project' => $project, 'report' => $report]), false)
        ->assertSee(__('Delete this report?'))
        ->assertDontSee('confirm(', false);
});

test('owner can update an older report with current project evidence and template', function () {
    Storage::fake('public');
    Storage::disk('public')->put('accessibility-issues/keyboard-trap.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jB1kAAAAASUVORK5CYII='));
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $issue = $project->issues()->create([
        'title' => 'Keyboard trap',
        'description' => '<p>Focus cannot leave the dialog.</p>',
        'solution_suggestions' => '<p>Restore focus when the dialog closes.</p>',
        'component_area' => 'Dialog',
        'sample_scope' => 'some',
        'severity' => 'critical',
        'difficulty' => 'medium',
        'status' => 'open',
    ]);
    $issue->attachments()->create([
        'filename' => 'keyboard-trap.png',
        'original_filename' => 'keyboard-trap.png',
        'mime_type' => 'image/png',
        'size' => 120,
        'path' => 'accessibility-issues/keyboard-trap.png',
    ]);
    $criterion = WcagSuccessCriterion::create([
        'number' => '2.1.2',
        'level' => 'A',
        'name_en' => 'No Keyboard Trap',
        'name_sv' => 'Ingen tangentbordsfälla',
        'description_en' => 'Keyboard focus can leave components.',
        'description_sv' => 'Tangentbordsfokus kan lämna komponenter.',
        'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/no-keyboard-trap.html',
    ]);
    $issue->wcagCriteria()->attach($criterion->id, [
        'failure_type' => 'keyboard_trap',
        'comment' => 'Focus remains inside the dialog.',
        'code_snippet' => 'dialog.addEventListener("keydown", trapFocus);',
    ]);
    $example = $criterion->examples()->create([
        'title' => 'Return focus after close',
        'description' => 'Move focus to the control that opened the dialog.',
        'code_snippet' => 'trigger.focus();',
        'code_language' => 'javascript',
    ]);
    $issue->examples()->attach($example->id);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Legacy report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<html><body>Old report</body></html>',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)->post(route('accessibility-reports.regenerate', ['project' => $project, 'report' => $report]))
        ->assertRedirect();

    $newVersion = $project->reports()->where('version', 2)->firstOrFail();
    expect($report->fresh()->html_content)->toBe('<html><body>Old report</body></html>')
        ->and($report->total_issues)->toBe(0)
        ->and($newVersion->previous_version_id)->toBe($report->id)
        ->and($newVersion->total_issues)->toBe(1)
        ->and($newVersion->html_content)
        ->toContain('data-report-template="8"')
        ->toContain('Keyboard trap')
        ->toContain('Dialog')
        ->toContain(__('Parts of the page or service'))
        ->toContain('Restore focus when the dialog closes.')
        ->toContain('keyboard-trap.png')
        ->toContain('data:image/png;base64,')
        ->toContain('Focus remains inside the dialog.')
        ->toContain('Return focus after close')
        ->toContain('trigger.focus();');

    Storage::disk('public')->delete('accessibility-issues/keyboard-trap.png');
    expect($newVersion->fresh()->html_content)->toContain('data:image/png;base64,');
    $this->actingAs($user)->get(route('accessibility-reports.download', [$project, $newVersion]))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('can download report as pdf', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Audit "Finance"/2026',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1><p>Test content</p>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}/reports/{$report->id}/download");

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');

    expect($response->headers->get('Content-Disposition'))
        ->toContain(Str::slug($report->title).'-'.now()->format('Y-m-d').'.pdf')
        ->not->toContain("\r")
        ->not->toContain("\n");
    expect(str_starts_with($response->streamedContent(), '%PDF-'))->toBeTrue();
});

test('report routes are scoped to their parent project', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $otherProject = AccessibilityProject::factory()->create(['team_id' => $team->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $otherProject->id,
        'title' => 'Other project report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Other project report</h1>',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('accessibility-reports.show', [$project, $report]))
        ->assertNotFound();

    $this->get(route('accessibility-reports.download', [$project, $report]))
        ->assertNotFound();

    $this->post(route('accessibility-reports.regenerate', [$project, $report]))
        ->assertNotFound();

    $this->delete(route('accessibility-reports.destroy', [$project, $report]))
        ->assertNotFound();

    $this->assertDatabaseHas('accessibility_reports', ['id' => $report->id]);
});

test('only members can view reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $other_user = User::factory()->create(['team_id' => $household->id]);

    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    // Non-member trying to access report
    $response = $this->actingAs($other_user)->get("/accessibility-projects/{$project->id}/reports/{$report->id}");
    $response->assertStatus(403);
});

test('editor can generate reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Editor Report',
    ]);

    $response->assertRedirect();
    expect(AccessibilityReport::where([
        'project_id' => $project->id,
        'title' => 'Editor Report',
    ])->exists())->toBeTrue();
});

test('viewer cannot generate reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'viewer',
    ]);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Viewer Report',
    ]);

    $response->assertStatus(403);
    expect(AccessibilityReport::where('title', 'Viewer Report')->exists())->toBeFalse();
});

test('only owner can delete reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->delete("/accessibility-projects/{$project->id}/reports/{$report->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('accessibility_reports', [
        'id' => $report->id,
    ]);
});

test('editor cannot delete reports', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $report = AccessibilityReport::create([
        'project_id' => $project->id,
        'title' => 'Test Report',
        'target_wcag_level' => 'AA',
        'total_issues' => 0,
        'html_content' => '<h1>Test Report</h1>',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->delete("/accessibility-projects/{$project->id}/reports/{$report->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('accessibility_reports', [
        'id' => $report->id,
    ]);
});

test('report displays wcag criteria with localized descriptions', function () {
    $household = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $household->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $household->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    // Create a WCAG criterion
    $wcagCriterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehål',
        'description_en' => 'All non-text content that is presented to the user has a text alternative.',
        'description_sv' => 'Allt icke-textinnehål har ett textalternativ.',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.1.1.html',
    ]);

    // Create an issue and link it to WCAG criterion
    $issue = $project->issues()->create([
        'title' => 'Missing alt text on images',
        'description' => 'All images lack alternative text',
        'severity' => 'critical',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);

    $issue->wcagCriteria()->attach($wcagCriterion->id);

    $response = $this->actingAs($user)->post("/accessibility-projects/{$project->id}/reports", [
        'title' => 'Audit with WCAG Links',
        'scope' => 'Full website',
    ]);

    $response->assertRedirect();

    $report = AccessibilityReport::where('title', 'Audit with WCAG Links')->first();
    expect($report)->not->toBeNull();

    // Verify WCAG criterion number appears in report
    expect($report->html_content)->toContain('1.1.1');
    // Report may contain localized name_sv or name_en depending on app locale
    $this->assertTrue(str_contains($report->html_content, $wcagCriterion->name_en) || str_contains($report->html_content, $wcagCriterion->name_sv));
});
