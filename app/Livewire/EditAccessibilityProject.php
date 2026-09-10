<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class EditAccessibilityProject extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    #[Locked]
    public string $projectId;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('required|in:A,AA,AAA')]
    public string $target_wcag_level = 'AA';

    #[Validate('required|in:planning,in-progress,completed')]
    public string $status = 'planning';

    #[Validate('nullable|image|mimes:png,jpg,jpeg,svg|max:2048')]
    public $client_logo;

    public function mount(AccessibilityProject $project): void
    {
        $this->authorize('update', $project);

        $this->projectId = $project->id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->target_wcag_level = $project->target_wcag_level;
        $this->status = $project->status;
    }

    public function update(): void
    {
        $this->validate();

        $project = AccessibilityProject::findOrFail($this->projectId);

        $this->authorize('update', $project);

        $project->update([
            'name' => $this->name,
            'description' => $this->description,
            'target_wcag_level' => $this->target_wcag_level,
            'status' => $this->status,
        ]);

        if ($this->client_logo) {
            $path = $this->client_logo->store('project-logos', 'public');
            $project->update(['client_logo' => $path]);
        }

        session()->flash('success', __('Project updated successfully.'));
        $this->redirect(route('accessibility-projects.show', $project), navigate: true);
    }

    public function cancel(): void
    {
        $project = AccessibilityProject::findOrFail($this->projectId);
        $this->redirect(route('accessibility-projects.show', $project), navigate: true);
    }

    public function render()
    {
        $project = AccessibilityProject::findOrFail($this->projectId);

        return view('livewire.edit-accessibility-project', [
            'clientLogo' => $project->client_logo,
        ]);
    }
}
