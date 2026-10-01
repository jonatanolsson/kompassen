<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\ProjectShareLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class GuestProjectController extends Controller
{
    use AuthorizesRequests;

    public function show(string $token): Response
    {
        $link = ProjectShareLink::where('token', $token)
            ->with('report')
            ->firstOrFail();

        if ($link->report_id === null || $link->report === null || $link->report->project_id !== $link->project_id || blank($link->report->html_content)) {
            abort(410, __('This customer link is unavailable. Create a new link from a saved report.'));
        }

        if ($link->isExpired()) {
            abort(410, __('This customer link has expired.'));
        }

        return response($link->report->html_content)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'private, no-store')
            ->header('Content-Security-Policy', "default-src 'none'; img-src data:; font-src data:; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
    }

    public function preview(AccessibilityProject $project): View
    {
        $this->authorize('view', $project);

        $link = null;

        return view('shared-project.show', $this->previewData($project) + compact('link'));
    }

    private function calculateStats(AccessibilityProject $project): array
    {
        $inScopePageIds = $project->pages
            ->where('scope', 'in_scope')
            ->pluck('id');

        $allIssues = $project->issues
            ->filter(fn ($issue) => $issue->page_id === null || $inScopePageIds->contains($issue->page_id))
            ->values();

        $severityCounts = [
            'critical' => $allIssues->where('severity', 'critical')->count(),
            'major' => $allIssues->where('severity', 'major')->count(),
            'moderate' => $allIssues->where('severity', 'moderate')->count(),
            'minor' => $allIssues->where('severity', 'minor')->count(),
        ];

        $wcagMapping = $allIssues
            ->flatMap(fn ($issue) => $issue->wcagCriteria->map(fn ($criterion) => ['criterion' => $criterion, 'issue' => $issue]))
            ->groupBy('criterion.number');

        $severityRank = ['critical' => 0, 'major' => 1, 'moderate' => 2, 'minor' => 3];
        $priorityIssues = $allIssues
            ->sortBy(fn ($issue) => $severityRank[$issue->severity] ?? 99)
            ->take(3)
            ->values();

        $riskLevel = match (true) {
            $severityCounts['critical'] > 0 => __('High risk'),
            $severityCounts['major'] > 0 => __('Elevated risk'),
            $severityCounts['moderate'] > 0 => __('Moderate risk'),
            $allIssues->count() > 0 => __('Low risk'),
            default => __('No documented issues'),
        };

        $riskSummary = match (true) {
            $severityCounts['critical'] > 0 => __('Critical accessibility barriers were found and should be addressed before launch or delivery.'),
            $severityCounts['major'] > 0 => __('Major accessibility issues were found and should be prioritized in the next remediation cycle.'),
            $severityCounts['moderate'] > 0 => __('Accessibility issues were found, but no critical or major barriers are documented.'),
            $allIssues->count() > 0 => __('Only minor accessibility issues are currently documented.'),
            default => __('No accessibility issues are currently documented in this report.'),
        };

        return [
            'total_issues' => $allIssues->count(),
            'severity_counts' => $severityCounts,
            'total_pages' => $inScopePageIds->count(),
            'total_out_of_scope_pages' => $project->pages->where('scope', 'out_of_scope')->count(),
            'wcag_mapping' => $wcagMapping,
            'priority_issues' => $priorityIssues,
            'risk_level' => $riskLevel,
            'risk_summary' => $riskSummary,
            'recommended_next_step' => __('Review the prioritized actions below and plan remediation for the highest severity issues first.'),
            'has_critical' => $severityCounts['critical'] > 0,
        ];
    }

    /**
     * @return array{
     *     project: AccessibilityProject,
     *     stats: array<string, mixed>,
     *     projectWideIssues: Collection,
     *     inScopePages: Collection,
     *     outOfScopePages: Collection
     * }
     */
    private function previewData(AccessibilityProject $project): array
    {
        $project->load([
            'pages.issues.attachments',
            'pages.issues.wcagCriteria',
            'pages.issues.examples' => fn ($query) => $query->visibleToTeam((string) $project->team_id),
            'pages.issues.examples.criterion',
            'pages.issues.examples.attachments',
            'pages.issues.examples.referenceLinks',
            'issues.attachments',
            'issues.page',
            'issues.wcagCriteria',
            'issues.examples' => fn ($query) => $query->visibleToTeam((string) $project->team_id),
            'issues.examples.criterion',
            'issues.examples.attachments',
            'issues.examples.referenceLinks',
        ]);

        return [
            'project' => $project,
            'stats' => $this->calculateStats($project),
            'projectWideIssues' => $project->issues->whereNull('page_id')->values(),
            'inScopePages' => $project->pages->where('scope', 'in_scope')->values(),
            'outOfScopePages' => $project->pages->where('scope', 'out_of_scope')->values(),
        ];
    }
}
