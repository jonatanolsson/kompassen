<?php

namespace App\Services;

use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use Illuminate\Support\Collection;

class ReportGenerator
{
    public function __construct(
        private AccessibilityProject $project,
    ) {}

    public function generate(string $title, ?string $scope = null): AccessibilityReport
    {
        $this->project->loadMissing('pages');
        $issues = $this->project->issues()->with('wcagCriteria')->get();
        $issuesByWcag = $this->groupIssuesByWcag($issues);
        $counts = $this->calculateIssueCounts($issues);

        $htmlContent = view('reports.accessibility-report', [
            'title' => $title,
            'project' => $this->project,
            'scope' => $scope,
            'issuesByWcag' => $issuesByWcag,
            'counts' => $counts,
            'issues' => $issues,
        ])->render();

        return AccessibilityReport::create([
            'project_id' => $this->project->id,
            'title' => $title,
            'scope' => $scope,
            'target_wcag_level' => $this->project->target_wcag_level,
            'total_issues' => $issues->count(),
            'critical_count' => $counts['critical'] ?? 0,
            'major_count' => $counts['major'] ?? 0,
            'moderate_count' => $counts['moderate'] ?? 0,
            'minor_count' => $counts['minor'] ?? 0,
            'html_content' => $htmlContent,
            'created_by' => auth()->id(),
        ]);
    }

    private function groupIssuesByWcag(Collection $issues): Collection
    {
        // Group issues that have WCAG criteria
        $groupedByWcag = $issues
            ->filter(fn ($issue) => $issue->wcagCriteria->isNotEmpty())
            ->flatMap(function ($issue) {
                return $issue->wcagCriteria->map(function ($sc) use ($issue) {
                    return [
                        'sc' => $sc,
                        'issue' => $issue,
                    ];
                });
            })
            ->groupBy('sc.number')
            ->map(function ($group) {
                return [
                    'sc' => $group->first()['sc'],
                    'issues' => $group->map(fn ($item) => $item['issue'])->unique('id'),
                ];
            });

        // If there are issues without WCAG criteria, group them separately
        $unlistedIssues = $issues->filter(fn ($issue) => $issue->wcagCriteria->isEmpty());
        if ($unlistedIssues->isNotEmpty()) {
            $groupedByWcag['_unlisted'] = [
                'sc' => null,
                'issues' => $unlistedIssues,
            ];
        }

        return $groupedByWcag;
    }

    private function calculateIssueCounts(Collection $issues): array
    {
        return $issues->countBy('severity')->toArray();
    }
}
