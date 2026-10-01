<?php

namespace App\Services;

use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\AppSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportGenerator
{
    public const TEMPLATE_VERSION = 8;

    public function __construct(
        private AccessibilityProject $project,
    ) {}

    public function generate(string $title, ?string $scope = null, ?array $options = null, ?AccessibilityReport $previous = null, ?string $siteUrl = null): AccessibilityReport
    {
        $selectedSite = $siteUrl ?? $this->project->report_site_url ?? '';
        $siteIcon = $selectedSite !== '' ? app(ReportSiteIcon::class)->fetch($this->project, $selectedSite) : null;

        return DB::transaction(function () use ($title, $scope, $options, $previous, $selectedSite, $siteIcon): AccessibilityReport {
            $this->project = AccessibilityProject::query()->lockForUpdate()->findOrFail($this->project->id);
            $seriesId = $previous?->series_id ?? $previous?->id;
            $latest = $previous
                ? $this->project->reports()->where(function ($query) use ($seriesId) {
                    $query->where('series_id', $seriesId)->orWhere('id', $seriesId);
                })->orderByDesc('version')->firstOrFail()
                : null;
            $version = $latest ? $latest->version + 1 : 1;
            $generatedAt = now();
            $resolvedOptions = ReportOptions::resolve($options ?? $latest?->report_options ?? $this->project->report_options);
            $brandLogo = AppSetting::get('brand_logo');
            $documentMeta = [
                'version' => $version,
                'generatedAt' => $generatedAt,
                'auditDate' => $this->project->audit_date,
                'logo' => $this->project->client_logo,
                'logoPath' => $this->logoDataUri($this->project->client_logo, __('The project logo could not be found. Upload it again before generating the report.')),
                'brandLogoPath' => $this->logoDataUri($brandLogo, __('The company logo could not be used. Upload a PNG or JPEG image in settings before generating the report.')),
                'siteIconPath' => $siteIcon,
                'siteUrl' => $selectedSite,
                'creator' => auth()->user()->name,
            ];
            $reportData = $this->reportData($title, $scope, $resolvedOptions, $documentMeta);

            $report = new AccessibilityReport([
                'project_id' => $this->project->id,
                'version' => $version,
                'previous_version_id' => $latest?->id,
                'generated_at' => $generatedAt,
                'audit_date_snapshot' => $this->project->audit_date,
                'client_logo_snapshot' => $this->project->client_logo,
                'report_options' => $resolvedOptions,
                'document_metadata' => [
                    'project_name' => $this->project->name,
                    'creator' => $documentMeta['creator'],
                    'brand_logo' => $brandLogo,
                    'site_url' => $selectedSite,
                    'target_wcag_level' => $this->project->target_wcag_level,
                ],
                'title' => $title,
                'scope' => $scope,
                'target_wcag_level' => $this->project->target_wcag_level,
                'total_issues' => $reportData['issues']->count(),
                'critical_count' => $reportData['counts']['critical'] ?? 0,
                'major_count' => $reportData['counts']['major'] ?? 0,
                'moderate_count' => $reportData['counts']['moderate'] ?? 0,
                'minor_count' => $reportData['counts']['minor'] ?? 0,
                'html_content' => view('reports.accessibility-report', $reportData)->render(),
                'created_by' => auth()->id(),
            ]);
            $report->id = (string) Str::ulid();
            $report->series_id = $seriesId ?? $report->id;
            $report->save();
            $this->project->update(['report_options' => $resolvedOptions]);

            return $report;
        });
    }

    public function regenerate(AccessibilityReport $report): AccessibilityReport
    {
        return $this->generate($report->title, $report->scope, $report->report_options, $report);
    }

    /**
     * @return array{
     *     title: string,
     *     project: AccessibilityProject,
     *     scope: ?string,
     *     counts: array<string, int>,
     *     issues: Collection,
     *     templateVersion: int,
     *     pageGroups: Collection,
     *     projectWideIssues: Collection,
     *     cleanPages: Collection,
     *     reportOptions: array<string, bool>,
     *     reportImages: array<string, string>,
     *     documentMeta: array<string, mixed>
     * }
     */
    private function reportData(string $title, ?string $scope, array $options, array $documentMeta): array
    {
        $this->project->loadMissing('team');
        $this->project->setRelation(
            'methodologies',
            $this->project->methodologies()->availableToTeam((string) $this->project->team_id)->get(),
        );
        $this->project->setRelation(
            'pages',
            $this->project->pages()->where('scope', 'in_scope')->orderBy('name')->get(),
        );
        $issues = $this->project->issues()
            ->where(function ($query) {
                $query
                    ->whereNull('page_id')
                    ->orWhereHas('page', fn ($pageQuery) => $pageQuery
                        ->where('project_id', $this->project->id)
                        ->where('scope', 'in_scope'));
            })
            ->with([
                'attachments',
                'examples' => fn ($query) => $query->visibleToTeam((string) $this->project->team_id),
                'examples.attachments',
                'examples.criterion',
                'examples.referenceLinks',
                'page',
                'wcagCriteria',
            ])
            ->get()
            ->sortBy(fn ($issue) => sprintf(
                '%d-%d-%s-%s',
                ['open' => 0, 'resolved' => 1, 'wont_fix' => 2][$issue->status] ?? 3,
                $this->severityRank($issue->severity),
                mb_strtolower($issue->title),
                $issue->id,
            ))->values();
        foreach ($issues as $issue) {
            $issue->mermaid_svg = IssueDiagramSvg::assertCurrent($issue, 'report');
            if ($options['examples'] && $options['diagrams']) {
                foreach ($issue->examples as $example) {
                    if ($example->code_language === 'mermaid' && filled($example->code_snippet)) {
                        $example->mermaid_svg = IssueDiagramSvg::assertExampleCurrent($example);
                    }
                }
            }
        }

        $counts = $this->calculateIssueCounts($issues);
        $pageGroups = $this->project->pages
            ->map(function ($page) use ($issues): array {
                $pageIssues = $issues->where('page_id', $page->id)->values();

                return [
                    'page' => $page,
                    'issues' => $pageIssues,
                    'statusGroups' => $pageIssues->groupBy('status'),
                    'highestSeverity' => $pageIssues->sortBy(fn ($issue) => $this->severityRank($issue->severity))->first()?->severity,
                ];
            });
        $cleanPages = $pageGroups->filter(fn ($group) => $group['issues']->isEmpty())->pluck('page')->values();
        $pageGroups = $pageGroups->filter(fn ($group) => $group['issues']->isNotEmpty())
            ->sortBy(fn ($group) => sprintf('%d-%s-%s', $this->severityRank($group['highestSeverity']), mb_strtolower($group['page']->name), $group['page']->id))
            ->values();

        return [
            'title' => $title,
            'project' => $this->project,
            'scope' => $scope,
            'counts' => $counts,
            'issues' => $issues,
            'templateVersion' => self::TEMPLATE_VERSION,
            'pageGroups' => $pageGroups,
            'projectWideIssues' => $issues->whereNull('page_id')->values(),
            'cleanPages' => $cleanPages,
            'reportOptions' => $options,
            'documentMeta' => $documentMeta,
            'reportImages' => $this->reportImages($issues, $options),
        ];
    }

    private function reportImages(Collection $issues, array $options): array
    {
        if (! $options['images']) {
            return [];
        }

        $paths = $issues->flatMap(function ($issue) use ($options): Collection {
            return $issue->attachments->pluck('path')
                ->concat($issue->wcagCriteria->pluck('pivot.screenshot_path'))
                ->concat($options['examples']
                    ? $issue->examples->flatMap(fn ($example) => $example->attachments->pluck('path'))
                    : []);
        })->filter()->unique();

        $disk = Storage::disk('public');
        $images = [];
        foreach ($paths as $path) {
            if (! $disk->exists($path)) {
                throw ValidationException::withMessages(['report' => __('Report image ":path" is missing. Restore or remove it before generating the report.', ['path' => $path])]);
            }

            $mime = $disk->mimeType($path);
            if (! in_array($mime, ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) {
                throw ValidationException::withMessages(['report' => __('Report image ":path" has an unsupported format. Replace it with PNG or JPEG.', ['path' => $path])]);
            }

            $images[$path] = 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
        }

        return $images;
    }

    private function severityRank(?string $severity): int
    {
        return ['critical' => 0, 'major' => 1, 'moderate' => 2, 'minor' => 3][$severity] ?? 4;
    }

    private function logoDataUri(?string $path, string $error): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            throw ValidationException::withMessages(['report' => $error]);
        }

        $mime = $disk->mimeType($path);
        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) {
            throw ValidationException::withMessages(['report' => $error]);
        }

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
    }

    private function calculateIssueCounts(Collection $issues): array
    {
        return $issues->countBy('severity')->toArray();
    }
}
