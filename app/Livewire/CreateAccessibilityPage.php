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

    public string $resourceType = 'page';

    public string $url = '';

    public string $accessContext = 'not_applicable';

    public string $description = '';

    public string $scope = 'in_scope';

    public function mount(AccessibilityProject $project): void
    {
        $this->authorize('update', $project);
        $this->projectId = $project->id;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'resourceType' => 'required|in:page,service',
            'url' => 'nullable|url',
            'accessContext' => 'required|in:not_applicable,public,authenticated,mixed',
            'description' => 'nullable|string',
            'scope' => 'required|in:in_scope,out_of_scope',
        ];
    }

    public function submit()
    {
        $validated = $this->validate();

        $project = AccessibilityProject::findOrFail($this->projectId);
        $this->authorize('update', $project);

        $project->pages()->create([
            'name' => $validated['name'],
            'resource_type' => $validated['resourceType'],
            'url' => $validated['url'],
            'access_context' => $validated['accessContext'],
            'description' => $validated['description'],
            'scope' => $validated['scope'],
        ]);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Page or service created successfully.'));
    }

    public function render()
    {
        $project = AccessibilityProject::findOrFail($this->projectId);

        return view('livewire.create-accessibility-page', compact('project'));
    }
}
