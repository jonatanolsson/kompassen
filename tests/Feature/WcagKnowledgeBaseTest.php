<?php

use App\Livewire\WcagKnowledgeBase;
use App\Models\User;
use App\Models\WcagCriterionExample;
use App\Models\WcagCriterionExampleLink;
use App\Models\WcagRelatedResource;
use App\Models\WcagSuccessCriterion;
use Database\Seeders\WcagRelatedResourcesSeeder;
use Livewire\Livewire;

test('knowledge base shows resources related to selected criterion', function () {
    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternative',
        'description_sv' => 'Textalternativ',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content',
    ]);

    WcagRelatedResource::create([
        'wcag_success_criterion_id' => $criterion->id,
        'type' => 'resource_link',
        'title_en' => 'DIGG: Web Guidelines',
        'title_sv' => 'DIGG: Webbriktlinjer',
        'url' => 'https://www.digg.se/webbriktlinjer/alla-webbriktlinjer',
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $criterion->id)
        ->assertSee('DIGG: Webbriktlinjer')
        ->assertSee(__('Related Resources'));
});

test('example editor is presented in a modal', function () {
    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternative',
        'description_sv' => 'Textalternativ',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content',
    ]);

    $example = $criterion->examples()->create([
        'title' => 'Visible button label',
        'description' => 'A button communicates its purpose.',
        'code_snippet' => '<button>Save</button>',
        'code_language' => 'html',
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $criterion->id)
        ->assertSee('example-form-modal')
        ->assertSee('Visible button label')
        ->assertSee(__('Edit'))
        ->call('editExample', $example->id)
        ->assertSet('editingExampleId', $example->id)
        ->assertSet('exampleTitle', 'Visible button label');
});

test('examples support multiple reference links', function () {
    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternative',
        'description_sv' => 'Textalternativ',
    ]);

    $component = Livewire::actingAs(User::factory()->create())
        ->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $criterion->id)
        ->set('exampleTitle', 'Accessible button')
        ->set('exampleLinks', [
            ['url' => 'https://developer.mozilla.org/button', 'label' => 'MDN button'],
            ['url' => 'https://www.w3.org/button', 'label' => 'W3C button guidance'],
        ])
        ->call('saveExample');

    $component
        ->assertSee('MDN button')
        ->assertSee('W3C button guidance');

    $example = WcagCriterionExample::query()
        ->where('title', 'Accessible button')
        ->firstOrFail();

    expect($example->referenceLinks)->toHaveCount(2)
        ->and($example->referenceLinks->pluck('url')->all())->toEqual([
            'https://developer.mozilla.org/button',
            'https://www.w3.org/button',
        ]);

    $component
        ->call('editExample', $example->id)
        ->set('exampleLinks', [
            ['url' => 'https://www.w3.org/updated-button', 'label' => 'Updated guidance'],
        ])
        ->call('saveExample');

    expect(WcagCriterionExampleLink::query()
        ->where('wcag_criterion_example_id', $example->id)
        ->pluck('url')
        ->all())->toBe(['https://www.w3.org/updated-button']);
});

test('related resource seeder is idempotent and uses current digg links', function () {
    WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternative',
        'description_sv' => 'Textalternativ',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content',
    ]);

    $seeder = app(WcagRelatedResourcesSeeder::class);
    $seeder->run();
    $seeder->run();

    expect(WcagRelatedResource::count())->toBe(4)
        ->and(WcagRelatedResource::where('url', 'like', 'https://www.digg.se/webbriktlinjer/%')->count())->toBe(1);
});
