<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Services\ReportSiteIcon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateAccessibilityProject extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $projectId = '';

    public string $name = '';

    public string $description = '';

    public string $target_wcag_level = 'AA';

    public string $target_wcag_version = '2.1';

    public ?string $audit_date = null;

    public ?string $report_site_url = null;

    public $client_logo;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_wcag_level' => 'required|in:A,AA,AAA',
            'target_wcag_version' => ['required', Rule::in(AccessibilityProject::WCAG_VERSIONS)],
            'audit_date' => 'nullable|date',
            'report_site_url' => ReportSiteIcon::primarySiteRules(),
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
            'target_wcag_version' => $validated['target_wcag_version'],
            'audit_date' => $validated['audit_date'],
            'report_site_url' => ReportSiteIcon::origin($validated['report_site_url'] ?? null),
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
