<?php

use App\Livewire\ProjectMethodologies;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\TestingMethodology;
use App\Models\User;
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
        ->assertHasNoErrors();

    expect($project->methodologies()->whereKey($methodology->id)->exists())->toBeTrue();
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
