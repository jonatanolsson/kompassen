<?php

namespace App\Livewire;

use App\Models\ProjectMember;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateAccessibilityProject extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $projectId = '';

    public string $name = '';

    public string $description = '';

    public string $target_wcag_level = 'AA';

    public ?string $audit_date = null;

    public $client_logo;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_wcag_level' => 'required|in:A,AA,AAA',
            'audit_date' => 'nullable|date',
            'client_logo' => 'nullable|image|max:2048',
        ];
    }

    public function submit()
    {
        $validated = $this->validate();

        $project_data = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'target_wcag_level' => $validated['target_wcag_level'],
            'audit_date' => $validated['audit_date'],
        ];

        if ($this->client_logo) {
            $project_data['client_logo'] = $this->client_logo->store('client-logos', 'public');
        }

        $project = auth()->user()->team->accessibilityProjects()->create($project_data);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'role' => 'owner',
        ]);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Project created successfully.'));
    }

    public function render()
    {
        return view('livewire.create-accessibility-project');
    }
}
