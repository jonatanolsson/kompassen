<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use App\Models\TestingMethodology;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProjectMethodologies extends Component
{
    use AuthorizesRequests;

    public AccessibilityProject $project;

    public bool $showAddForm = false;

    #[Validate('required|exists:testing_methodologies,id')]
    public string $selectedMethodologyId = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    public function mount(AccessibilityProject $project): void
    {
        $this->project = $project;
        $this->authorize('view', $this->project);
    }

    public function addMethodology(): void
    {
        $this->authorize('update', $this->project);
        $this->validate();

        $this->project->methodologies()->syncWithoutDetaching([
            $this->selectedMethodologyId => [
                'id' => (string) Str::ulid(),
                'notes' => $this->notes ?: null,
            ],
        ]);

        $this->reset(['selectedMethodologyId', 'notes']);
    }

    public function removeMethodology(string $methodologyId): void
    {
        $this->authorize('update', $this->project);
        $this->project->methodologies()->detach($methodologyId);
    }

    public function updateNotes(string $methodologyId, string $notes): void
    {
        $this->authorize('update', $this->project);
        Validator::make(
            ['notes' => $notes],
            ['notes' => ['nullable', 'string', 'max:1000']],
        )->validate();

        $this->project->methodologies()->updateExistingPivot($methodologyId, [
            'notes' => $notes ?: null,
        ]);
    }

    public function render()
    {
        $allMethodologies = TestingMethodology::orderBy('category')->orderBy('name')->get()
            ->groupBy('category');

        $projectMethodologies = $this->project->methodologies()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('livewire.project-methodologies', [
            'allMethodologies' => $allMethodologies,
            'projectMethodologies' => $projectMethodologies,
            'projectMethodologyIds' => $projectMethodologies->pluck('id')->all(),
        ]);
    }
}
