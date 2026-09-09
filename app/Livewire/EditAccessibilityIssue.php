<?php

namespace App\Livewire;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\WcagSuccessCriterion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditAccessibilityIssue extends Component
{
    use AuthorizesRequests;

    public AccessibilityProject $project;
    public AccessibilityIssue $issue;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|exists:accessibility_pages,id')]
    public ?string $page_id = null;

    #[Validate('nullable|string|max:255')]
    public string $component_area = '';

    #[Validate('required|in:critical,major,moderate,minor')]
    public string $severity = 'major';

    #[Validate('required|in:easy,medium,hard')]
    public string $difficulty = 'medium';

    #[Validate('required|in:open,resolved,wont_fix')]
    public string $status = 'open';

    public array $selectedCriteria = [];

    public function mount(AccessibilityProject $project, AccessibilityIssue $issue): void
    {
        $this->authorize('update', $project);

        $this->project = $project;
        $this->issue = $issue;

        $this->title = $issue->title;
        $this->description = $issue->description;
        $this->page_id = $issue->page_id;
        $this->component_area = $issue->component_area;
        $this->severity = $issue->severity;
        $this->difficulty = $issue->difficulty;
        $this->status = $issue->status;

        $this->selectedCriteria = $issue->wcagCriteria()
            ->get()
            ->map(fn ($criterion) => [
                'id' => $criterion->id,
                'failure_type' => $criterion->pivot->failure_type ?? '',
                'comment' => $criterion->pivot->comment ?? '',
                'code_snippet' => $criterion->pivot->code_snippet ?? '',
            ])
            ->values()
            ->toArray();
    }

    public function update(): void
    {
        $this->validate();

        $this->issue->update([
            'title' => $this->title,
            'description' => $this->description,
            'page_id' => $this->page_id,
            'component_area' => $this->component_area,
            'severity' => $this->severity,
            'difficulty' => $this->difficulty,
            'status' => $this->status,
        ]);

        // Update WCAG criteria
        if (! empty($this->selectedCriteria)) {
            $syncData = [];
            foreach ($this->selectedCriteria as $criterion) {
                $syncData[$criterion['id']] = [
                    'failure_type' => $criterion['failure_type'] ?? null,
                    'comment' => $criterion['comment'] ?? null,
                    'code_snippet' => $criterion['code_snippet'] ?? null,
                ];
            }
            $this->issue->wcagCriteria()->sync($syncData);
        } else {
            $this->issue->wcagCriteria()->detach();
        }

        session()->flash('success', __('Issue updated successfully.'));
        $this->redirect(route('accessibility-projects.show', $this->project), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('accessibility-projects.show', $this->project), navigate: true);
    }

    public function render()
    {
        return view('livewire.edit-accessibility-issue', [
            'project' => $this->project,
            'issue' => $this->issue,
            'wcagCriteria' => WcagSuccessCriterion::all(),
        ]);
    }
}

