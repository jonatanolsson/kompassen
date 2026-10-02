<?php

use App\Livewire\ProjectMethodologies;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\TestingMethodology;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('project owner can add a testing methodology', function () {
    $owner = User::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $owner->team_id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
    ]);

    Livewire::actingAs($owner)
        ->test(ProjectMethodologies::class, ['project' => $project])
        ->set('selectedMethodologyId', $methodology->id)
        ->call('addMethodology')
        ->assertDispatched('close-modal', name: 'add-methodology-modal')
        ->assertDispatched('toast', message: __('Testing method added to project.'), variant: 'success')
        ->assertHasNoErrors();

    expect($project->methodologies()->whereKey($methodology->id)->exists())->toBeTrue();
});

test('project testing methodologies are presented as a compact table', function () {
    $owner = User::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $owner->team_id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
        'description' => 'Full testing method description shown on its own page.',
    ]);
    $project->methodologies()->attach($methodology->id, [
        'id' => (string) Str::ulid(),
        'notes' => 'NVDA 2025',
    ]);

    Livewire::actingAs($owner)
        ->test(ProjectMethodologies::class, ['project' => $project])
        ->assertSeeHtml('class="product-section mb-8"')
        ->assertSeeHtml('class="flex items-center justify-between mb-4"')
        ->assertSeeHtml('<table')
        ->assertSeeHtml('wire:key="project-methodology-'.$methodology->id.'"')
        ->assertSee(__('Method / Tool'))
        ->assertSee(__('Version / Device / Notes'))
        ->assertSee(__('Actions'))
        ->assertSee('Keyboard testing')
        ->assertSee(__('Testing Tools'))
        ->assertSee('NVDA 2025')
        ->assertSee(route('project-methodologies.show', [$project, $methodology]))
        ->assertDontSee('Full testing method description shown on its own page.');
});

test('project owner can save testing methodology notes and keep edit open on validation errors', function () {
    $owner = User::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $owner->team_id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
    ]);
    $project->methodologies()->attach($methodology->id, [
        'id' => (string) Str::ulid(),
        'notes' => null,
    ]);

    $component = Livewire::actingAs($owner)
        ->test(ProjectMethodologies::class, ['project' => $project]);

    $component
        ->call('updateNotes', $methodology->id, str_repeat('x', 1001))
        ->assertHasErrors('notes');

    $component
        ->call('cancelNotesEdit')
        ->assertHasNoErrors()
        ->call('updateNotes', $methodology->id, 'NVDA 2025')
        ->assertHasNoErrors();

    expect($project->methodologies()->whereKey($methodology->id)->first()?->pivot->notes)
        ->toBe('NVDA 2025');
});

test('project owner can cancel adding a testing methodology and remove an assigned method', function () {
    $owner = User::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $owner->team_id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'role' => 'owner',
    ]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
    ]);
    $project->methodologies()->attach($methodology->id, [
        'id' => (string) Str::ulid(),
        'notes' => null,
    ]);

    $component = Livewire::actingAs($owner)
        ->test(ProjectMethodologies::class, ['project' => $project])
        ->set('selectedMethodologyId', $methodology->id)
        ->set('notes', 'Discard me')
        ->call('cancelAddMethodology')
        ->assertSet('selectedMethodologyId', '')
        ->assertSet('notes', '');

    $component
        ->call('removeMethodology', $methodology->id)
        ->assertDispatched('close-modal', name: 'remove-project-methodology-'.$methodology->id)
        ->assertDispatched('toast', message: __('Testing method removed from project.'), variant: 'success');

    expect($project->methodologies()->whereKey($methodology->id)->exists())->toBeFalse();
});

test('project viewer cannot change testing methodologies', function () {
    $viewer = User::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $viewer->team_id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
    ]);

    Livewire::actingAs($viewer)
        ->test(ProjectMethodologies::class, ['project' => $project])
        ->set('selectedMethodologyId', $methodology->id)
        ->call('addMethodology')
        ->assertForbidden();
});

test('non-members cannot view project testing methodologies', function () {
    $user = User::factory()->create();
    $project = AccessibilityProject::factory()->create();

    Livewire::actingAs($user)
        ->test(ProjectMethodologies::class, ['project' => $project])
        ->assertForbidden();
});

test('project members can view details for assigned testing methods', function () {
    $viewer = User::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $viewer->team_id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
        'description' => 'Detailed testing instructions.',
    ]);
    $project->methodologies()->attach($methodology->id, [
        'id' => (string) Str::ulid(),
        'notes' => 'NVDA 2025',
    ]);

    $this->actingAs($viewer)
        ->get(route('project-methodologies.show', [$project, $methodology]))
        ->assertSuccessful()
        ->assertSee('Keyboard testing')
        ->assertSee(__('Testing Tool'))
        ->assertSee('Detailed testing instructions.')
        ->assertSee('NVDA 2025')
        ->assertSee(route('accessibility-projects.show', $project).'#testing-methodology', false);
});

test('project methodology details are limited to assigned team methods and project members', function () {
    $viewer = User::factory()->create();
    $project = AccessibilityProject::factory()->create(['team_id' => $viewer->team_id]);
    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
    $assignedMethodology = TestingMethodology::create([
        'name' => 'Assigned method',
        'category' => 'testing_tool',
    ]);
    $unassignedMethodology = TestingMethodology::create([
        'name' => 'Unassigned method',
        'category' => 'testing_tool',
    ]);
    $otherTeam = Team::factory()->create();
    $otherTeamMethodology = TestingMethodology::create([
        'name' => 'Other team method',
        'team_id' => $otherTeam->id,
        'category' => 'testing_tool',
    ]);
    $project->methodologies()->attach($assignedMethodology->id, [
        'id' => (string) Str::ulid(),
    ]);
    $project->methodologies()->attach($otherTeamMethodology->id, [
        'id' => (string) Str::ulid(),
    ]);
    $nonMember = User::factory()->create();

    $this->actingAs($viewer)
        ->get(route('project-methodologies.show', [$project, $unassignedMethodology]))
        ->assertNotFound();

    $this->get(route('project-methodologies.show', [$project, $otherTeamMethodology]))
        ->assertNotFound();

    $this->actingAs($nonMember)
        ->get(route('project-methodologies.show', [$project, $assignedMethodology]))
        ->assertForbidden();
});
