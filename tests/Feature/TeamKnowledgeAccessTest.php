<?php

use App\Livewire\ProjectMethodologies;
use App\Livewire\TestingMethodologies;
use App\Livewire\WcagKnowledgeBase;
use App\Models\AccessibilityProject;
use App\Models\ProjectShareLink;
use App\Models\Team;
use App\Models\TestingMethodology;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->owner = User::factory()->create(['team_id' => $this->team->id]);
    $this->team->update(['owner_id' => $this->owner->id]);
    $this->editor = User::factory()->create(['team_id' => $this->team->id]);
    $this->otherTeam = Team::factory()->create();
    $this->other = User::factory()->create(['team_id' => $this->otherTeam->id]);
    $this->otherTeam->update(['owner_id' => $this->other->id]);
    $this->criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1', 'level' => 'A',
        'name_en' => 'Non-text content', 'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Guidance', 'description_sv' => 'Vägledning',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content',
    ]);
});

test('team owner appoints and removes editor; another team cannot appoint', function () {
    $this->actingAs($this->owner)->get(route('team.knowledge-editors.index'))->assertSuccessful();
    $this->put(route('team.knowledge-editors.update', $this->editor), ['is_knowledge_editor' => '1'])->assertRedirect();
    expect($this->editor->fresh()->canEditTeamKnowledge())->toBeTrue();
    $this->put(route('team.knowledge-editors.update', $this->other), ['is_knowledge_editor' => '1'])->assertNotFound();
    $this->actingAs($this->editor)->put(route('team.knowledge-editors.update', $this->owner), ['is_knowledge_editor' => '0'])->assertForbidden();
    $this->actingAs($this->owner)->put(route('team.knowledge-editors.update', $this->editor), ['is_knowledge_editor' => '0'])->assertRedirect();
    expect($this->editor->fresh()->canEditTeamKnowledge())->toBeFalse();
    Livewire::actingAs($this->editor->fresh())->test(TestingMethodologies::class)
        ->set('name', 'After revocation')->call('save')->assertForbidden();
});

test('reference criteria and resources cannot be changed even by owner', function () {
    Livewire::actingAs($this->owner)->test(WcagKnowledgeBase::class)
        ->call('startCreatingCriterion')->assertForbidden();
    Livewire::actingAs($this->owner)->test(WcagKnowledgeBase::class)
        ->call('editCriterion', $this->criterion->id)->assertForbidden();
    Livewire::actingAs($this->owner)->test(WcagKnowledgeBase::class)
        ->call('deleteCriterion', $this->criterion->id)->assertForbidden();
    Livewire::actingAs($this->owner)->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $this->criterion->id)->call('startCreatingResource')->assertForbidden();
});

test('editor creates own example but cannot edit global or other team examples', function () {
    $global = $this->criterion->examples()->create(['title' => 'Global reference']);
    $otherExample = $this->criterion->examples()->create(['title' => 'Other team secret', 'team_id' => $this->otherTeam->id]);
    $this->editor->update(['is_knowledge_editor' => true]);
    $component = Livewire::actingAs($this->editor)->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $this->criterion->id)
        ->assertSee('Global reference')
        ->assertDontSee('Other team secret')
        ->set('exampleTitle', 'Our example')
        ->call('saveExample')->assertHasNoErrors();
    $ours = $this->criterion->examples()->where('title', 'Our example')->firstOrFail();
    expect($ours->team_id)->toBe((string) $this->team->id);
    $component->call('editExample', $ours->id)->assertSet('exampleTitle', 'Our example');
    Livewire::actingAs($this->editor)->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $this->criterion->id)->call('editExample', $global->id)->assertForbidden();
    expect(fn () => Livewire::actingAs($this->editor)->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $this->criterion->id)->call('deleteExample', $otherExample->id))
        ->toThrow(ModelNotFoundException::class);
    Livewire::actingAs($this->other)->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $this->criterion->id)->assertDontSee('Our example');
    expect(fn () => Livewire::actingAs($this->other)->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $this->criterion->id)->call('editExample', $ours->id))
        ->toThrow(ModelNotFoundException::class);
});

test('only team editors manage team methods, while global defaults stay read-only', function () {
    $default = TestingMethodology::create(['name' => 'Shared method', 'category' => 'testing_tool']);
    $otherMethod = TestingMethodology::create([
        'name' => 'Other team secret', 'category' => 'testing_tool',
        'is_custom' => true, 'team_id' => $this->otherTeam->id,
    ]);
    $this->actingAs($this->other)->get(route('testing-methodologies'))->assertDontSee('Our method');
    Livewire::actingAs($this->editor)->test(TestingMethodologies::class)
        ->call('openCreateForm')->assertForbidden();
    Livewire::actingAs($this->editor)->test(TestingMethodologies::class)
        ->set('name', 'Hidden write')->call('save')->assertForbidden();
    $this->editor->update(['is_knowledge_editor' => true]);
    Livewire::actingAs($this->editor)->test(TestingMethodologies::class)
        ->assertSee('Shared method')
        ->assertDontSee('Other team secret')
        ->call('edit', $default->id)->assertForbidden();
    $component = Livewire::actingAs($this->editor)->test(TestingMethodologies::class);
    $component->set('name', 'Our method')->set('category', 'browser')->call('save')->assertHasNoErrors();
    $ours = TestingMethodology::where('name', 'Our method')->firstOrFail();
    expect($ours->team_id)->toBe((string) $this->team->id);
    expect(fn () => $component->call('delete', $otherMethod->id))->toThrow(ModelNotFoundException::class);
    Livewire::actingAs($this->other)->test(TestingMethodologies::class)->assertDontSee('Our method');
});

test('project methods reject cross-team IDs but allow global and own team methods', function () {
    $project = AccessibilityProject::factory()->create(['team_id' => $this->team->id]);
    $project->members()->create(['user_id' => $this->owner->id, 'role' => 'owner']);
    $global = TestingMethodology::create(['name' => 'Default method', 'category' => 'browser']);
    $otherMethod = TestingMethodology::create([
        'name' => 'Other method', 'category' => 'browser', 'team_id' => $this->otherTeam->id,
    ]);
    $component = Livewire::actingAs($this->owner)->test(ProjectMethodologies::class, ['project' => $project])
        ->assertSee('Default method')->assertDontSee('Other method');
    $component->set('selectedMethodologyId', $otherMethod->id)->call('addMethodology')
        ->assertHasErrors('selectedMethodologyId');
    $component->set('selectedMethodologyId', $global->id)->call('addMethodology')->assertHasNoErrors();
    expect($project->methodologies()->count())->toBe(1);
    $component->assertSee('Default method')->assertDontSee('Other method');
});

test('project preview and newly generated report exclude linked cross-team examples and methodologies', function () {
    $project = AccessibilityProject::factory()->create(['team_id' => $this->team->id]);
    $project->members()->create(['user_id' => $this->owner->id, 'role' => 'owner']);
    $issue = $project->issues()->create([
        'title' => 'Keyboard issue',
        'severity' => 'major',
        'difficulty' => 'easy',
        'status' => 'open',
    ]);
    $global = $this->criterion->examples()->create(['title' => 'Shared guidance example']);
    $own = $this->criterion->examples()->create(['title' => 'Our team example', 'team_id' => (string) $this->team->id]);
    $other = $this->criterion->examples()->create(['title' => 'Other team confidential example', 'team_id' => (string) $this->otherTeam->id]);
    $issue->examples()->attach([$global->id, $own->id, $other->id]);

    $otherMethod = TestingMethodology::create([
        'name' => 'Other team confidential method',
        'category' => 'testing_tool',
        'team_id' => (string) $this->otherTeam->id,
    ]);
    $project->methodologies()->attach($otherMethod->id, ['id' => (string) Str::ulid()]);

    $this->actingAs($this->owner)
        ->get(route('accessibility-projects.preview', $project))
        ->assertSuccessful()
        ->assertSee('Shared guidance example')
        ->assertSee('Our team example')
        ->assertDontSee('Other team confidential example');

    $this->post(route('accessibility-reports.store', $project), ['title' => 'Scoped audit'])
        ->assertRedirect();
    $html = $project->reports()->firstOrFail()->html_content;
    expect($html)->toContain('Shared guidance example', 'Our team example')
        ->not->toContain('Other team confidential example', 'Other team confidential method');

    $link = ProjectShareLink::create([
        'project_id' => $project->id,
        'report_id' => $project->reports()->firstOrFail()->id,
        'created_by' => $this->owner->id,
        'expires_at' => now()->addDay(),
    ]);
    auth()->logout();
    $response = $this->get(route('projects.shared', $link->token))->assertSuccessful();
    expect($response->getContent())->toBe($html)
        ->not->toContain('Other team confidential example', 'Other team confidential method');
});
