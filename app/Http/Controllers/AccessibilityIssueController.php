<?php

namespace App\Http\Controllers;

use App\Actions\ResolveAccessibilityIssue;
use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Models\WcagSuccessCriterion;
use App\Services\IssueExporter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccessibilityIssueController extends Controller
{
    use AuthorizesRequests;

    public function create(AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $wcagCriteria = WcagSuccessCriterion::all();

        return view('accessibility.issues.create', compact('project', 'wcagCriteria'));
    }

    public function show(AccessibilityProject $project, AccessibilityIssue $issue)
    {
        $this->authorize('view', $project);

        $wcagCriteria = $issue->wcagCriteria()->get();

        return view('accessibility.issues.show', compact('project', 'issue', 'wcagCriteria'));
    }

    public function store(Request $request, AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'page_id' => [
                'nullable',
                Rule::exists('accessibility_pages', 'id')
                    ->where(fn ($query) => $query->where('project_id', $project->id)),
            ],
            'severity' => 'required|in:critical,major,moderate,minor',
            'difficulty' => 'required|in:easy,medium,hard',
            'component_area' => 'nullable|string|max:255',
            'status' => 'required|in:open,resolved,wont_fix',
            'wcag_criteria' => 'nullable|array',
            'wcag_criteria.*' => 'exists:wcag_success_criteria,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,webp|max:5120',
        ]);

        $issue = $project->issues()->create($validated);

        if (! empty($validated['wcag_criteria'])) {
            $issue->wcagCriteria()->sync($validated['wcag_criteria']);
        }

        if ($request->hasFile('attachments')) {
            $this->storeAttachments($request->file('attachments'), $issue);
        }

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', 'Issue created successfully.');
    }

    public function edit(AccessibilityProject $project, AccessibilityIssue $issue)
    {
        $this->authorize('update', $project);

        $wcagCriteria = WcagSuccessCriterion::all();
        $selectedCriteria = $issue->wcagCriteria()
            ->get()
            ->map(fn ($criterion) => [
                'id' => $criterion->id,
                'failure_type' => $criterion->pivot->failure_type ?? '',
                'comment' => $criterion->pivot->comment ?? '',
                'code_snippet' => $criterion->pivot->code_snippet ?? '',
            ])
            ->values()
            ->toArray();

        return view('accessibility.issues.edit', compact('project', 'issue', 'wcagCriteria', 'selectedCriteria'));
    }

    public function update(Request $request, AccessibilityProject $project, AccessibilityIssue $issue)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'page_id' => [
                'nullable',
                Rule::exists('accessibility_pages', 'id')
                    ->where(fn ($query) => $query->where('project_id', $project->id)),
            ],
            'severity' => 'required|in:critical,major,moderate,minor',
            'difficulty' => 'required|in:easy,medium,hard',
            'component_area' => 'nullable|string|max:255',
            'status' => 'required|in:open,resolved,wont_fix',
            'wcag_criteria' => 'nullable|array',
            'wcag_criteria.*' => 'exists:wcag_success_criteria,id',
            'wcag_failure_types' => 'nullable|array',
            'wcag_comments' => 'nullable|array',
            'wcag_code_snippets' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,webp|max:5120',
        ]);

        $issue->update($validated);

        if (! empty($validated['wcag_criteria'])) {
            $syncData = [];
            foreach ($validated['wcag_criteria'] as $criterionId) {
                $syncData[$criterionId] = [
                    'failure_type' => $validated['wcag_failure_types'][$criterionId] ?? null,
                    'comment' => $validated['wcag_comments'][$criterionId] ?? null,
                    'code_snippet' => $validated['wcag_code_snippets'][$criterionId] ?? null,
                ];
            }
            $issue->wcagCriteria()->sync($syncData);
        }

        if ($request->hasFile('attachments')) {
            $this->storeAttachments($request->file('attachments'), $issue);
        }

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', 'Issue updated successfully.');
    }

    public function destroy(AccessibilityProject $project, AccessibilityIssue $issue)
    {
        $this->authorize('update', $project);

        $issue->delete();

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Issue deleted successfully.'));
    }

    public function resolve(AccessibilityProject $project, AccessibilityIssue $issue)
    {
        $this->authorize('update', $project);

        $validated = request()->validate([
            'resolution_status' => 'required|in:open,fixed,wontfix',
            'resolution_notes' => 'nullable|string|max:5000',
        ]);

        (new ResolveAccessibilityIssue)($issue, $validated['resolution_status'], $validated['resolution_notes'] ?? null);

        $translatedStatus = __($validated['resolution_status']);

        return back()->with('success', __('Issue marked as :status', ['status' => $translatedStatus]));
    }

    public function assign(AccessibilityProject $project, AccessibilityIssue $issue)
    {
        $this->authorize('update', $project);

        $validated = request()->validate([
            'assigned_to' => [
                'nullable',
                Rule::exists('project_members', 'user_id')
                    ->where(fn ($query) => $query->where('project_id', $project->id)),
            ],
        ]);

        $issue->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => $validated['assigned_to'] ? now() : null,
        ]);

        $assigneeName = $validated['assigned_to']
            ? $issue->assignedTo->name
            : __('Unassigned');

        return back()->with('success', __('Issue assigned to :name', ['name' => $assigneeName]));
    }

    public function export(AccessibilityProject $project, AccessibilityIssue $issue): Response
    {
        $this->authorize('view', $project);

        $format = request()->query('format', 'json');
        $exporter = new IssueExporter($issue);
        $content = $exporter->export($format);

        $filename = Str::slug($issue->title).'.'.$this->getFileExtension($format);

        return response($content)
            ->header('Content-Type', $this->getContentType($format))
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    private function getFileExtension(string $format): string
    {
        return match ($format) {
            'json' => 'json',
            'markdown' => 'md',
            'csv' => 'csv',
            default => 'json',
        };
    }

    private function getContentType(string $format): string
    {
        return match ($format) {
            'json' => 'application/json',
            'markdown' => 'text/markdown',
            'csv' => 'text/csv',
            default => 'application/json',
        };
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
}
