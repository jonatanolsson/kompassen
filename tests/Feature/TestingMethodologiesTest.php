<?php

use App\Livewire\TestingMethodologies;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\Team;
use App\Models\TestingMethodology;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('team users can view testing methodology administration', function () {
    $user = User::factory()->create();
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
        'is_custom' => true,
    ]);

    $response = $this->actingAs($user)->get(route('testing-methodologies'));

    $response->assertSuccessful()
        ->assertSee(__('Testing Methodologies'))
        ->assertDontSee('testing-methodology-form-modal', false)
        ->assertDontSee('delete-testing-methodology-'.$methodology->id, false)
        ->assertDontSee('wire:confirm', false)
        ->assertSee($methodology->name);
});

test('users without a team cannot administer testing methodologies', function () {
    $user = User::factory()->create(['team_id' => null]);

    $this->actingAs($user)
        ->get(route('testing-methodologies'))
        ->assertForbidden();
});

test('users can create update and delete an unused custom methodology', function () {
    $user = User::factory()->create();
    $user->team->update(['owner_id' => $user->id]);

    $component = Livewire::actingAs($user)
        ->test(TestingMethodologies::class)
        ->call('openCreateForm')
        ->assertSet('editingMethodologyId', null)
        ->set('name', 'Keyboard testing')
        ->set('category', 'testing_tool')
        ->set('description', 'Manual keyboard-only checks.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'testing-methodology-form-modal');

    $methodology = TestingMethodology::query()
        ->where('name', 'Keyboard testing')
        ->firstOrFail();

    expect($methodology->is_custom)->toBeTrue()
        ->and($methodology->team_id)->toBe((string) $user->team_id);

    $component
        ->call('edit', $methodology->id)
        ->assertSet('editingMethodologyId', $methodology->id)
        ->assertSet('category', 'testing_tool')
        ->assertSet('description', 'Manual keyboard-only checks.')
        ->set('name', 'Keyboard and focus testing')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'testing-methodology-form-modal');

    expect($methodology->refresh()->name)->toBe('Keyboard and focus testing');

    $component->call('delete', $methodology->id);

    $this->assertModelMissing($methodology);
});

test('methodologies used by projects cannot be deleted', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create(['team_id' => $team->id]);
    $project = AccessibilityProject::factory()->create(['team_id' => $team->id]);
    $methodology = TestingMethodology::create([
        'name' => 'Keyboard testing',
        'category' => 'testing_tool',
        'is_custom' => true,
        'team_id' => (string) $team->id,
    ]);
    $team->update(['owner_id' => $user->id]);

    ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);

    DB::table('accessibility_project_methodologies')->insert([
        'id' => (string) Str::ulid(),
        'project_id' => $project->id,
        'methodology_id' => $methodology->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(TestingMethodologies::class)
        ->call('delete', $methodology->id);

    $this->assertModelExists($methodology);
});
