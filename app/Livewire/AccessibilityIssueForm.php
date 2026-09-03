<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use App\Models\AccessibilityIssue;
use App\Models\WcagSuccessCriterion;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

class AccessibilityIssueForm extends Component
{
    use WithFileUploads;

    public AccessibilityProject $project;
    public ?AccessibilityIssue $issue = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|exists:accessibility_pages,id')]
    public ?string $page_id = null;

    #[Validate('required|in:critical,major,moderate,minor')]
    public string $severity = 'major';

    #[Validate('required|in:easy,medium,hard')]
    public string $difficulty = 'medium';

    #[Validate('nullable|string|max:255')]
    public string $component_area = '';

    #[Validate('required|in:open,resolved,wont_fix')]
    public string $status = 'open';

    #[Validate('nullable|array')]
    public array $wcag_criteria = [];

    #[Validate('nullable|array')]
    public array $attachments = [];

    public array $selectedCriteria = [];

    public function mount(): void
    {
        if ($this->issue) {
            $this->title = $this->issue->title;
            $this->description = $this->issue->description ?? '';
            $this->page_id = $this->issue->page_id;
            $this->severity = $this->issue->severity;
            $this->difficulty = $this->issue->difficulty;
            $this->component_area = $this->issue->component_area ?? '';
            $this->status = $this->issue->status;
            $this->selectedCriteria = $this->issue->wcagCriteria()->pluck('wcag_success_criterion_id')->toArray();
        }
    }

    public function submit(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'page_id' => $this->page_id,
            'severity' => $this->severity,
            'difficulty' => $this->difficulty,
            'component_area' => $this->component_area,
            'status' => $this->status,
        ];

        if ($this->issue) {
            $this->issue->update($data);
            if (! empty($this->selectedCriteria)) {
                $this->issue->wcagCriteria()->sync($this->selectedCriteria);
            }
            $this->storeAttachments($this->issue);
            $this->redirect(route('accessibility-issues.show', [$this->project, $this->issue]));
        } else {
            $issue = $this->project->issues()->create($data);
            if (! empty($this->selectedCriteria)) {
                $issue->wcagCriteria()->sync($this->selectedCriteria);
            }
            $this->storeAttachments($issue);
            $this->redirect(route('accessibility-projects.show', $this->project));
        }
    }

    private function storeAttachments(AccessibilityIssue $issue): void
    {
        if (! empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $filename = \Illuminate\Support\Str::ulid() . '.' . $file->extension();
                $path = $file->storeAs('accessibility-issues/' . $issue->id, $filename, 'public');

                $issue->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                ]);
            }
        }
    }

    public function render()
    {
        $wcagCriteria = WcagSuccessCriterion::all();

        return view('livewire.accessibility-issue-form', [
            'wcagCriteria' => $wcagCriteria,
        ]);
    }
}
