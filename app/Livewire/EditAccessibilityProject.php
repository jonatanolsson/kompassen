<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use App\Services\ReportSiteIcon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
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

    #[Validate('nullable|string|max:10000')]
    public string $audit_scope = '';

    #[Validate('nullable|string|max:10000')]
    public string $scope_limitations = '';

    #[Validate('required|in:A,AA,AAA')]
    public string $target_wcag_level = 'AA';

    public string $target_wcag_version = '2.1';

    public ?string $report_site_url = null;

    #[Validate('required|in:planning,in_progress,completed')]
    public string $status = 'planning';

    #[Validate('nullable|image|mimes:png,jpg,jpeg,svg|max:2048')]
    public $client_logo;

    public function mount(AccessibilityProject $project): void
    {
        $this->authorize('update', $project);

        $this->projectId = $project->id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->audit_scope = $project->audit_scope ?? '';
        $this->scope_limitations = $project->scope_limitations ?? '';
        $this->target_wcag_level = $project->target_wcag_level;
        $this->target_wcag_version = $project->target_wcag_version ?? '2.1';
        $this->report_site_url = $project->report_site_url;
        $this->status = $project->status;
    }

    public function update(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audit_scope' => 'nullable|string|max:10000',
            'scope_limitations' => 'nullable|string|max:10000',
            'target_wcag_level' => 'required|in:A,AA,AAA',
            'target_wcag_version' => ['required', Rule::in(AccessibilityProject::WCAG_VERSIONS)],
            'status' => 'required|in:planning,in_progress,completed',
            'client_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'report_site_url' => ReportSiteIcon::primarySiteRules(),
        ]);

        $project = AccessibilityProject::findOrFail($this->projectId);

        $this->authorize('update', $project);

        $project->update([
            'name' => $this->name,
            'description' => $this->description,
            'audit_scope' => $this->audit_scope,
            'scope_limitations' => $this->scope_limitations,
            'target_wcag_level' => $this->target_wcag_level,
            'target_wcag_version' => $this->target_wcag_version,
            'status' => $this->status,
            'report_site_url' => ReportSiteIcon::origin($this->report_site_url),
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
