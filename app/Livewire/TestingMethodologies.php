<?php

namespace App\Livewire;

use App\Models\TestingMethodology;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TestingMethodologies extends Component
{
    use AuthorizesRequests;

    public ?string $editingMethodologyId = null;

    public string $name = '';

    public string $category = 'screen_reader';

    public string $description = '';

    public function mount(): void
    {
        $this->authorize('viewAny', TestingMethodology::class);
    }

    #[Computed]
    public function methodologies(): Collection
    {
        return TestingMethodology::query()
            ->availableToTeam((string) auth()->user()->team_id)
            ->withCount(['projects' => fn (Builder $query) => $query->where('accessibility_projects.team_id', (string) auth()->user()->team_id)])
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }

    public function categoryOptions(): array
    {
        return [
            'screen_reader' => __('Screen Readers'),
            'browser' => __('Browsers'),
            'browser_extension' => __('Browser Extensions'),
            'device' => __('Devices'),
            'testing_tool' => __('Testing Tools'),
        ];
    }

    public function openCreateForm(): void
    {
        $this->authorize('create', TestingMethodology::class);
        $this->resetForm();
    }

    public function edit(string $id): void
    {
        $methodology = TestingMethodology::availableToTeam((string) auth()->user()->team_id)->findOrFail($id);
        $this->authorize('update', $methodology);

        $this->editingMethodologyId = $methodology->id;
        $this->name = $methodology->name;
        $this->category = $methodology->category;
        $this->description = $methodology->description ?? '';
    }

    public function save(): void
    {
        $methodology = $this->editingMethodologyId
            ? TestingMethodology::availableToTeam((string) auth()->user()->team_id)->findOrFail($this->editingMethodologyId)
            : null;

        $this->authorize($methodology ? 'update' : 'create', $methodology ?? TestingMethodology::class);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('testing_methodologies', 'name')
                    ->where(fn ($query) => $query->where('team_id', (string) auth()->user()->team_id))
                    ->ignore($methodology?->id),
            ],
            'category' => ['required', Rule::in(array_keys($this->categoryOptions()))],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($methodology) {
            $methodology->update([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?: null,
            ]);
        } else {
            TestingMethodology::create([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?: null,
                'is_custom' => true,
                'team_id' => (string) auth()->user()->team_id,
            ]);
        }

        $this->resetForm();
        unset($this->methodologies);
        $this->dispatch('close-modal', name: 'testing-methodology-form-modal');
        $this->dispatch('toast', message: __('Testing methodology saved.'), variant: 'success');
    }

    public function delete(string $id): void
    {
        $methodology = TestingMethodology::availableToTeam((string) auth()->user()->team_id)->findOrFail($id);
        $this->authorize('delete', $methodology);

        if ($methodology->projects()->exists()) {
            $this->dispatch('toast', message: __('Cannot delete a methodology used by projects.'), variant: 'error');

            return;
        }

        $methodology->delete();
        unset($this->methodologies);

        if ($this->editingMethodologyId === $id) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: __('Testing methodology deleted.'), variant: 'success');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingMethodologyId = null;
        $this->name = '';
        $this->category = 'screen_reader';
        $this->description = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.testing-methodologies');
    }
}
