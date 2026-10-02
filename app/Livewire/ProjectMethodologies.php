<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use App\Models\TestingMethodology;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProjectMethodologies extends Component
{
    use AuthorizesRequests;

    public AccessibilityProject $project;

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
        $this->validate([
            'selectedMethodologyId' => [
                'required',
                Rule::exists('testing_methodologies', 'id')
                    ->where(fn ($query) => $query->whereNull('team_id')->orWhere('team_id', $this->project->team_id)),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->project->methodologies()->syncWithoutDetaching([
            $this->selectedMethodologyId => [
                'id' => (string) Str::ulid(),
                'notes' => $this->notes ?: null,
            ],
        ]);

        $this->reset(['selectedMethodologyId', 'notes']);
        $this->dispatch('close-modal', name: 'add-methodology-modal');
        $this->dispatch('toast', message: __('Testing method added to project.'), variant: 'success');
    }

    public function cancelAddMethodology(): void
    {
        $this->reset(['selectedMethodologyId', 'notes']);
        $this->resetValidation();
    }

    public function cancelNotesEdit(): void
    {
        $this->resetValidation('notes');
    }

    public function removeMethodology(string $methodologyId): void
    {
        $this->authorize('update', $this->project);
        $this->project->methodologies()->availableToTeam((string) $this->project->team_id)
            ->wherePivot('methodology_id', $methodologyId)->firstOrFail();
        $this->project->methodologies()->detach($methodologyId);
        $this->dispatch('close-modal', name: 'remove-project-methodology-'.$methodologyId);
        $this->dispatch('toast', message: __('Testing method removed from project.'), variant: 'success');
    }

    public function updateNotes(string $methodologyId, string $notes): bool
    {
        $this->authorize('update', $this->project);
        $this->project->methodologies()->availableToTeam((string) $this->project->team_id)
            ->wherePivot('methodology_id', $methodologyId)->firstOrFail();

        $validator = Validator::make(
            ['notes' => $notes],
            ['notes' => ['nullable', 'string', 'max:1000']],
        );

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());

            return false;
        }

        $this->resetValidation('notes');

        $this->project->methodologies()->updateExistingPivot($methodologyId, [
            'notes' => $notes ?: null,
        ]);

        return true;
    }

    public function render()
    {
        $allMethodologies = TestingMethodology::availableToTeam($this->project->team_id)
            ->orderBy('category')->orderBy('name')->get()
            ->groupBy('category');

        $projectMethodologies = $this->project->methodologies()->availableToTeam($this->project->team_id)
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
