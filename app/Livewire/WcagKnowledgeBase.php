<?php

namespace App\Livewire;

use App\Models\WcagCriterionExample;
use App\Models\WcagSuccessCriterion;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WcagKnowledgeBase extends Component
{
    public ?string $selectedCriterionId = null;

    public string $search = '';

    // Example form fields
    public string $exampleTitle = '';

    public string $exampleDescription = '';

    public string $exampleCode = '';

    public string $exampleCodeLanguage = 'html';

    /**
     * @var array<int, array{url: string, label: string}>
     */
    public array $exampleLinks = [];

    public ?string $editingExampleId = null;

    #[Computed]
    public function criteria()
    {
        return WcagSuccessCriterion::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('number', 'like', "%{$this->search}%")
                        ->orWhere('name_en', 'like', "%{$this->search}%")
                        ->orWhere('name_sv', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('number')
            ->withCount('examples')
            ->get()
            ->groupBy(fn ($c) => explode('.', $c->number)[0]);
    }

    #[Computed]
    public function selectedCriterion()
    {
        if (! $this->selectedCriterionId) {
            return null;
        }

        return WcagSuccessCriterion::with(['examples.referenceLinks', 'relatedResources'])
            ->find($this->selectedCriterionId);
    }

    public function selectCriterion(string $id): void
    {
        $this->selectedCriterionId = $this->selectedCriterionId === $id ? null : $id;
        $this->resetExampleForm();
    }

    public function startCreatingExample(): void
    {
        $this->resetExampleForm();
        $this->resetValidation();
    }

    public function saveExample(): void
    {
        $this->validate([
            'selectedCriterionId' => ['required', 'exists:wcag_success_criteria,id'],
            'exampleTitle' => ['required', 'string', 'max:255'],
            'exampleDescription' => ['nullable', 'string'],
            'exampleCode' => ['nullable', 'string'],
            'exampleCodeLanguage' => ['nullable', 'string', 'max:50'],
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
            'url_label' => $links->first()['label'] ?: null,
        ];

        DB::transaction(function () use ($data, $links): void {
            if ($this->editingExampleId) {
                $example = WcagCriterionExample::query()
                    ->whereKey($this->editingExampleId)
                    ->where('wcag_success_criterion_id', $this->selectedCriterionId)
                    ->firstOrFail();
                $example->update($data);
            } else {
                $example = WcagCriterionExample::create([
                    ...$data,
                    'wcag_success_criterion_id' => $this->selectedCriterionId,
                ]);
            }

            $example->referenceLinks()->delete();
            $example->referenceLinks()->createMany(
                $links->map(fn (array $link, int $index): array => [
                    ...$link,
                    'sort_order' => $index,
                ])->all()
            );
        });

        $this->resetExampleForm();
        unset($this->selectedCriterion);
        $this->dispatch('close-modal', name: 'example-form-modal');
        $this->dispatch('toast', message: __('Example saved.'), variant: 'success');
    }

    public function editExample(string $id): void
    {
        $example = WcagCriterionExample::query()
            ->whereKey($id)
            ->where('wcag_success_criterion_id', $this->selectedCriterionId)
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
        WcagCriterionExample::query()
            ->whereKey($id)
            ->where('wcag_success_criterion_id', $this->selectedCriterionId)
            ->firstOrFail()
            ->delete();
        unset($this->selectedCriterion);
        $this->dispatch('toast', message: __('Example deleted.'), variant: 'success');
    }

    public function cancelEdit(): void
    {
        $this->resetExampleForm();
    }

    private function resetExampleForm(): void
    {
        $this->editingExampleId = null;
        $this->exampleTitle = '';
        $this->exampleDescription = '';
        $this->exampleCode = '';
        $this->exampleCodeLanguage = 'html';
        $this->exampleLinks = [];
    }

    public function render()
    {
        return view('livewire.wcag-knowledge-base');
    }
}
