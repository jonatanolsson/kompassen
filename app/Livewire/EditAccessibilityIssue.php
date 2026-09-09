<?php

namespace App\Livewire;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\WcagSuccessCriterion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class EditAccessibilityIssue extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    #[Locked]
    public string $projectId;

    #[Locked]
    public string $issueId;

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

    public bool $wcagModalOpen = false;

    public string $wcagSearch = '';

    #[Validate('nullable|array')]
    public array $attachments = [];

    public function mount(AccessibilityProject $project, AccessibilityIssue $issue): void
    {
        $this->authorize('update', $project);

        $this->projectId = $project->id;
        $this->issueId = $issue->id;
        $this->title = $issue->title;
        $this->description = $issue->description;
        $this->page_id = $issue->page_id;
        $this->component_area = $issue->component_area;
        $this->severity = $issue->severity;
        $this->difficulty = $issue->difficulty;
        $this->status = $issue->status;
    }

    public function update(): void
    {
        $this->validate();

        $project = AccessibilityProject::findOrFail($this->projectId);
        $issue = AccessibilityIssue::findOrFail($this->issueId);

        $this->authorize('update', $project);

        $issue->update([
            'title' => $this->title,
            'description' => $this->description,
            'page_id' => $this->page_id,
            'component_area' => $this->component_area,
            'severity' => $this->severity,
            'difficulty' => $this->difficulty,
            'status' => $this->status,
        ]);

        // Handle file uploads
        if (! empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $filename = $file->store('issue-attachments', 'public');
                $issue->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
            $this->attachments = [];
        }

        session()->flash('success', __('Issue updated successfully.'));
        $this->redirect(route('accessibility-projects.show', $project), navigate: true);
    }

    public function cancel(): void
    {
        $project = AccessibilityProject::findOrFail($this->projectId);
        $this->redirect(route('accessibility-projects.show', $project), navigate: true);
    }

    public function openWcagModal(): void
    {
        $this->wcagModalOpen = true;
    }

    public function closeWcagModal(): void
    {
        $this->wcagModalOpen = false;
        $this->wcagSearch = '';
    }

    public function deleteAttachment(string $attachmentId): void
    {
        $issue = AccessibilityIssue::findOrFail($this->issueId);
        $attachment = $issue->attachments()->findOrFail($attachmentId);

        $this->authorize('update', AccessibilityProject::findOrFail($this->projectId));

        Storage::disk('public')->delete($attachment->filename);
        $attachment->delete();
    }

    public function render()
    {
        $pages = AccessibilityProject::findOrFail($this->projectId)
            ->pages()
            ->select('id', 'name')
            ->get()
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
            ->toArray();

        $attachmentsList = AccessibilityIssue::findOrFail($this->issueId)
            ->attachments()
            ->select('id', 'filename', 'original_filename')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'filename' => $a->filename,
                'original_filename' => $a->original_filename,
            ])
            ->toArray();

        $availableCriteria = WcagSuccessCriterion::all()
            ->when($this->wcagSearch, function ($criteria) {
                return $criteria->filter(function ($c) {
                    return str_contains(
                        strtolower($c->number.' '.($c->name_sv ?? '').' '.($c->name_en ?? '')),
                        strtolower($this->wcagSearch)
                    );
                });
            })
            ->map(fn ($c) => [
                'id' => $c->id,
                'number' => $c->number,
                'name_sv' => $c->name_sv,
                'name_en' => $c->name_en,
                'level' => $c->level,
            ])
            ->values()
            ->toArray();

        return view('livewire.edit-accessibility-issue', [
            'pages' => $pages,
            'databaseAttachments' => $attachmentsList,
            'availableCriteria' => $availableCriteria,
        ]);
    }
}
