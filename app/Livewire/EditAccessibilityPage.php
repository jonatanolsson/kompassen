<?php

namespace App\Livewire;

use App\Models\AccessibilityPage;
use App\Models\AccessibilityProject;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class EditAccessibilityPage extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public string $projectId = '';

    #[Locked]
    public string $pageId = '';

    public string $name = '';

    public string $resourceType = 'page';

    public string $url = '';

    public string $accessContext = 'not_applicable';

    public string $description = '';

    public string $scope = 'in_scope';

    public string $scopeExclusionReason = '';

    public function mount(AccessibilityProject $project, AccessibilityPage $page): void
    {
        $this->authorize('update', $project);

        $page = $project->pages()->findOrFail($page->getKey());

        $this->projectId = $project->id;
        $this->pageId = $page->id;
        $this->name = $page->name;
        $this->resourceType = $page->resource_type ?? 'page';
        $this->url = $page->url ?? '';
        $this->accessContext = $page->access_context ?? 'not_applicable';
        $this->description = $page->description ?? '';
        $this->scope = $page->scope ?? 'in_scope';
        $this->scopeExclusionReason = $page->scope_exclusion_reason ?? '';
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
            'scopeExclusionReason' => 'required_if:scope,out_of_scope|string|max:10000',
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();
        $project = $this->authorizedProject();
        $page = $project->pages()->findOrFail($this->pageId);

        $page->update([
            'name' => $validated['name'],
            'resource_type' => $validated['resourceType'],
            'url' => $validated['url'],
            'access_context' => $validated['accessContext'],
            'description' => $validated['description'],
            'scope' => $validated['scope'],
            'scope_exclusion_reason' => $validated['scope'] === 'out_of_scope' ? $validated['scopeExclusionReason'] : null,
        ]);

        session()->flash('success', __('Page or service updated successfully.'));
        $this->redirect(route('accessibility-projects.show', $project), navigate: true);
    }

    public function render(): View
    {
        $project = $this->authorizedProject();
        $page = $project->pages()->findOrFail($this->pageId);

        return view('livewire.edit-accessibility-page', compact('project', 'page'));
    }

    private function authorizedProject(): AccessibilityProject
    {
        $project = AccessibilityProject::findOrFail($this->projectId);
        $this->authorize('update', $project);

        return $project;
    }
}
