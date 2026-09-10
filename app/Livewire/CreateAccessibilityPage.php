<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateAccessibilityPage extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public string $projectId = '';

    public string $name = '';

    public string $url = '';

    public string $description = '';

    public function mount(AccessibilityProject $project): void
    {
        $this->authorize('update', $project);
        $this->projectId = $project->id;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'nullable|url',
            'description' => 'nullable|string',
        ];
    }

    public function submit()
    {
        $validated = $this->validate();

        $project = AccessibilityProject::findOrFail($this->projectId);
        $this->authorize('update', $project);

        $project->pages()->create($validated);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Page created successfully.'));
    }

    public function render()
    {
        $project = AccessibilityProject::findOrFail($this->projectId);

        return view('livewire.create-accessibility-page', compact('project'));
    }
}
