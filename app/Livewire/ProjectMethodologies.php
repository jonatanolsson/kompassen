<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use App\Models\TestingMethodology;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ProjectMethodologies extends Component
{
    public AccessibilityProject $project;

    public bool $showAddForm = false;

    #[Validate('required|exists:testing_methodologies,id')]
    public string $selectedMethodologyId = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    public function addMethodology(): void
    {
        $this->validate();

        $this->project->methodologies()->syncWithoutDetaching([
            $this->selectedMethodologyId => ['notes' => $this->notes ?: null],
        ]);

        $this->reset(['selectedMethodologyId', 'notes', 'showAddForm']);
    }

    public function removeMethodology(string $methodologyId): void
    {
        $this->project->methodologies()->detach($methodologyId);
    }

    public function updateNotes(string $methodologyId, string $notes): void
    {
        $this->project->methodologies()->updateExistingPivot($methodologyId, [
            'notes' => $notes ?: null,
        ]);
    }

    public function render()
    {
        $allMethodologies = TestingMethodology::orderBy('category')->orderBy('name')->get()
            ->groupBy('category');

        $projectMethodologyIds = $this->project->methodologies->pluck('id')->toArray();

        return view('livewire.project-methodologies', [
            'allMethodologies' => $allMethodologies,
            'projectMethodologies' => $this->project->methodologies()->orderBy('category')->orderBy('name')->get(),
            'projectMethodologyIds' => $projectMethodologyIds,
        ]);
    }
}