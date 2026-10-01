<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccessibilityReportRequest;
use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Services\PdfReportGenerator;
use App\Services\ReportGenerator;
use App\Services\ReportOptions;
use App\Services\ReportSiteIcon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class AccessibilityReportController extends Controller
{
    use AuthorizesRequests;

    public function index(AccessibilityProject $project)
    {
        $this->authorize('view', $project);

        $reports = $project->reports()->with('creator')->orderByDesc('created_at')->orderByDesc('id')->get();
        $shareLinksByReport = auth()->user()->can('update', $project)
            ? $project->shareLinks()->whereNotNull('report_id')->get()->groupBy('report_id')
            : collect();

        return view('accessibility.reports.index', compact('project', 'reports', 'shareLinksByReport'));
    }

    public function create(AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $reportOptions = ReportOptions::resolve($project->report_options);
        $reportSites = ReportSiteIcon::sitesFor($project);

        return view('accessibility.reports.create', compact('project', 'reportOptions', 'reportSites'));
    }

    public function store(StoreAccessibilityReportRequest $request, AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validated();

        $generator = new ReportGenerator($project);
        $report = $generator->generate($validated['title'], $validated['scope'] ?? null, $validated['options'] ?? null, null, $validated['site_url'] ?? '');

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

        $usesCurrentTemplate = str_contains(
            $report->html_content,
            'data-report-template="'.$this->reportTemplateVersion().'"',
        );
        $versions = $project->reports()->where(function ($query) use ($report) {
            $query->where('series_id', $report->series_id ?? $report->id)
                ->orWhere('id', $report->series_id ?? $report->id);
        })->orderBy('version')->get();
        $shareLinks = auth()->user()->can('update', $project)
            ? $project->shareLinks()->where('report_id', $report->id)->latest()->get()
            : collect();

        return view('accessibility.reports.show', compact('project', 'report', 'usesCurrentTemplate', 'versions', 'shareLinks'));
    }

    public function download(AccessibilityProject $project, AccessibilityReport $report)
    {
        $this->authorize('view', $project);

        // Ensure report belongs to project
        if ((string) $report->project_id !== (string) $project->id) {
            abort(404);
        }

        $generator = new PdfReportGenerator($report);
        $pdf = $generator->generate();
        $filename = (Str::slug($report->title) ?: 'rapport').'-'.now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            function () use ($pdf): void {
                echo $pdf;
            },
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
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

    public function regenerate(AccessibilityProject $project, AccessibilityReport $report)
    {
        $this->authorize('update', $project);

        if ((string) $report->project_id !== (string) $project->id) {
            abort(404);
        }

        $newVersion = (new ReportGenerator($project))->regenerate($report);

        return redirect()
            ->route('accessibility-reports.show', ['project' => $project, 'report' => $newVersion])
            ->with('success', __('New report version created.'));
    }

    private function reportTemplateVersion(): int
    {
        return ReportGenerator::TEMPLATE_VERSION;
    }
}
