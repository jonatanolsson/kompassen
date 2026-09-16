<?php

use App\Livewire\CreateAccessibilityPage;
use App\Livewire\EditAccessibilityProject;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\User;
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

test('can view project details', function () {
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

    $response = $this->actingAs($user)->get("/accessibility-projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertSee($project->name);
    $response->assertSee(route('accessibility-reports.create', $project));
    $response->assertSee(__('Preview'));
    $response->assertSee(__('Actions'));
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
