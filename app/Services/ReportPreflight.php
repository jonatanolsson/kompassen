<?php

namespace App\Services;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityPage;
use App\Models\AccessibilityProject;
use App\Models\TestingMethodology;
use App\Models\WcagCriterionExample;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReportPreflight
{
    /**
     * @return array{
     *     has_warnings: bool,
     *     scope_recorded: bool,
     *     included_pages: Collection<int, AccessibilityPage>,
     *     excluded_pages: Collection<int, AccessibilityPage>,
     *     pages_without_scope_decision: Collection<int, AccessibilityPage>,
     *     excluded_pages_without_reason: Collection<int, AccessibilityPage>,
     *     clean_pages: Collection<int, AccessibilityPage>,
     *     included_issue_count: int,
     *     issues_without_summary: Collection<int, AccessibilityIssue>,
     *     issues_without_wcag: Collection<int, AccessibilityIssue>,
     *     excluded_issues: Collection<int, AccessibilityIssue>,
     *     issues_with_stale_diagrams: Collection<int, AccessibilityIssue>,
     *     examples_with_stale_diagrams: Collection<int, WcagCriterionExample>,
     *     methodologies: Collection<int, TestingMethodology>,
     *     methodology_count: int,
     *     available_options: array<string, bool>
     * }
     */
    public function assess(AccessibilityProject $project): array
    {
        $teamId = (string) $project->team_id;
        $pages = $project->pages()
            ->orderBy('name')
            ->get(['id', 'project_id', 'name', 'scope', 'url', 'scope_exclusion_reason']);
        $scope = $this->assessScope($project, $pages);
        $issues = $project->issues()
            ->with([
                'attachments',
                'examples' => fn (BelongsToMany $query): BelongsToMany => $query->visibleToTeam($teamId),
                'examples.attachments',
                'page',
                'wcagCriteria',
            ])
            ->get([
                'id',
                'project_id',
                'page_id',
                'title',
                'summary',
                'problem_code_snippet',
                'corrected_code_snippet',
                'mermaid_diagram',
                'mermaid_svg',
                'mermaid_source_hash',
                'resolution_notes',
                'sample_scope',
            ]);

        $includedPages = $scope['included_pages'];
        $excludedPages = $scope['excluded_pages'];
        $pagesWithoutScopeDecision = $scope['pages_without_scope_decision'];
        $excludedPagesWithoutReason = $scope['excluded_pages_without_reason'];
        $includedIssues = $issues->filter(fn (AccessibilityIssue $issue): bool => $this->isIncludedInReport($issue, $project))
            ->values();
        $excludedIssues = $issues
            ->filter(fn (AccessibilityIssue $issue): bool => $issue->page !== null
                && (string) $issue->page->project_id === (string) $project->id
                && $issue->page->scope === 'out_of_scope')
            ->values();
        $criteria = $includedIssues->flatMap(fn (AccessibilityIssue $issue) => $issue->wcagCriteria)->unique('id')->values();
        $examples = $includedIssues->flatMap(fn (AccessibilityIssue $issue) => $issue->examples)->unique('id')->values();
        $pageIdsWithIssues = $includedIssues->pluck('page_id')
            ->filter()
            ->map(fn (string $pageId): string => (string) $pageId)
            ->flip();
        $cleanPages = $includedPages
            ->reject(fn (AccessibilityPage $page): bool => $pageIdsWithIssues->has((string) $page->id))
            ->values();
        $methodologies = $project->methodologies()
            ->availableToTeam($teamId)
            ->orderBy('name')
            ->get();
        $methodologyCount = $methodologies->count();

        $issuesWithStaleDiagrams = $includedIssues
            ->filter(fn (AccessibilityIssue $issue): bool => $this->issueDiagramNeedsRefresh($issue))
            ->values();
        $examplesWithStaleDiagrams = $examples
            ->filter(fn (WcagCriterionExample $example): bool => $example->code_language === 'mermaid'
                && filled($example->code_snippet)
                && $this->exampleDiagramNeedsRefresh($example))
            ->values();

        $availableOptions = [
            'evidence' => $includedIssues->contains(fn (AccessibilityIssue $issue): bool => $issue->wcagCriteria->isNotEmpty()),
            'images' => $includedIssues->contains(fn (AccessibilityIssue $issue): bool => $issue->attachments->isNotEmpty()
                || $issue->wcagCriteria->contains(fn ($criterion): bool => filled($criterion->pivot?->screenshot_path))
                || $issue->examples->contains(fn ($example): bool => $example->attachments->isNotEmpty())),
            'code' => $includedIssues->contains(fn (AccessibilityIssue $issue): bool => filled($issue->problem_code_snippet)
                || filled($issue->corrected_code_snippet)
                || $issue->wcagCriteria->contains(fn ($criterion): bool => filled($criterion->pivot?->code_snippet))
                || $issue->examples->contains(fn ($example): bool => $example->code_language !== 'mermaid' && filled($example->code_snippet))),
            'diagrams' => $includedIssues->contains(fn (AccessibilityIssue $issue): bool => IssueDiagramSvg::hasSource($issue->mermaid_diagram)
                || $issue->examples->contains(fn (WcagCriterionExample $example): bool => $example->code_language === 'mermaid'
                    && filled($example->code_snippet))),
            'examples' => $examples->isNotEmpty(),
            'resolution' => $includedIssues->contains(fn (AccessibilityIssue $issue): bool => filled($issue->resolution_notes)),
            'sample_scope' => $includedIssues->contains(fn (AccessibilityIssue $issue): bool => filled($issue->sample_scope)),
            'methodologies' => $methodologyCount > 0,
            'clean_pages' => $cleanPages->isNotEmpty(),
            'wcag_index' => $criteria->isNotEmpty(),
        ];
        $scopeRecorded = $scope['scope_recorded'];
        $issuesWithoutSummary = $includedIssues
            ->filter(fn (AccessibilityIssue $issue): bool => blank($issue->summary))
            ->values();
        $issuesWithoutWcag = $includedIssues
            ->filter(fn (AccessibilityIssue $issue): bool => $issue->wcagCriteria->isEmpty())
            ->values();
        $hasWarnings = ! $scopeRecorded
            || $includedPages->isEmpty()
            || $pagesWithoutScopeDecision->isNotEmpty()
            || $excludedPagesWithoutReason->isNotEmpty()
            || $issuesWithoutSummary->isNotEmpty()
            || $issuesWithoutWcag->isNotEmpty()
            || $excludedIssues->isNotEmpty()
            || $issuesWithStaleDiagrams->isNotEmpty()
            || $examplesWithStaleDiagrams->isNotEmpty()
            || $methodologyCount === 0;

        return [
            'has_warnings' => $hasWarnings,
            ...$scope,
            'clean_pages' => $cleanPages,
            'included_issue_count' => $includedIssues->count(),
            'issues_without_summary' => $issuesWithoutSummary,
            'issues_without_wcag' => $issuesWithoutWcag,
            'excluded_issues' => $excludedIssues,
            'issues_with_stale_diagrams' => $issuesWithStaleDiagrams,
            'examples_with_stale_diagrams' => $examplesWithStaleDiagrams,
            'methodologies' => $methodologies,
            'methodology_count' => $methodologyCount,
            'available_options' => $availableOptions,
        ];
    }

    /**
     * @return array{
     *     scope_recorded: bool,
     *     included_pages: Collection<int, AccessibilityPage>,
     *     excluded_pages: Collection<int, AccessibilityPage>,
     *     pages_without_scope_decision: Collection<int, AccessibilityPage>,
     *     excluded_pages_without_reason: Collection<int, AccessibilityPage>
     * }
     */
    public function assessScope(AccessibilityProject $project, Collection $pages): array
    {
        $includedPages = $pages->where('scope', 'in_scope')->values();
        $excludedPages = $pages->where('scope', 'out_of_scope')->values();

        return [
            'scope_recorded' => filled($project->audit_scope) || filled($project->scope_limitations),
            'included_pages' => $includedPages,
            'excluded_pages' => $excludedPages,
            'pages_without_scope_decision' => $pages
                ->reject(fn (AccessibilityPage $page): bool => in_array($page->scope, ['in_scope', 'out_of_scope'], true))
                ->values(),
            'excluded_pages_without_reason' => $excludedPages
                ->filter(fn (AccessibilityPage $page): bool => blank($page->scope_exclusion_reason))
                ->values(),
        ];
    }

    private function isIncludedInReport(AccessibilityIssue $issue, AccessibilityProject $project): bool
    {
        if ($issue->page_id === null) {
            return true;
        }

        return $issue->page !== null
            && (string) $issue->page->project_id === (string) $project->id
            && $issue->page->scope === 'in_scope';
    }

    private function issueDiagramNeedsRefresh(AccessibilityIssue $issue): bool
    {
        try {
            IssueDiagramSvg::assertCurrent($issue, 'report');

            return false;
        } catch (ValidationException) {
            return true;
        }
    }

    private function exampleDiagramNeedsRefresh(WcagCriterionExample $example): bool
    {
        try {
            IssueDiagramSvg::assertExampleCurrent($example, 'report');

            return false;
        } catch (ValidationException) {
            return true;
        }
    }
}
