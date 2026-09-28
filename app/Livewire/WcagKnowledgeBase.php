<?php

namespace App\Livewire;

use App\Models\WcagCriterionExample;
use App\Models\WcagCriterionExampleAttachment;
use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class WcagKnowledgeBase extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?string $selectedCriterionId = null;

    public string $search = '';

    public string $standardFilter = '';

    public ?string $editingCriterionId = null;

    public string $criterionStandard = 'WCAG 2.1';

    public string $criterionNumber = '';

    public string $criterionLevel = 'A';

    public string $criterionNameEn = '';

    public string $criterionNameSv = '';

    public string $criterionDescriptionEn = '';

    public string $criterionDescriptionSv = '';

    public string $criterionUrl = '';

    public ?string $editingResourceId = null;

    public string $resourceType = 'resource_link';

    public string $resourceCode = '';

    public string $resourceTitleEn = '';

    public string $resourceTitleSv = '';

    public string $resourceDescriptionEn = '';

    public string $resourceDescriptionSv = '';

    public string $resourceUrl = '';

    public int $resourceOrder = 0;

    public string $exampleTitle = '';

    public string $exampleDescription = '';

    public string $exampleCode = '';

    public string $exampleCodeLanguage = 'html';

    /**
     * @var array<int, TemporaryUploadedFile>
     */
    public array $exampleAttachments = [];

    /**
     * @var array<int, string>
     */
    public array $exampleAttachmentAltTexts = [];

    /**
     * @var array<int, array{id: string, path: string, original_filename: string, alt_text: ?string}>
     */
    public array $existingExampleAttachments = [];

    /**
     * @var array<int, array{url: string, label: string}>
     */
    public array $exampleLinks = [];

    public ?string $editingExampleId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', WcagSuccessCriterion::class);
    }

    #[Computed]
    public function criteria(): Collection
    {
        return WcagSuccessCriterion::query()
            ->when($this->standardFilter !== '', fn (Builder $query): Builder => $query->where('standard', $this->standardFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.trim($this->search).'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('number', 'like', $search)
                        ->orWhere('standard', 'like', $search)
                        ->orWhere('name_en', 'like', $search)
                        ->orWhere('name_sv', 'like', $search);
                });
            })
            ->orderBy('standard')
            ->orderBy('number')
            ->withCount('examples')
            ->get()
            ->groupBy('standard');
    }

    #[Computed]
    public function standards(): array
    {
        return WcagSuccessCriterion::query()
            ->select('standard')
            ->distinct()
            ->orderBy('standard')
            ->pluck('standard')
            ->all();
    }

    #[Computed]
    public function selectedCriterion(): ?WcagSuccessCriterion
    {
        if (! $this->selectedCriterionId) {
            return null;
        }

        return WcagSuccessCriterion::with(['examples.referenceLinks', 'examples.attachments', 'relatedResources'])
            ->find($this->selectedCriterionId);
    }

    public function selectCriterion(string $id): void
    {
        $this->selectedCriterionId = $this->selectedCriterionId === $id ? null : $id;
        $this->resetExampleForm();
        $this->resetResourceForm();
    }

    public function startCreatingCriterion(): void
    {
        $this->authorize('create', WcagSuccessCriterion::class);
        $this->resetCriterionForm();
        $this->resetValidation();
    }

    public function editCriterion(string $id): void
    {
        $criterion = WcagSuccessCriterion::query()->findOrFail($id);
        $this->authorize('update', $criterion);

        $this->editingCriterionId = $criterion->id;
        $this->criterionStandard = $criterion->standard;
        $this->criterionNumber = $criterion->number;
        $this->criterionLevel = $criterion->level;
        $this->criterionNameEn = $criterion->name_en;
        $this->criterionNameSv = $criterion->name_sv;
        $this->criterionDescriptionEn = $criterion->description_en;
        $this->criterionDescriptionSv = $criterion->description_sv;
        $this->criterionUrl = $criterion->url;
        $this->resetValidation();
    }

    public function saveCriterion(): void
    {
        $criterion = $this->editingCriterionId
            ? WcagSuccessCriterion::query()->findOrFail($this->editingCriterionId)
            : null;

        $this->authorize($criterion ? 'update' : 'create', $criterion ?? WcagSuccessCriterion::class);

        $validated = $this->validate([
            'criterionStandard' => ['required', 'string', 'max:100'],
            'criterionNumber' => [
                'required',
                'string',
                'max:50',
                Rule::unique('wcag_success_criteria', 'number')
                    ->where(fn ($query) => $query->where('standard', $this->criterionStandard))
                    ->ignore($criterion?->id),
            ],
            'criterionLevel' => ['required', 'string', 'max:50'],
            'criterionNameEn' => ['required', 'string', 'max:255'],
            'criterionNameSv' => ['required', 'string', 'max:255'],
            'criterionDescriptionEn' => ['required', 'string'],
            'criterionDescriptionSv' => ['required', 'string'],
            'criterionUrl' => ['required', 'url', 'max:500'],
        ]);

        $data = [
            'standard' => trim($validated['criterionStandard']),
            'number' => trim($validated['criterionNumber']),
            'level' => trim($validated['criterionLevel']),
            'name_en' => trim($validated['criterionNameEn']),
            'name_sv' => trim($validated['criterionNameSv']),
            'description_en' => $validated['criterionDescriptionEn'],
            'description_sv' => $validated['criterionDescriptionSv'],
            'url' => $validated['criterionUrl'],
        ];

        if ($criterion) {
            $criterion->update($data);
            $savedCriterionId = $criterion->id;
        } else {
            $savedCriterionId = WcagSuccessCriterion::create($data)->id;
        }

        $this->selectedCriterionId = $savedCriterionId;
        $this->resetCriterionForm();
        unset($this->criteria, $this->standards, $this->selectedCriterion);
        $this->dispatch('close-modal', name: 'criterion-form-modal');
        $this->dispatch('toast', message: __('Knowledge base entry saved.'), variant: 'success');
    }

    public function deleteCriterion(string $id): void
    {
        $criterion = WcagSuccessCriterion::query()->findOrFail($id);
        $this->authorize('delete', $criterion);

        if ($criterion->issues()->exists()) {
            $this->dispatch('toast', message: __('Cannot delete a criterion used by issues.'), variant: 'error');

            return;
        }

        $criterion->load('examples.attachments');
        foreach ($criterion->examples as $example) {
            foreach ($example->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->path);
            }
        }

        $criterion->delete();
        unset($this->criteria, $this->standards, $this->selectedCriterion);

        if ($this->selectedCriterionId === $id) {
            $this->selectedCriterionId = null;
        }

        $this->dispatch('toast', message: __('Knowledge base entry deleted.'), variant: 'success');
    }

    public function startCreatingResource(): void
    {
        $this->authorize('update', $this->selectedCriterionModel());
        $this->resetResourceForm();
        $this->resetValidation();
    }

    public function editResource(string $id): void
    {
        $criterion = $this->selectedCriterionModel();
        $this->authorize('update', $criterion);

        $resource = $criterion->relatedResources()->whereKey($id)->firstOrFail();
        $this->editingResourceId = $resource->id;
        $this->resourceType = $resource->type;
        $this->resourceCode = $resource->code ?? '';
        $this->resourceTitleEn = $resource->title_en;
        $this->resourceTitleSv = $resource->title_sv;
        $this->resourceDescriptionEn = $resource->description_en ?? '';
        $this->resourceDescriptionSv = $resource->description_sv ?? '';
        $this->resourceUrl = $resource->url ?? '';
        $this->resourceOrder = $resource->order;
        $this->resetValidation();
    }

    public function saveResource(): void
    {
        $criterion = $this->selectedCriterionModel();
        $this->authorize('update', $criterion);

        $resource = $this->editingResourceId
            ? $criterion->relatedResources()->whereKey($this->editingResourceId)->firstOrFail()
            : null;

        $validated = $this->validate([
            'resourceType' => ['required', Rule::in(array_keys($this->resourceTypeOptions()))],
            'resourceCode' => ['nullable', 'string', 'max:50'],
            'resourceTitleEn' => ['required', 'string', 'max:255'],
            'resourceTitleSv' => ['required', 'string', 'max:255'],
            'resourceDescriptionEn' => ['nullable', 'string'],
            'resourceDescriptionSv' => ['nullable', 'string'],
            'resourceUrl' => ['required', 'url', 'max:500'],
            'resourceOrder' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $data = [
            'type' => $validated['resourceType'],
            'code' => $validated['resourceCode'] ?: null,
            'title_en' => trim($validated['resourceTitleEn']),
            'title_sv' => trim($validated['resourceTitleSv']),
            'description_en' => $validated['resourceDescriptionEn'] ?: null,
            'description_sv' => $validated['resourceDescriptionSv'] ?: null,
            'url' => $validated['resourceUrl'],
            'order' => $validated['resourceOrder'],
        ];

        if ($resource) {
            $resource->update($data);
        } else {
            $criterion->relatedResources()->create($data);
        }

        $this->resetResourceForm();
        unset($this->selectedCriterion);
        $this->dispatch('close-modal', name: 'resource-form-modal');
        $this->dispatch('toast', message: __('Related resource saved.'), variant: 'success');
    }

    public function deleteResource(string $id): void
    {
        $criterion = $this->selectedCriterionModel();
        $this->authorize('update', $criterion);

        $criterion->relatedResources()->whereKey($id)->firstOrFail()->delete();
        unset($this->selectedCriterion);
        $this->dispatch('toast', message: __('Related resource deleted.'), variant: 'success');
    }

    public function resourceTypeOptions(): array
    {
        return [
            'wai_failure' => __('WAI Failure'),
            'project_issue' => __('Project Issue'),
            'resource_link' => __('Resource Link'),
        ];
    }

    public function startCreatingExample(): void
    {
        $this->authorize('update', $this->selectedCriterionModel());
        $this->resetExampleForm();
        $this->resetValidation();
    }

    public function saveExample(): void
    {
        $criterion = $this->selectedCriterionModel();
        $this->authorize('update', $criterion);

        $this->validate([
            'selectedCriterionId' => ['required', 'exists:wcag_success_criteria,id'],
            'exampleTitle' => ['required', 'string', 'max:255'],
            'exampleDescription' => ['nullable', 'string'],
            'exampleCode' => ['nullable', 'string'],
            'exampleCodeLanguage' => ['nullable', 'string', 'max:50'],
            'exampleAttachments' => ['array', 'max:10'],
            'exampleAttachments.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'exampleAttachmentAltTexts' => ['array'],
            'exampleAttachmentAltTexts.*' => ['required', 'string', 'max:255'],
            'exampleLinks' => ['array', 'max:10'],
            'exampleLinks.*' => ['array'],
            'exampleLinks.*.url' => ['nullable', 'url', 'max:500'],
            'exampleLinks.*.label' => ['nullable', 'string', 'max:255'],
        ]);

        $links = collect($this->exampleLinks)
            ->map(fn (array $link): array => [
                'url' => trim($link['url'] ?? ''),
                'label' => trim($link['label'] ?? ''),
            ])
            ->filter(fn (array $link): bool => $link['url'] !== '')
            ->values();

        $data = [
            'title' => $this->exampleTitle,
            'description' => $this->exampleDescription ?: null,
            'code_snippet' => $this->exampleCode ?: null,
            'code_language' => $this->exampleCodeLanguage ?: 'html',
            'url' => $links->first()['url'] ?? null,
            'url_label' => ($links->first()['label'] ?? null) ?: null,
        ];

        DB::transaction(function () use ($criterion, $data, $links): void {
            if ($this->editingExampleId) {
                $example = WcagCriterionExample::query()
                    ->whereKey($this->editingExampleId)
                    ->whereBelongsTo($criterion, 'criterion')
                    ->firstOrFail();
                $example->update($data);
            } else {
                $example = WcagCriterionExample::create([
                    ...$data,
                    'wcag_success_criterion_id' => $criterion->id,
                ]);
            }

            $example->referenceLinks()->delete();
            $example->referenceLinks()->createMany(
                $links->map(fn (array $link, int $index): array => [
                    ...$link,
                    'sort_order' => $index,
                ])->all()
            );

            foreach ($this->exampleAttachments as $index => $file) {
                $filename = Str::ulid().'.'.$file->extension();
                $path = $file->storeAs('wcag-examples/'.$example->id, $filename, 'public');

                $example->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                    'alt_text' => trim($this->exampleAttachmentAltTexts[$index] ?? ''),
                    'sort_order' => $index,
                ]);
            }
        });

        $this->resetExampleForm();
        unset($this->selectedCriterion);
        $this->dispatch('close-modal', name: 'example-form-modal');
        $this->dispatch('toast', message: __('Example saved.'), variant: 'success');
    }

    public function editExample(string $id): void
    {
        $criterion = $this->selectedCriterionModel();
        $this->authorize('update', $criterion);

        $example = WcagCriterionExample::query()
            ->whereKey($id)
            ->whereBelongsTo($criterion, 'criterion')
            ->with('attachments')
            ->firstOrFail();

        $this->editingExampleId = $id;
        $this->exampleTitle = $example->title;
        $this->exampleDescription = $example->description ?? '';
        $this->exampleCode = $example->code_snippet ?? '';
        $this->exampleCodeLanguage = $example->code_language ?? 'html';
        $this->exampleLinks = $example->referenceLinks
            ->map(fn ($link): array => [
                'url' => $link->url,
                'label' => $link->label ?? '',
            ])
            ->whenEmpty(fn ($links) => $example->url
                ? $links->push([
                    'url' => $example->url,
                    'label' => $example->url_label ?? '',
                ])
                : $links
            )
            ->values()
            ->all();
        $this->existingExampleAttachments = $example->attachments
            ->map(fn (WcagCriterionExampleAttachment $attachment): array => [
                'id' => $attachment->id,
                'path' => $attachment->path,
                'original_filename' => $attachment->original_filename,
                'alt_text' => $attachment->alt_text,
            ])
            ->all();
        $this->exampleAttachments = [];
        $this->exampleAttachmentAltTexts = [];
        $this->resetValidation();
    }

    public function updatedExampleAttachments(): void
    {
        foreach ($this->exampleAttachments as $index => $file) {
            $this->exampleAttachmentAltTexts[$index] ??= '';
        }
    }

    public function addExampleLink(): void
    {
        $this->exampleLinks[] = ['url' => '', 'label' => ''];
    }

    public function removeExampleLink(int $index): void
    {
        unset($this->exampleLinks[$index]);
        $this->exampleLinks = array_values($this->exampleLinks);
    }

    public function deleteExample(string $id): void
    {
        $criterion = $this->selectedCriterionModel();
        $this->authorize('update', $criterion);

        $example = WcagCriterionExample::query()
            ->whereKey($id)
            ->whereBelongsTo($criterion, 'criterion')
            ->with('attachments')
            ->firstOrFail();

        foreach ($example->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
        }

        $example->delete();
        unset($this->selectedCriterion);
        $this->dispatch('toast', message: __('Example deleted.'), variant: 'success');
    }

    public function deleteExampleAttachment(string $id): void
    {
        $criterion = $this->selectedCriterionModel();
        $this->authorize('update', $criterion);

        $example = WcagCriterionExample::query()
            ->whereKey($this->editingExampleId)
            ->whereBelongsTo($criterion, 'criterion')
            ->firstOrFail();
        $attachment = $example->attachments()->whereKey($id)->firstOrFail();

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        $this->existingExampleAttachments = array_values(array_filter(
            $this->existingExampleAttachments,
            fn (array $existing): bool => $existing['id'] !== $id
        ));
        unset($this->selectedCriterion);
    }

    public function cancelEdit(): void
    {
        $this->resetExampleForm();
    }

    private function selectedCriterionModel(): WcagSuccessCriterion
    {
        return WcagSuccessCriterion::query()->findOrFail($this->selectedCriterionId);
    }

    public function resetCriterionForm(): void
    {
        $this->editingCriterionId = null;
        $this->criterionStandard = 'WCAG 2.1';
        $this->criterionNumber = '';
        $this->criterionLevel = 'A';
        $this->criterionNameEn = '';
        $this->criterionNameSv = '';
        $this->criterionDescriptionEn = '';
        $this->criterionDescriptionSv = '';
        $this->criterionUrl = '';
        $this->resetValidation();
    }

    public function resetResourceForm(): void
    {
        $this->editingResourceId = null;
        $this->resourceType = 'resource_link';
        $this->resourceCode = '';
        $this->resourceTitleEn = '';
        $this->resourceTitleSv = '';
        $this->resourceDescriptionEn = '';
        $this->resourceDescriptionSv = '';
        $this->resourceUrl = '';
        $this->resourceOrder = 0;
        $this->resetValidation();
    }

    private function resetExampleForm(): void
    {
        $this->editingExampleId = null;
        $this->exampleTitle = '';
        $this->exampleDescription = '';
        $this->exampleCode = '';
        $this->exampleCodeLanguage = 'html';
        $this->exampleAttachments = [];
        $this->exampleAttachmentAltTexts = [];
        $this->existingExampleAttachments = [];
        $this->exampleLinks = [];
    }

    public function render()
    {
        return view('livewire.wcag-knowledge-base');
    }
}
