<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\ProjectShareLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GuestProjectController extends Controller
{
    use AuthorizesRequests;

    public function show(string $token)
    {
        $link = ProjectShareLink::where('token', $token)
            ->with('project.pages.issues.wcagCriteria')
            ->firstOrFail();

        if ($link->isExpired()) {
            abort(410, 'This share link has expired.');
        }

        $project = $link->project;
        $stats = $this->calculateStats($project);

        return view('shared-project.show', compact('project', 'link', 'stats'));
    }

    public function preview(AccessibilityProject $project)
    {
        $this->authorize('view', $project);

        $project->load('pages.issues.wcagCriteria');
        $link = null;
        $stats = $this->calculateStats($project);

        return view('shared-project.show', compact('project', 'link', 'stats'));
    }

    private function calculateStats(AccessibilityProject $project): array
    {
        $allIssues = $project->issues()->with('wcagCriteria')->get();

        $severityCounts = [
            'critical' => $allIssues->where('severity', 'critical')->count(),
            'major' => $allIssues->where('severity', 'major')->count(),
            'moderate' => $allIssues->where('severity', 'moderate')->count(),
            'minor' => $allIssues->where('severity', 'minor')->count(),
        ];

        $wcagMapping = $allIssues
            ->flatMap(fn ($issue) => $issue->wcagCriteria->map(fn ($criterion) => ['criterion' => $criterion, 'issue' => $issue]))
            ->groupBy('criterion.number');

        return [
            'total_issues' => $allIssues->count(),
            'severity_counts' => $severityCounts,
            'total_pages' => $project->pages->count(),
            'wcag_mapping' => $wcagMapping,
            'has_critical' => $severityCounts['critical'] > 0,
        ];
    }
}
