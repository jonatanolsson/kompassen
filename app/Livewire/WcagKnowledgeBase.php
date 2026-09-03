<?php

namespace App\Livewire;

use App\Models\WcagCriterionExample;
use App\Models\WcagSuccessCriterion;
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
    public string $exampleUrl = '';
    public string $exampleUrlLabel = '';

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

        return WcagSuccessCriterion::with('examples')->find($this->selectedCriterionId);
    }

    public function selectCriterion(string $id): void
    {
        $this->selectedCriterionId = $this->selectedCriterionId === $id ? null : $id;
        $this->resetExampleForm();
    }

    public function saveExample(): void
    {
        $this->validate([
            'exampleTitle'        => ['required', 'string', 'max:255'],
            'exampleDescription'  => ['nullable', 'string'],
            'exampleCode'         => ['nullable', 'string'],
            'exampleCodeLanguage' => ['nullable', 'string', 'max:50'],
            'exampleUrl'          => ['nullable', 'url', 'max:500'],
            'exampleUrlLabel'     => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'title'        => $this->exampleTitle,
            'description'  => $this->exampleDescription ?: null,
            'code_snippet' => $this->exampleCode ?: null,
            'code_language' => $this->exampleCodeLanguage ?: 'html',
            'url'          => $this->exampleUrl ?: null,
            'url_label'    => $this->exampleUrlLabel ?: null,
        ];

        if ($this->editingExampleId) {
            WcagCriterionExample::find($this->editingExampleId)?->update($data);
        } else {
            WcagCriterionExample::create([
                ...$data,
                'wcag_success_criterion_id' => $this->selectedCriterionId,
            ]);
        }

        $this->resetExampleForm();
        unset($this->selectedCriterion);
        $this->dispatch('toast', message: 'Example saved.', variant: 'success');
    }

    public function editExample(string $id): void
    {
        $example = WcagCriterionExample::find($id);
        if (! $example) {
            return;
        }

        $this->editingExampleId    = $id;
        $this->exampleTitle        = $example->title;
        $this->exampleDescription  = $example->description ?? '';
        $this->exampleCode         = $example->code_snippet ?? '';
        $this->exampleCodeLanguage = $example->code_language ?? 'html';
        $this->exampleUrl          = $example->url ?? '';
        $this->exampleUrlLabel     = $example->url_label ?? '';
    }

    public function deleteExample(string $id): void
    {
        WcagCriterionExample::find($id)?->delete();
        unset($this->selectedCriterion);
        $this->dispatch('toast', message: 'Example deleted.', variant: 'success');
    }

    public function cancelEdit(): void
    {
        $this->resetExampleForm();
    }

    private function resetExampleForm(): void
    {
        $this->editingExampleId    = null;
        $this->exampleTitle        = '';
        $this->exampleDescription  = '';
        $this->exampleCode         = '';
        $this->exampleCodeLanguage = 'html';
        $this->exampleUrl          = '';
        $this->exampleUrlLabel     = '';
    }

    public function render()
    {
        return view('livewire.wcag-knowledge-base');
    }
}
