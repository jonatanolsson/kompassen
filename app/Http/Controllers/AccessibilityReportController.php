<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Services\PdfReportGenerator;
use App\Services\ReportGenerator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AccessibilityReportController extends Controller
{
    use AuthorizesRequests;

    public function index(AccessibilityProject $project)
    {
        $this->authorize('view', $project);

        $reports = $project->reports()->with('creator')->orderByDesc('created_at')->get();

        return view('accessibility.reports.index', compact('project', 'reports'));
    }

    public function create(AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        return view('accessibility.reports.create', compact('project'));
    }

    public function store(Request $request, AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'scope' => 'nullable|string',
        ]);

        $generator = new ReportGenerator($project);
        $report = $generator->generate($validated['title'], $validated['scope'] ?? null);

        return redirect()->route('accessibility-reports.show', ['project' => $project, 'report' => $report])
            ->with('success', __('Report generated successfully.'));
    }

    public function show(AccessibilityProject $project, AccessibilityReport $report)
    {
        $this->authorize('view', $project);

        // Ensure report belongs to project
        if ((string) $report->project_id !== (string) $project->id) {
            abort(404);
        }

        return view('accessibility.reports.show', compact('project', 'report'));
    }

    public function download(AccessibilityProject $project, AccessibilityReport $report)
    {
        $this->authorize('view', $project);

        // Ensure report belongs to project
        if ((string) $report->project_id !== (string) $project->id) {
            abort(404);
        }

        $generator = new PdfReportGenerator($report);
        $pdf = $generator->download();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$report->title.'_'.now()->format('Y-m-d').'.pdf"');
    }

    public function destroy(AccessibilityProject $project, AccessibilityReport $report)
    {
        $this->authorize('delete', $project);

        // Ensure report belongs to project
        if ((string) $report->project_id !== (string) $project->id) {
            abort(404);
        }

        $report->delete();

        return redirect()->route('accessibility-reports.index', $project)
            ->with('success', __('Report deleted successfully.'));
    }
}
