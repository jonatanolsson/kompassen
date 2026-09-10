<?php

namespace App\Livewire;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\WcagSuccessCriterion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateAccessibilityIssue extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    #[Locked]
    public string $projectId = '';

    public string $title = '';

    public string $description = '';

    public ?string $page_id = null;

    public string $severity = 'major';

    public string $difficulty = 'medium';

    public string $component_area = '';

    public string $status = 'open';

    public array $wcag_criteria = [];

    public array $attachments = [];

    public function mount(AccessibilityProject $project)
    {
        $this->authorize('update', $project);
        $this->projectId = $project->id;
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'page_id' => 'nullable|exists:accessibility_pages,id',
            'severity' => 'required|in:critical,major,moderate,minor',
            'difficulty' => 'required|in:easy,medium,hard',
            'component_area' => 'nullable|string|max:255',
            'status' => 'required|in:open,resolved,wont_fix',
            'wcag_criteria' => 'nullable|array',
            'wcag_criteria.*' => 'exists:wcag_success_criteria,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,webp|max:5120',
        ];
    }

    public function submit()
    {
        $validated = $this->validate();

        $project = AccessibilityProject::findOrFail($this->projectId);
        $this->authorize('update', $project);

        $issue_data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'page_id' => $validated['page_id'],
            'severity' => $validated['severity'],
            'difficulty' => $validated['difficulty'],
            'component_area' => $validated['component_area'],
            'status' => $validated['status'],
        ];

        $issue = $project->issues()->create($issue_data);

        if (! empty($validated['wcag_criteria'])) {
            $issue->wcagCriteria()->sync($validated['wcag_criteria']);
        }

        if (! empty($validated['attachments'])) {
            $this->storeAttachments($validated['attachments'], $issue);
        }

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Issue created successfully.'));
    }

    private function storeAttachments(array $files, AccessibilityIssue $issue): void
    {
        foreach ($files as $file) {
            $filename = Str::ulid().'.'.$file->extension();
            $path = $file->storeAs('accessibility-issues/'.$issue->id, $filename, 'public');

            $issue->attachments()->create([
                'filename' => $filename,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'original_filename' => $file->getClientOriginalName(),
                'path' => $path,
            ]);
        }
    }

    public function render()
    {
        $project = AccessibilityProject::findOrFail($this->projectId);
        $pages = $project->pages;
        $wcagCriteria = WcagSuccessCriterion::all();

        return view('livewire.create-accessibility-issue', compact('project', 'pages', 'wcagCriteria'));
    }
}
