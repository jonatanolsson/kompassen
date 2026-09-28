<?php

namespace App\Livewire;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\WcagCriterionExample;
use App\Models\WcagSuccessCriterion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    #[Validate('nullable|string|max:20000')]
    public string $mermaid_diagram = '';

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

    public array $wcag_criteria = [];

    public array $example_ids = [];

    public string $exampleSearch = '';

    #[Validate('nullable|array')]
    public array $attachments = [];

    public function mount(AccessibilityProject $project, AccessibilityIssue $issue): void
    {
        $this->authorize('update', $project);

        $this->projectId = $project->id;
        $this->issueId = $issue->id;
        $this->title = $issue->title;
        $this->description = $issue->description;
        $this->mermaid_diagram = $issue->mermaid_diagram ?? '';
        $this->page_id = $issue->page_id;
        $this->component_area = $issue->component_area ?? '';
        $this->severity = $issue->severity;
        $this->difficulty = $issue->difficulty;
        $this->status = $issue->status;
        $this->wcag_criteria = $issue->wcagCriteria()->pluck('id')->all();
        $this->example_ids = $issue->examples()->pluck('wcag_criterion_examples.id')->all();
    }

    public function update(): void
    {
        $this->validate();

        $project = AccessibilityProject::findOrFail($this->projectId);
        $issue = AccessibilityIssue::findOrFail($this->issueId);

        $this->authorize('update', $project);

        $this->validateOnly('page_id', [
            'page_id' => [
                'nullable',
                Rule::exists('accessibility_pages', 'id')
                    ->where(fn ($query) => $query->where('project_id', $project->id)),
            ],
        ]);

        $this->validate([
            'wcag_criteria' => ['nullable', 'array'],
            'wcag_criteria.*' => Rule::exists('wcag_success_criteria', 'id')
                ->where(fn ($query) => $query->where('standard', 'WCAG 2.1')),
            'example_ids' => ['nullable', 'array'],
            'example_ids.*' => Rule::exists('wcag_criterion_examples', 'id')
                ->where(fn ($query) => $query->whereIn('wcag_success_criterion_id', $this->wcag_criteria)),
        ]);

        $issue->update([
            'title' => $this->title,
            'description' => $this->description,
            'mermaid_diagram' => $this->mermaid_diagram,
            'page_id' => $this->page_id,
            'component_area' => $this->component_area,
            'severity' => $this->severity,
            'difficulty' => $this->difficulty,
            'status' => $this->status,
        ]);

        $issue->wcagCriteria()->sync($this->wcag_criteria);
        $issue->examples()->sync($this->examplePivotData($this->example_ids));

        // Handle file uploads
        if (! empty($this->attachments)) {
            $this->storeAttachments($this->attachments, $issue);
            $this->attachments = [];
        }

        session()->flash('success', __('Issue updated successfully.'));
        $this->redirect(route('accessibility-issues.edit', [$project, $issue]), navigate: true);
    }

    public function updatedWcagCriteria(): void
    {
        $this->example_ids = $this->validExampleIds();
    }

    public function cancel(): void
    {
        $project = AccessibilityProject::findOrFail($this->projectId);
        $issue = AccessibilityIssue::findOrFail($this->issueId);
        $this->redirect(route('accessibility-issues.show', [$project, $issue]), navigate: true);
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

    private function storeAttachments(array $files, AccessibilityIssue $issue): void
    {
        foreach ($files as $file) {
            $filename = Str::ulid().'.'.$file->extension();
            $path = $file->storeAs('accessibility-issues/'.$issue->id, $filename, 'public');

            $issue->attachments()->create([
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $path,
            ]);
        }
    }

    public function deleteAttachment(string $attachmentId): void
    {
        $issue = AccessibilityIssue::findOrFail($this->issueId);
        $attachment = $issue->attachments()->findOrFail($attachmentId);

        $this->authorize('update', AccessibilityProject::findOrFail($this->projectId));

        Storage::disk('public')->delete($attachment->path);
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
            ->select('id', 'path', 'original_filename')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'path' => $a->path,
                'original_filename' => $a->original_filename,
            ])
            ->toArray();

        $availableCriteria = WcagSuccessCriterion::wcag()
            ->get()
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

        $availableExamples = WcagCriterionExample::query()
            ->with('criterion')
            ->whereIn('wcag_success_criterion_id', $this->wcag_criteria)
            ->when(trim($this->exampleSearch) !== '', function ($query): void {
                $search = '%'.trim($this->exampleSearch).'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->orderBy('wcag_success_criterion_id')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return view('livewire.edit-accessibility-issue', [
            'pages' => $pages,
            'databaseAttachments' => $attachmentsList,
            'availableCriteria' => $availableCriteria,
            'availableExamples' => $availableExamples,
        ]);
    }

    /**
     * @param  array<int, string>  $exampleIds
     * @return array<string, array{sort_order: int}>
     */
    private function examplePivotData(array $exampleIds): array
    {
        return collect($exampleIds)
            ->values()
            ->mapWithKeys(fn (string $exampleId, int $index): array => [
                $exampleId => ['sort_order' => $index],
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function validExampleIds(): array
    {
        if ($this->wcag_criteria === []) {
            return [];
        }

        $validIds = WcagCriterionExample::query()
            ->whereIn('wcag_success_criterion_id', $this->wcag_criteria)
            ->pluck('id')
            ->all();

        return array_values(array_intersect($this->example_ids, $validIds));
    }
}
