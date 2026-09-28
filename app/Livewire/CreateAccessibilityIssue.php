<?php

namespace App\Livewire;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\WcagCriterionExample;
use App\Models\WcagSuccessCriterion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    public string $mermaid_diagram = '';

    public ?string $page_id = null;

    public string $severity = 'major';

    public string $difficulty = 'medium';

    public string $component_area = '';

    public string $status = 'open';

    public array $wcag_criteria = [];

    public array $example_ids = [];

    public string $exampleSearch = '';

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
            'mermaid_diagram' => 'nullable|string|max:20000',
            'page_id' => [
                'nullable',
                Rule::exists('accessibility_pages', 'id')
                    ->where(fn ($query) => $query->where('project_id', $this->projectId)),
            ],
            'severity' => 'required|in:critical,major,moderate,minor',
            'difficulty' => 'required|in:easy,medium,hard',
            'component_area' => 'nullable|string|max:255',
            'status' => 'required|in:open,resolved,wont_fix',
            'wcag_criteria' => 'nullable|array',
            'wcag_criteria.*' => Rule::exists('wcag_success_criteria', 'id')
                ->where(fn ($query) => $query->where('standard', 'WCAG 2.1')),
            'example_ids' => 'nullable|array',
            'example_ids.*' => Rule::exists('wcag_criterion_examples', 'id')
                ->where(fn ($query) => $query->whereIn('wcag_success_criterion_id', $this->wcag_criteria)),
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
            'mermaid_diagram' => $validated['mermaid_diagram'],
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

        $issue->examples()->sync($this->examplePivotData($validated['example_ids'] ?? []));

        if (! empty($validated['attachments'])) {
            $this->storeAttachments($validated['attachments'], $issue);
        }

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Issue created successfully.'));
    }

    public function updatedWcagCriteria(): void
    {
        $this->example_ids = $this->validExampleIds();
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
        $wcagCriteria = WcagSuccessCriterion::wcag()->get();
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

        return view('livewire.create-accessibility-issue', compact(
            'project',
            'pages',
            'wcagCriteria',
            'availableExamples'
        ));
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
