<?php

use App\Livewire\WcagKnowledgeBase;
use App\Models\User;
use App\Models\WcagCriterionExample;
use App\Models\WcagCriterionExampleAttachment;
use App\Models\WcagCriterionExampleLink;
use App\Models\WcagRelatedResource;
use App\Models\WcagSuccessCriterion;
use Database\Seeders\WcagRelatedResourcesSeeder;
use Database\Seeders\WcagSuccessCriteriaSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        ->assertSee(__('Actions'))
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

test('examples support image attachments with alt text', function () {
    Storage::fake('public');

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
        ->set('exampleTitle', 'Accessible image')
        ->set('exampleAttachments', [UploadedFile::fake()->image('button.png')])
        ->set('exampleAttachmentAltTexts', [0 => 'A button with a visible label'])
        ->call('saveExample');

    $attachment = WcagCriterionExampleAttachment::query()->firstOrFail();

    expect($attachment->original_filename)->toBe('button.png')
        ->and($attachment->alt_text)->toBe('A button with a visible label');

    Storage::disk('public')->assertExists($attachment->path);
    $component->assertSee('Accessible image');
});

test('example image attachments reject non-image files', function () {
    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternative',
        'description_sv' => 'Textalternativ',
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $criterion->id)
        ->set('exampleTitle', 'Invalid attachment')
        ->set('exampleAttachments', [UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')])
        ->set('exampleAttachmentAltTexts', [0 => 'A document'])
        ->call('saveExample')
        ->assertHasErrors(['exampleAttachments.0']);
});

test('example image attachments can be deleted with their stored file', function () {
    Storage::fake('public');

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
        ->set('exampleTitle', 'Removable image')
        ->set('exampleAttachments', [UploadedFile::fake()->image('screen.png')])
        ->set('exampleAttachmentAltTexts', [0 => 'A screen capture'])
        ->call('saveExample');
    $attachment = WcagCriterionExampleAttachment::query()->firstOrFail();
    $example = $attachment->example;

    $component
        ->call('editExample', $example->id)
        ->call('deleteExampleAttachment', $attachment->id);

    expect(WcagCriterionExampleAttachment::query()->find($attachment->id))->toBeNull();
    Storage::disk('public')->assertMissing($attachment->path);
});

test('examples support mermaid diagrams', function () {
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
        ->set('exampleTitle', 'Audit flow')
        ->set('exampleCode', 'flowchart LR; A[Start] --> B[Review]')
        ->set('exampleCodeLanguage', 'mermaid')
        ->call('saveExample')
        ->assertSee('data-mermaid-diagram');

    expect(WcagCriterionExample::query()->firstOrFail()->code_language)->toBe('mermaid');
    $component->assertSee('flowchart LR');
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
    WcagSuccessCriterion::create([
        'number' => '1.4.3',
        'level' => 'AA',
        'name_en' => 'Contrast (Minimum)',
        'name_sv' => 'Kontrast (minimum)',
        'description_en' => 'Text has sufficient contrast.',
        'description_sv' => 'Text har tillräcklig kontrast.',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum',
    ]);

    $seeder = app(WcagRelatedResourcesSeeder::class);
    $seeder->run();
    $seeder->run();

    expect(WcagRelatedResource::count())->toBe(7)
        ->and(WcagRelatedResource::where('url', 'like', 'https://www.digg.se/webbriktlinjer/%')->count())->toBe(2)
        ->and(WcagRelatedResource::where('url', 'like', '%beskriv-med-text-allt-innehall-som-inte-ar-text')->count())->toBe(1)
        ->and(WcagRelatedResource::where('url', 'like', '%anvand-tillracklig-kontrast-mellan-text-och-bakgrund')->count())->toBe(1);
});

test('knowledge base entries support standards beyond wcag', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(WcagKnowledgeBase::class)
        ->call('startCreatingCriterion')
        ->set('criterionStandard', 'EN 301 549')
        ->set('criterionNumber', '9.1.1')
        ->set('criterionLevel', 'Applicable')
        ->set('criterionNameEn', 'Web content accessibility')
        ->set('criterionNameSv', 'Tillgänglighet för webbinnehåll')
        ->set('criterionDescriptionEn', 'A European accessibility requirement.')
        ->set('criterionDescriptionSv', 'Ett europeiskt tillgänglighetskrav.')
        ->set('criterionUrl', 'https://www.etsi.org/standards')
        ->call('saveCriterion');

    $criterion = WcagSuccessCriterion::query()
        ->where('standard', 'EN 301 549')
        ->where('number', '9.1.1')
        ->firstOrFail();

    expect($criterion->level)->toBe('Applicable');

    $component
        ->call('editCriterion', $criterion->id)
        ->set('criterionNameSv', 'Webbinnehållets tillgänglighet')
        ->call('saveCriterion');

    expect($criterion->fresh()->name_sv)->toBe('Webbinnehållets tillgänglighet');
});

test('knowledge base resources can be created and edited', function () {
    $criterion = WcagSuccessCriterion::create([
        'number' => '1.1.1',
        'level' => 'A',
        'name_en' => 'Non-text Content',
        'name_sv' => 'Icke-textinnehåll',
        'description_en' => 'Text alternative',
        'description_sv' => 'Textalternativ',
        'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content',
    ]);

    $component = Livewire::actingAs(User::factory()->create())
        ->test(WcagKnowledgeBase::class)
        ->call('selectCriterion', $criterion->id)
        ->call('startCreatingResource')
        ->set('resourceType', 'resource_link')
        ->set('resourceTitleEn', 'DIGG guidance')
        ->set('resourceTitleSv', 'DIGG-vägledning')
        ->set('resourceDescriptionEn', 'Guidance')
        ->set('resourceDescriptionSv', 'Vägledning')
        ->set('resourceUrl', 'https://www.digg.se/webbriktlinjer/alla-webbriktlinjer')
        ->call('saveResource');

    $resource = WcagRelatedResource::query()
        ->where('wcag_success_criterion_id', $criterion->id)
        ->firstOrFail();

    $component
        ->call('editResource', $resource->id)
        ->set('resourceTitleSv', 'Uppdaterad DIGG-vägledning')
        ->call('saveResource');

    expect($resource->fresh()->title_sv)->toBe('Uppdaterad DIGG-vägledning');
});

test('wcag seeder uses canonical w3c understanding links', function () {
    app(WcagSuccessCriteriaSeeder::class)->run();

    expect(WcagSuccessCriterion::query()->where('standard', 'WCAG 2.1')->count())->toBe(50)
        ->and(WcagSuccessCriterion::query()->where('standard', 'WCAG 2.2')->count())->toBe(58)
        ->and(WcagSuccessCriterion::query()
            ->where('standard', 'WCAG 2.2')
            ->where('number', '4.1.1')
            ->exists())->toBeFalse()
        ->and(WcagSuccessCriterion::query()
            ->where('standard', 'WCAG 2.2')
            ->where('number', '3.3.8')
            ->exists())->toBeTrue()
        ->and(WcagSuccessCriterion::query()->where('standard', 'EN 301 549')->count())->toBe(27)
        ->and(WcagSuccessCriterion::wcag()->where('number', '5.2')->exists())->toBeFalse()
        ->and(WcagSuccessCriterion::query()->where('url', 'like', '%.html')->count())->toBe(0)
        ->and(WcagSuccessCriterion::query()->where('url', 'like', 'https://www.w3.org/WAI/WCAG21/Understanding/%')->count())->toBe(50)
        ->and(WcagSuccessCriterion::query()->where('url', 'like', 'https://www.w3.org/WAI/WCAG22/Understanding/%')->count())->toBe(58);
});

test('related resources cover WCAG 2.2 and standalone EN 301 549 requirements', function () {
    app(WcagSuccessCriteriaSeeder::class)->run();
    app(WcagRelatedResourcesSeeder::class)->run();

    $wcag22Criterion = WcagSuccessCriterion::query()
        ->where('standard', 'WCAG 2.2')
        ->where('number', '3.3.8')
        ->firstOrFail();
    $enCriterion = WcagSuccessCriterion::query()
        ->where('standard', 'EN 301 549')
        ->where('number', '5.2')
        ->firstOrFail();

    expect($wcag22Criterion->relatedResources)
        ->toHaveCount(1)
        ->and($wcag22Criterion->relatedResources->first()->url)
        ->toContain('/WAI/WCAG22/Understanding/accessible-authentication-minimum')
        ->and($enCriterion->relatedResources)
        ->toHaveCount(1)
        ->and($enCriterion->relatedResources->first()->url)
        ->toContain('en_301549v040101p.pdf');
});
