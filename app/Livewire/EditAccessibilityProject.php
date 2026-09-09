<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditAccessibilityProject extends Component
{
    use AuthorizesRequests;

    public AccessibilityProject $project;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('required|in:wcag2.0-a,wcag2.0-aa,wcag2.0-aaa,wcag2.1-a,wcag2.1-aa,wcag2.1-aaa')]
    public string $target_wcag_level = 'wcag2.1-aa';

    #[Validate('required|in:planning,in-progress,completed')]
    public string $status = 'planning';

    public function mount(AccessibilityProject $project): void
    {
        $this->authorize('update', $project);

        $this->project = $project;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->target_wcag_level = $project->target_wcag_level;
        $this->status = $project->status;
    }

    public function update(): void
    {
        $this->validate();

        $this->project->update([
            'name' => $this->name,
            'description' => $this->description,
            'target_wcag_level' => $this->target_wcag_level,
            'status' => $this->status,
        ]);

        // Handle logo if provided
        if (request()->hasFile('client_logo')) {
            $path = request()->file('client_logo')->store('project-logos', 'public');
            $this->project->update(['client_logo' => $path]);
        }

        session()->flash('success', __('Project updated successfully.'));
        $this->redirect(route('accessibility-projects.show', $this->project), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('accessibility-projects.show', $this->project), navigate: true);
    }

    public function render()
    {
        return view('livewire.edit-accessibility-project', [
            'project' => $this->project,
        ]);
    }
}
