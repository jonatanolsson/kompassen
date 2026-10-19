<?php

namespace App\Services;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityPage;
use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\WcagCriterionExample;
use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class ReportChangeSummary
{
    /**
     * @param  Collection<int, AccessibilityPage>  $pages
     * @param  Collection<int, AccessibilityIssue>  $issues
     * @param  Collection<int, Model>  $methodologies
     * @return array{project: string, resources: string, findings: string, methodologies: string}
     */
    public function fingerprints(AccessibilityProject $project, Collection $pages, Collection $issues, Collection $methodologies): array
    {
        $projectAttributes = $this->attributes($project);
        unset($projectAttributes['report_options']);

        return [
            'project' => $this->fingerprint($projectAttributes),
            'resources' => $this->fingerprint($pages
                ->sortBy('id')
                ->map(fn (AccessibilityPage $page): array => $this->attributes($page))
                ->values()
                ->all()),
            'findings' => $this->fingerprint($issues
                ->sortBy('id')
                ->map(fn (AccessibilityIssue $issue): array => $this->issueSnapshot($issue))
                ->values()
                ->all()),
            'methodologies' => $this->fingerprint($methodologies
                ->sortBy('id')
                ->map(fn (Model $methodology): array => [
                    'attributes' => $this->attributes($methodology),
                    'pivot' => $this->attributes($methodology->pivot),
                ])
                ->values()
                ->all()),
        ];
    }

    /**
     * @return array{
     *     tracking_available: bool,
     *     has_changes: bool,
     *     changed_sections: array<int, string>
     * }
     */
    public function assess(AccessibilityProject $project, AccessibilityReport $report): array
    {
        $storedFingerprints = $report->document_metadata['source_fingerprints'] ?? null;
        $keys = ['project', 'resources', 'findings', 'methodologies'];

        if (! is_array($storedFingerprints)) {
            return ['tracking_available' => false, 'has_changes' => false, 'changed_sections' => []];
        }

        foreach ($keys as $key) {
            if (! isset($storedFingerprints[$key]) || ! is_string($storedFingerprints[$key])) {
                return ['tracking_available' => false, 'has_changes' => false, 'changed_sections' => []];
            }
        }

        $methodologies = $project->methodologies()
            ->availableToTeam((string) $project->team_id)
            ->orderBy('name')
            ->get();
        $pages = $project->pages()->orderBy('id')->get();
        $issues = $project->issues()
            ->where(function (Builder $query) use ($project): void {
                $query
                    ->whereNull('page_id')
                    ->orWhereHas('page', fn (Builder $pageQuery): Builder => $pageQuery
                        ->where('project_id', $project->id)
                        ->where('scope', 'in_scope'));
            })
            ->with([
                'attachments',
                'examples' => fn (BelongsToMany $query): BelongsToMany => $query->visibleToTeam((string) $project->team_id),
                'examples.attachments',
                'examples.criterion',
                'examples.referenceLinks',
                'page',
                'wcagCriteria',
            ])
            ->orderBy('id')
            ->get();
        $currentFingerprints = $this->fingerprints($project, $pages, $issues, $methodologies);
        $labels = [
            'project' => __('Project details and audit scope'),
            'resources' => __('Pages and services'),
            'findings' => __('Findings and supporting evidence'),
            'methodologies' => __('Testing methodologies'),
        ];
        $changedSections = [];

        foreach ($keys as $key) {
            if ($storedFingerprints[$key] !== $currentFingerprints[$key]) {
                $changedSections[] = $labels[$key];
            }
        }

        return [
            'tracking_available' => true,
            'has_changes' => $changedSections !== [],
            'changed_sections' => $changedSections,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function issueSnapshot(AccessibilityIssue $issue): array
    {
        return [
            'attributes' => $this->attributes($issue),
            'attachments' => $this->modelSnapshots($issue->attachments),
            'criteria' => $issue->wcagCriteria
                ->sortBy('id')
                ->map(fn (WcagSuccessCriterion $criterion): array => [
                    'attributes' => $this->attributes($criterion),
                    'pivot' => $this->attributes($criterion->pivot),
                ])
                ->values()
                ->all(),
            'examples' => $issue->examples
                ->sortBy('id')
                ->map(fn (WcagCriterionExample $example): array => $this->exampleSnapshot($example))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function exampleSnapshot(WcagCriterionExample $example): array
    {
        $criterion = $example->criterion;

        return [
            'attributes' => $this->attributes($example),
            'criterion' => $criterion instanceof Model ? $this->attributes($criterion) : null,
            'attachments' => $this->modelSnapshots($example->attachments),
            'reference_links' => $this->modelSnapshots($example->referenceLinks),
        ];
    }

    /**
     * @param  Collection<int, Model>  $models
     * @return array<int, array<string, mixed>>
     */
    private function modelSnapshots(Collection $models): array
    {
        return $models
            ->sortBy('id')
            ->map(fn (Model $model): array => $this->attributes($model))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(Model $model): array
    {
        $attributes = $model->getAttributes();
        unset($attributes['created_at'], $attributes['updated_at']);

        return $attributes;
    }

    private function fingerprint(array $value): string
    {
        return hash('sha256', json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }
}
