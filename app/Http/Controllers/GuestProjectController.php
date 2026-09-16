<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\ProjectShareLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GuestProjectController extends Controller
{
    use AuthorizesRequests;

    public function show(string $token): View
    {
        $link = ProjectShareLink::where('token', $token)
            ->with('project')
            ->firstOrFail();

        if ($link->isExpired()) {
            abort(410, 'This share link has expired.');
        }

        $project = $link->project;

        return view('shared-project.show', $this->previewData($project) + compact('link'));
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
            'issues.attachments',
            'issues.page',
            'issues.wcagCriteria',
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
