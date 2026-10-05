@php
    $severityOrder = ['critical', 'major', 'moderate', 'minor'];
    $issueStatuses = [
        'open' => __('Open report issues'),
        'resolved' => __('Resolved report issues'),
        'wont_fix' => __('Unfixed report issues'),
    ];
    $severityLabels = [
        'critical' => __('Critical report severity'),
        'major' => __('Major report severity'),
        'moderate' => __('Moderate report severity'),
        'minor' => __('Minor report severity'),
    ];
    $severityCountLabels = [
        'critical' => __('Critical report issues'),
        'major' => __('Major report issues'),
        'moderate' => __('Moderate report issues'),
        'minor' => __('Minor report issues'),
    ];
    $sampleScopeLabels = [
        'all' => __('Entire page or service'),
        'some' => __('Parts of the page or service'),
    ];
    $allReportIssues = collect($issues);
    $projectWideIssues = collect($projectWideIssues);
    $pageGroups = collect($pageGroups);
    $cleanPages = collect($cleanPages);
    $generatedAt = $documentMeta['generatedAt'];
    $documentVersion = $documentMeta['version'];
    $documentLogo = $documentMeta['logoPath'] ?? null;
    $descriptionSummaries = $allReportIssues->mapWithKeys(function ($issue) {
        $safeHtml = \App\Helpers\MarkdownHelper::toHtml($issue->description);
        $plainText = html_entity_decode(strip_tags($safeHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plainText = preg_replace('/\s+/u', ' ', trim($plainText)) ?? trim($plainText);
        $isTruncated = Str::length($plainText) > 320;

        return [
            $issue->getKey() => [
                'safeHtml' => $safeHtml,
                'excerpt' => Str::limit($plainText, 320),
                'isTruncated' => $isTruncated,
            ],
        ];
    });
    $longDescriptionIssues = $allReportIssues
        ->filter(fn ($issue) => $descriptionSummaries[$issue->getKey()]['isTruncated'] ?? false)
        ->values();
    $documentSections = collect();

    if ($projectWideIssues->isNotEmpty()) {
        $documentSections->push([
            'id' => 'project-wide-issues',
            'title' => __('Project-wide issues'),
            'page' => null,
            'issues' => $projectWideIssues,
        ]);
    }

    foreach ($pageGroups as $group) {
        if ($group['issues']->isEmpty()) {
            continue;
        }

        $page = $group['page'];
        $documentSections->push([
            'id' => 'page-'.Str::slug((string) $page->getKey()),
            'title' => $page->name,
            'page' => $page,
            'issues' => $group['issues'],
            'statusGroups' => $group['statusGroups'],
        ]);
    }

    $pagesWithIssues = $pageGroups->filter(fn ($group) => $group['issues']->isNotEmpty());
    $wcagIndex = $allReportIssues
        ->flatMap(fn ($issue) => collect($issue->wcagCriteria ?? [])
            ->map(fn ($criterion) => ['criterion' => $criterion, 'issue' => $issue]))
        ->groupBy(fn ($entry) => $entry['criterion']->standard.'|'.$entry['criterion']->number)
        ->sortKeys();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ __('Accessibility Audit Report') }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        .report-document,
        .report-document * {
            box-sizing: border-box;
        }
        .report-document {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            color: #27272a;
            color-scheme: light;
            font-family: Verdana, "DejaVu Sans", sans-serif;
            font-size: 14px;
            line-height: 1.7;
        }
        .report-document__paper {
            background: #fff;
            color: #27272a;
        }
        .report-document__cover,
        .report-document__header {
            border-bottom: 1px solid #d4d4d8;
            padding: 0 0 32px;
        }
        .report-document__brand {
            border-bottom: 3px solid #059669;
            margin-bottom: 28px;
            padding: 0 0 18px;
        }
        .report-document__logo {
            display: block;
            margin: 0 0 14px;
            max-height: 54px;
            max-width: 190px;
            object-fit: contain;
        }
        .report-document__site-icon {
            height: 24px;
            width: 24px;
            vertical-align: middle;
        }
        .report-document__eyebrow,
        .report-document__label {
            color: #71717a;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .report-document__title {
            color: #18181b;
            font-size: 30px;
            line-height: 1.2;
            margin: 5px 0 8px;
        }
        .report-document__identity {
            color: #52525b;
            font-size: 14px;
            margin: 0;
        }
        .report-document__identity-meta {
            color: #71717a;
            font-size: 11px;
            margin-top: 18px;
        }
        .report-document__identity-meta span {
            display: inline-block;
            margin-right: 16px;
        }
        .report-document__summary {
            border-collapse: collapse;
            margin: 26px 0 0;
            table-layout: fixed;
            width: 100%;
        }
        .report-document__summary td {
            border: 1px solid #e4e4e7;
            padding: 15px 10px;
            text-align: center;
            vertical-align: top;
        }
        .report-document__stat-value {
            color: #18181b;
            display: block;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
        }
        .report-document__stat-label {
            color: #71717a;
            display: block;
            font-size: 10px;
            font-weight: 700;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .report-document__section {
            margin-top: 38px;
        }
        .report-document__section--break {
            page-break-before: always;
        }
        .report-document__heading {
            border-bottom: 1px solid #d4d4d8;
            color: #18181b;
            font-size: 19px;
            line-height: 1.3;
            margin: 0 0 18px;
            padding-bottom: 10px;
        }
        .report-document__subheading {
            color: #27272a;
            font-size: 15px;
            margin: 0 0 12px;
        }
        .report-document__scope {
            background: #f4f4f5;
            border-left: 3px solid #059669;
            margin-top: 24px;
            padding: 16px 20px;
        }
        .report-document__scope .report-document__label {
            color: #047857;
            display: block;
            margin-bottom: 3px;
        }
        .report-document__rich-text {
            color: #3f3f46;
            overflow-wrap: anywhere;
        }
        .report-document__rich-text p {
            margin: 0 0 12px;
        }
        .report-document__rich-text ul,
        .report-document__rich-text ol {
            margin: 0 0 12px;
            padding-left: 22px;
        }
        .report-document__rich-text a,
        .report-document__link {
            color: #047857;
            text-decoration: underline;
        }
        .report-document__rich-text code,
        .report-document__code {
            background: #f4f4f5;
            font-family: monospace;
        }
        .report-document__toc {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .report-document__toc li {
            border-bottom: 1px solid #e4e4e7;
            padding: 10px 0;
        }
        .report-document__toc a {
            color: #047857;
            text-decoration: none;
        }
        .report-document__table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }
        .report-document__table th,
        .report-document__table td {
            border: 1px solid #d4d4d8;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }
        .report-document__table th {
            background: #f4f4f5;
            color: #3f3f46;
            font-size: 10px;
            text-transform: uppercase;
        }
        .report-document__page-name {
            color: #18181b;
            font-weight: 700;
        }
        .report-document__meta {
            color: #71717a;
            font-size: 11px;
            overflow-wrap: anywhere;
        }
        .report-document__badge {
            border: 1px solid #d4d4d8;
            border-radius: 3px;
            color: #3f3f46;
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            text-transform: uppercase;
        }
        .report-document__severity-critical {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .report-document__severity-major {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        .report-document__severity-moderate {
            background: #fefce8;
            border-color: #fef08a;
            color: #854d0e;
        }
        .report-document__severity-minor {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .report-document__group {
            margin: 32px 0;
        }
        .report-document__group-heading {
            background: #f4f4f5;
            border-left: 3px solid #059669;
            color: #18181b;
            font-size: 16px;
            margin: 0 0 16px;
            padding: 12px 14px;
            page-break-after: avoid;
        }
        .report-document__issue-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .report-document__issue {
            border: 1px solid #e4e4e7;
            margin: 0 0 20px;
            padding: 20px;
            page-break-inside: avoid;
        }
        .report-document__issue-heading {
            color: #18181b;
            font-size: 14px;
            margin: 0 0 10px;
        }
        .report-document__issue-meta {
            color: #71717a;
            font-size: 11px;
            margin: 0 0 12px;
        }
        .report-document__detail {
            margin-top: 16px;
        }
        .report-document__detail-title {
            color: #3f3f46;
            font-size: 10px;
            font-weight: 700;
            margin: 0 0 6px;
            text-transform: uppercase;
        }
        .report-document__status {
            color: #27272a;
            font-size: 14px;
            margin: 24px 0 12px;
            page-break-after: avoid;
        }
        .report-document__empty {
            color: #71717a;
            padding: 12px 0;
        }
        .report-document__appendix-item {
            border-top: 1px solid #e4e4e7;
            margin-top: 24px;
            padding-top: 18px;
            page-break-inside: avoid;
        }
        .report-document__evidence {
            background: #fafafa;
            border: 1px solid #e4e4e7;
            margin-top: 12px;
            padding: 14px;
        }
        .report-document__media {
            border: 1px solid #d4d4d8;
            display: block;
            height: auto;
            margin: 8px 0;
            max-width: 100%;
        }
        .report-document__media--large {
            max-height: 540px;
            width: auto;
        }
        .report-document__code {
            border: 1px solid #e4e4e7;
            color: #27272a;
            font-size: 10px;
            margin: 12px 0;
            overflow-wrap: anywhere;
            padding: 14px;
            white-space: pre-wrap;
        }
        .report-document__diagram img {
            display: block;
            height: auto;
            max-width: 100%;
        }
        .report-document__footer {
            border-top: 1px solid #d4d4d8;
            color: #71717a;
            font-size: 10px;
            margin-top: 40px;
            padding-top: 16px;
            text-align: center;
        }
        @media print {
            .report-document {
                max-width: none;
            }
            .report-document__link,
            .report-document__toc a {
                color: #047857;
            }
            .report-document__issue,
            .report-document__appendix-item {
                page-break-inside: avoid;
            }
        }
        @media (max-width: 640px) {
            .report-document__summary td {
                padding: 10px 4px;
            }
            .report-document__stat-value {
                font-size: 16px;
            }
            .report-document__table th,
            .report-document__table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <main class="report-document" data-report-template="{{ $templateVersion }}">
        <article class="report-document__paper">
            <header class="report-document__cover report-document__header">
                <div class="report-document__brand">
                    @if ($documentMeta['brandLogoPath'] ?? null)
                        <img class="report-document__logo" src="{{ $documentMeta['brandLogoPath'] }}" alt="{{ __('Company logo') }}">
                    @endif
                    <div class="report-document__eyebrow">{{ __('Accessibility Audit Report') }}</div>
                    <h1 class="report-document__title">{{ $title }}</h1>
                    <p class="report-document__identity">{{ $project->name }}</p>
                    @if ($documentLogo)
                        <img class="report-document__logo" src="{{ $documentLogo }}" alt="{{ __(':name logo', ['name' => $project->name]) }}">
                    @endif
                    @if ($documentMeta['siteIconPath'] ?? null)
                        <p class="report-document__identity">
                            <img class="report-document__site-icon" src="{{ $documentMeta['siteIconPath'] }}" alt="{{ __('Website icon') }}">
                            {{ $documentMeta['siteUrl'] }}
                        </p>
                    @endif
                    @if ($project->team?->name)
                        <p class="report-document__identity">{{ $project->team->name }}</p>
                    @endif
                    <div class="report-document__identity-meta">
                        <span>{{ __('Generated') }}: {{ $generatedAt->translatedFormat('j M Y, H:i') }}</span>
                        <span>{{ __('Version :version', ['version' => $documentVersion]) }}</span>
                        @if (! empty($documentMeta['auditDate']))
                            <span>{{ __('Audit date') }}: {{ $documentMeta['auditDate']->translatedFormat('j M Y') }}</span>
                        @endif
                        @if (! empty($documentMeta['creator']))
                            <span>{{ __('Prepared by') }}: {{ $documentMeta['creator'] }}</span>
                        @endif
                    </div>
                </div>

                <table class="report-document__summary" aria-label="{{ __('Report Summary') }}">
                    <tbody>
                        <tr>
                            <td>
                                <span class="report-document__stat-value">{{ $allReportIssues->count() }}</span>
                                <span class="report-document__stat-label">{{ __('Total Issues') }}</span>
                            </td>
                            @foreach ($severityOrder as $severity)
                                <td>
                                    <span class="report-document__stat-value">{{ $counts[$severity] ?? $allReportIssues->where('severity', $severity)->count() }}</span>
                                    <span class="report-document__stat-label">{{ $severityCountLabels[$severity] }}</span>
                                </td>
                            @endforeach
                            <td>
                                <span class="report-document__stat-value">{{ $project->target_wcag_level }}</span>
                                <span class="report-document__stat-label">{{ __('Target Level') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if ($scope)
                    <div class="report-document__scope">
                        <span class="report-document__label">{{ __('Report scope') }}</span>
                        <div>{{ $scope }}</div>
                    </div>
                @endif
            </header>

            <section class="report-document__section" aria-labelledby="summary-heading">
                <h2 id="summary-heading" class="report-document__heading">{{ __('Executive Summary') }}</h2>
                <table class="report-document__summary" aria-label="{{ __('Issue status summary') }}">
                    <tbody>
                        <tr>
                            <td>
                                <span class="report-document__stat-value">{{ $allReportIssues->where('status', 'open')->count() }}</span>
                                <span class="report-document__stat-label">{{ $issueStatuses['open'] }}</span>
                            </td>
                            <td>
                                <span class="report-document__stat-value">{{ $allReportIssues->where('status', 'resolved')->count() }}</span>
                                <span class="report-document__stat-label">{{ $issueStatuses['resolved'] }}</span>
                            </td>
                            <td>
                                <span class="report-document__stat-value">{{ $allReportIssues->where('status', 'wont_fix')->count() }}</span>
                                <span class="report-document__stat-label">{{ $issueStatuses['wont_fix'] }}</span>
                            </td>
                            <td>
                                <span class="report-document__stat-value">{{ $pagesWithIssues->count() }}</span>
                                <span class="report-document__stat-label">{{ __('Pages with issues') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="report-document__meta">{{ __('Prioritize open critical and major issues before lower-severity findings.') }}</p>
                @if ($project->description)
                    <div class="report-document__detail">
                        <h3 class="report-document__detail-title">{{ __('Description') }}</h3>
                        <div class="report-document__rich-text">{!! \App\Helpers\MarkdownHelper::toHtml($project->description) !!}</div>
                    </div>
                @endif
            </section>

            <nav class="report-document__section" aria-labelledby="contents-heading">
                <h2 id="contents-heading" class="report-document__heading">{{ __('Table of Contents') }}</h2>
                <ul class="report-document__toc">
                    @if ($projectWideIssues->isNotEmpty())
                        <li><a href="#project-wide-issues">{{ __('Project-wide issues') }}</a></li>
                    @endif
                    @if ($pagesWithIssues->isNotEmpty())
                        <li><a href="#pages-with-issues">{{ __('Pages with issues') }}</a></li>
                        @foreach ($documentSections->whereNotNull('page') as $section)
                            <li><a href="#{{ $section['id'] }}">{{ $section['title'] }}</a></li>
                        @endforeach
                    @endif
                    @if ($longDescriptionIssues->isNotEmpty())
                        <li><a href="#full-descriptions-appendix">{{ __('Full issue descriptions') }}</a></li>
                    @endif
                    @if (($reportOptions['methodologies'] ?? false) && collect($project->methodologies ?? [])->isNotEmpty())
                        <li><a href="#methodology-appendix">{{ __('Testing Methodology') }}</a></li>
                    @endif
                    @if (($reportOptions['clean_pages'] ?? false) && $cleanPages->isNotEmpty())
                        <li><a href="#clean-pages">{{ __('Clean pages') }}</a></li>
                    @endif
                    @if (($reportOptions['evidence'] ?? false) || ($reportOptions['images'] ?? false) || ($reportOptions['code'] ?? false) || ($reportOptions['diagrams'] ?? false) || ($reportOptions['examples'] ?? false) || ($reportOptions['resolution'] ?? false) || ($reportOptions['scope'] ?? false))
                        <li><a href="#evidence-appendix">{{ __('Evidence Appendix') }}</a></li>
                    @endif
                    @if (($reportOptions['wcag_index'] ?? false) && $wcagIndex->isNotEmpty())
                        <li><a href="#wcag-index">{{ __('WCAG index') }}</a></li>
                    @endif
                </ul>
            </nav>

            @if ($allReportIssues->isEmpty())
                <p class="report-document__empty">{{ __('No issues found in this project.') }}</p>
            @endif

            @if ($projectWideIssues->isNotEmpty())
                <section id="project-wide-issues" class="report-document__section" aria-labelledby="project-wide-heading">
                    <h2 id="project-wide-heading" class="report-document__heading">{{ __('Project-wide issues') }}</h2>
                    <x-reports.issue-sections
                        :status-groups="$projectWideIssues->groupBy('status')"
                        :description-summaries="$descriptionSummaries"
                        :severity-order="$severityOrder"
                        :severity-labels="$severityLabels"
                        :sample-scope-labels="$sampleScopeLabels"
                        :issue-statuses="$issueStatuses"
                        :report-options="$reportOptions"
                        :target-wcag-version="$project->target_wcag_version"
                    />
                </section>
            @endif

            @if ($pagesWithIssues->isNotEmpty())
                <section id="pages-with-issues" class="report-document__section" aria-labelledby="pages-heading">
                    <h2 id="pages-heading" class="report-document__heading">{{ __('Pages with issues') }}</h2>
                    <table class="report-document__table">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Page') }}</th>
                                <th scope="col">{{ __('Issue count') }}</th>
                                <th scope="col">{{ __('Highest severity') }}</th>
                                <th scope="col">{{ __('Open') }} / {{ __('Closed') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pagesWithIssues as $group)
                                @php
                                    $page = $group['page'];
                                    $pageId = 'page-'.Str::slug((string) $page->getKey());
                                @endphp
                                <tr>
                                    <td>
                                        <a class="report-document__link" href="#{{ $pageId }}">{{ $page->name }}</a>
                                        @if ($page->url)
                                            <div class="report-document__meta">{{ $page->url }}</div>
                                        @endif
                                        <div class="report-document__meta">
                                            {{ __($page->resource_type === 'service' ? 'Service' : 'Page') }}
                                            · {{ __($page->scope === 'in_scope' ? 'In Scope' : 'Out of Scope') }}
                                            @if ($page->access_context && $page->access_context !== 'not_applicable')
                                                · {{ __($page->access_context === 'authenticated' ? 'Authenticated' : ($page->access_context === 'mixed' ? 'Public and authenticated' : 'Public')) }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ collect($group['issues'])->count() }}</td>
                                    <td><span class="report-document__badge report-document__severity-{{ strtolower($group['highestSeverity'] ?? '') }}">{{ $severityLabels[$group['highestSeverity']] ?? '' }}</span></td>
                                    <td>{{ $group['issues']->where('status', 'open')->count() }} / {{ $group['issues']->whereIn('status', ['resolved', 'wont_fix'])->count() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @foreach ($documentSections->whereNotNull('page') as $section)
                        <section id="{{ $section['id'] }}" class="report-document__group" aria-labelledby="{{ $section['id'] }}-heading">
                            <h3 id="{{ $section['id'] }}-heading" class="report-document__group-heading">{{ $section['title'] }}</h3>
                            @if ($section['page']->url)
                                <p class="report-document__meta"><a class="report-document__link" href="{{ $section['page']->url }}">{{ $section['page']->url }}</a></p>
                            @endif
                            <x-reports.issue-sections
                                :issues="$section['issues']"
                                :status-groups="$section['statusGroups']"
                                :description-summaries="$descriptionSummaries"
                                :severity-order="$severityOrder"
                                :severity-labels="$severityLabels"
                                :sample-scope-labels="$sampleScopeLabels"
                                :issue-statuses="$issueStatuses"
                                :report-options="$reportOptions"
                                :target-wcag-version="$project->target_wcag_version"
                            />
                        </section>
                    @endforeach
                </section>
            @endif

            @if (($reportOptions['methodologies'] ?? false) && collect($project->methodologies ?? [])->isNotEmpty())
                <section id="methodology-appendix" class="report-document__section report-document__section--break" aria-labelledby="methodology-heading">
                    <h2 id="methodology-heading" class="report-document__heading">{{ __('Testing Methodology') }}</h2>
                    @foreach ($project->methodologies as $methodology)
                        <article class="report-document__appendix-item">
                            <h3 class="report-document__subheading">{{ $methodology->name }}</h3>
                            <p class="report-document__meta">{{ $methodology->category_label }}</p>
                            @if ($methodology->description)
                                <div class="report-document__rich-text">{!! \App\Helpers\MarkdownHelper::toHtml($methodology->description) !!}</div>
                            @endif
                            @if ($methodology->pivot?->notes)
                                <p>{{ $methodology->pivot->notes }}</p>
                            @endif
                        </article>
                    @endforeach
                </section>
            @endif

            @if (($reportOptions['clean_pages'] ?? false) && $cleanPages->isNotEmpty())
                <section id="clean-pages" class="report-document__section report-document__section--break" aria-labelledby="clean-pages-heading">
                    <h2 id="clean-pages-heading" class="report-document__heading">{{ __('Clean pages') }}</h2>
                    <table class="report-document__table">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Page') }}</th>
                                <th scope="col">{{ __('URL') }}</th>
                                <th scope="col">{{ __('Scope') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cleanPages as $page)
                                <tr>
                                    <td>
                                        <span class="report-document__page-name">{{ $page->name }}</span>
                                        <div class="report-document__meta">{{ __($page->resource_type === 'service' ? 'Service' : 'Page') }}</div>
                                        @if ($page->description)
                                            <div class="report-document__rich-text">{!! \App\Helpers\MarkdownHelper::toHtml($page->description) !!}</div>
                                        @endif
                                    </td>
                                    <td>{{ $page->url }}</td>
                                    <td>
                                        {{ __($page->scope === 'in_scope' ? 'In Scope' : 'Out of Scope') }}
                                        @if ($page->access_context && $page->access_context !== 'not_applicable')
                                            <div class="report-document__meta">{{ __($page->access_context === 'authenticated' ? 'Authenticated' : ($page->access_context === 'mixed' ? 'Public and authenticated' : 'Public')) }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif

            @if ($longDescriptionIssues->isNotEmpty())
                <section id="full-descriptions-appendix" class="report-document__section report-document__section--break" aria-labelledby="full-descriptions-heading">
                    <h2 id="full-descriptions-heading" class="report-document__heading">{{ __('Full issue descriptions') }}</h2>
                    @foreach ($longDescriptionIssues as $issue)
                        <article class="report-document__appendix-item">
                            <h3 class="report-document__subheading"><a class="report-document__link" href="#issue-{{ $issue->getKey() }}">{{ $issue->title }}</a></h3>
                            <div class="report-document__rich-text">{!! $descriptionSummaries[$issue->getKey()]['safeHtml'] !!}</div>
                        </article>
                    @endforeach
                </section>
            @endif

            @if (($reportOptions['evidence'] ?? false) || ($reportOptions['images'] ?? false) || ($reportOptions['code'] ?? false) || ($reportOptions['diagrams'] ?? false) || ($reportOptions['examples'] ?? false) || ($reportOptions['resolution'] ?? false) || ($reportOptions['scope'] ?? false))
                <section id="evidence-appendix" class="report-document__section report-document__section--break" aria-labelledby="evidence-heading">
                    <h2 id="evidence-heading" class="report-document__heading">{{ __('Evidence Appendix') }}</h2>
                    @foreach ($allReportIssues as $issue)
                        @php
                            $issueCriteria = collect($issue->wcagCriteria ?? []);
                            $issueExamples = collect($issue->examples ?? []);
                            $issueAttachments = collect($issue->attachments ?? []);
                            $hasEvidence = (($reportOptions['evidence'] ?? false) && $issueCriteria->isNotEmpty())
                                || (($reportOptions['images'] ?? false) && ($issueAttachments->isNotEmpty() || $issueCriteria->contains(fn ($criterion) => $criterion->pivot?->screenshot_path)))
                                || (($reportOptions['code'] ?? false) && ($issue->problem_code_snippet || $issue->corrected_code_snippet || $issueCriteria->contains(fn ($criterion) => $criterion->pivot?->code_snippet) || $issueExamples->contains(fn ($example) => $example->code_language !== 'mermaid' && $example->code_snippet)))
                                || (($reportOptions['diagrams'] ?? false) && $issue->mermaid_svg)
                                || (($reportOptions['examples'] ?? false) && $issueExamples->isNotEmpty())
                                || (($reportOptions['resolution'] ?? false) && ($issue->resolution_notes ?? null))
                                || (($reportOptions['scope'] ?? false) && ($issue->sample_scope ?? null));
                        @endphp
                        @if ($hasEvidence)
                            <article class="report-document__appendix-item">
                                <h3 class="report-document__subheading"><a class="report-document__link" href="#issue-{{ $issue->getKey() }}">{{ $issue->title }}</a></h3>
                                @if (($reportOptions['evidence'] ?? false) && $issueCriteria->isNotEmpty())
                                    <h4 class="report-document__detail-title">{{ __('WCAG evidence') }}</h4>
                                    @foreach ($issueCriteria as $criterion)
                                        <div class="report-document__evidence">
                                            <strong>{{ $criterion->standard }} {{ $criterion->number }} · {{ $criterion->name_sv ?? $criterion->name_en }}</strong>
                                            @if ($criterion->isOutsideProjectTargetVersion($project->target_wcag_version))
                                                <strong> — {{ __('Nice to have') }}</strong>
                                            @endif
                                            <span> ({{ __('Level') }} {{ Str::upper($criterion->level) }})</span>
                                            @if ($criterion->pivot?->failure_type)
                                                <p>{{ __('Failure type') }}: {{ __($criterion->pivot->failure_type) }}</p>
                                            @endif
                                            @if ($criterion->pivot?->comment)
                                                <p>{{ __('Evidence') }}: {{ $criterion->pivot->comment }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                @if (($reportOptions['images'] ?? false) && $issueAttachments->isNotEmpty())
                                    <h4 class="report-document__detail-title">{{ __('Screenshots') }}</h4>
                                    @foreach ($issueAttachments as $attachment)
                                        <figure class="report-document__evidence">
                                            <img class="report-document__media report-document__media--large" src="{{ $reportImages[$attachment->path] }}" alt="{{ $attachment->original_filename }}">
                                            <figcaption class="report-document__meta">{{ $attachment->original_filename }}</figcaption>
                                        </figure>
                                    @endforeach
                                @endif

                                @if (($reportOptions['images'] ?? false) && $issueCriteria->contains(fn ($criterion) => $criterion->pivot?->screenshot_path))
                                    <h4 class="report-document__detail-title">{{ __('WCAG screenshots') }}</h4>
                                    @foreach ($issueCriteria as $criterion)
                                        @if ($criterion->pivot?->screenshot_path)
                                            <figure class="report-document__evidence">
                                                <img class="report-document__media report-document__media--large" src="{{ $reportImages[$criterion->pivot->screenshot_path] }}" alt="{{ __('WCAG failure evidence for :criterion', ['criterion' => $criterion->standard.' '.$criterion->number]) }}">
                                                <figcaption class="report-document__meta">{{ $criterion->standard }} {{ $criterion->number }}</figcaption>
                                            </figure>
                                        @endif
                                    @endforeach
                                @endif

                                @if (($reportOptions['code'] ?? false) && ($issue->problem_code_snippet || $issue->corrected_code_snippet))
                                    <h4 class="report-document__detail-title">{{ __('Issue-specific code') }}</h4>
                                    @if ($issue->problem_code_snippet)
                                        <div class="report-document__evidence">
                                            <strong>{{ __('Code causing the issue') }}</strong>
                                            <pre class="report-document__code">{{ $issue->problem_code_snippet }}</pre>
                                        </div>
                                    @endif
                                    @if ($issue->corrected_code_snippet)
                                        <div class="report-document__evidence">
                                            <strong>{{ __('Suggested corrected code') }}</strong>
                                            <pre class="report-document__code">{{ $issue->corrected_code_snippet }}</pre>
                                        </div>
                                    @endif
                                @endif

                                @if (($reportOptions['code'] ?? false) && $issueCriteria->contains(fn ($criterion) => $criterion->pivot?->code_snippet))
                                    <h4 class="report-document__detail-title">{{ __('Code examples') }}</h4>
                                    @foreach ($issueCriteria as $criterion)
                                        @if ($criterion->pivot?->code_snippet)
                                            <pre class="report-document__code">{{ $criterion->pivot->code_snippet }}</pre>
                                        @endif
                                    @endforeach
                                @endif

                                @if (($reportOptions['diagrams'] ?? false) && $issue->mermaid_svg)
                                    <h4 class="report-document__detail-title">{{ __('Issue diagram') }}</h4>
                                    <div class="report-document__evidence report-document__diagram">
                                        <img src="data:image/svg+xml;base64,{{ base64_encode($issue->mermaid_svg) }}" alt="{{ __('Diagram for :title', ['title' => $issue->title]) }}">
                                    </div>
                                @endif

                                @if (($reportOptions['examples'] ?? false) && $issueExamples->isNotEmpty())
                                    <h4 class="report-document__detail-title">{{ __('Knowledge Base Examples') }}</h4>
                                    @foreach ($issueExamples as $example)
                                        <div class="report-document__evidence">
                                            <strong>{{ $example->title }}</strong>
                                            @if ($example->description)
                                                <div class="report-document__rich-text">{!! \App\Helpers\MarkdownHelper::toHtml($example->description) !!}</div>
                                            @endif
                                            @if (($reportOptions['diagrams'] ?? false) && $example->code_language === 'mermaid' && $example->mermaid_svg)
                                                <img class="report-document__media report-document__media--large" src="data:image/svg+xml;base64,{{ base64_encode($example->mermaid_svg) }}" alt="{{ __('Diagram for :title', ['title' => $example->title]) }}">
                                            @endif
                                            @if (($reportOptions['code'] ?? false) && $example->code_language !== 'mermaid' && $example->code_snippet)
                                                <pre class="report-document__code">{{ $example->code_snippet }}</pre>
                                            @endif
                                            @if ($example->url)
                                                <p><a class="report-document__link" href="{{ $example->url }}">{{ $example->url_label ?: $example->url }}</a></p>
                                            @endif
                                            @foreach ($example->referenceLinks ?? [] as $link)
                                                <p><a class="report-document__link" href="{{ $link->url }}">{{ $link->label ?: $link->url }}</a></p>
                                            @endforeach
                                            @if (($reportOptions['images'] ?? false))
                                                @foreach ($example->attachments ?? [] as $attachment)
                                                    <img class="report-document__media report-document__media--large" src="{{ $reportImages[$attachment->path] }}" alt="{{ $attachment->alt_text ?: $attachment->original_filename }}">
                                                @endforeach
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                @if (($reportOptions['resolution'] ?? false) && ($issue->resolution_notes ?? null))
                                    <h4 class="report-document__detail-title">{{ __('Resolution Notes') }}</h4>
                                    <div class="report-document__rich-text">{!! \App\Helpers\MarkdownHelper::toHtml($issue->resolution_notes) !!}</div>
                                @endif
                                @if (($reportOptions['scope'] ?? false) && $issue->sample_scope)
                                    <h4 class="report-document__detail-title">{{ __('Sample scope') }}</h4>
                                    <p>{{ $sampleScopeLabels[$issue->sample_scope] ?? throw new \UnexpectedValueException('Unsupported issue sample scope') }}</p>
                                @endif
                            </article>
                        @endif
                    @endforeach
                </section>
            @endif

            @if (($reportOptions['wcag_index'] ?? false) && $wcagIndex->isNotEmpty())
                <section id="wcag-index" class="report-document__section report-document__section--break" aria-labelledby="wcag-index-heading">
                    <h2 id="wcag-index-heading" class="report-document__heading">{{ __('WCAG index') }}</h2>
                    @foreach ($wcagIndex as $entries)
                        @php
                            $criterion = $entries->first()['criterion'];
                        @endphp
                        <article id="wcag-{{ Str::slug($criterion->standard.'-'.$criterion->number) }}" class="report-document__appendix-item">
                            <h3 class="report-document__subheading">{{ $criterion->standard }} {{ $criterion->number }} · {{ $criterion->name_sv ?? $criterion->name_en }}</h3>
                            @if ($criterion->isOutsideProjectTargetVersion($project->target_wcag_version))
                                <p><strong>{{ __('Nice to have') }}</strong></p>
                            @endif
                            <p class="report-document__meta">{{ __('Level') }} {{ Str::upper($criterion->level) }}</p>
                            @foreach ($entries as $entry)
                                <p><a class="report-document__link" href="#issue-{{ $entry['issue']->getKey() }}">{{ $entry['issue']->title }}</a></p>
                            @endforeach
                        </article>
                    @endforeach
                </section>
            @endif

            <footer class="report-document__footer">
                <p>{{ __('Report generated on :date', ['date' => $generatedAt->translatedFormat('j M Y, H:i')]) }}</p>
                <p>{{ __('Version :version', ['version' => $documentVersion]) }}</p>
            </footer>
        </article>
    </main>
</body>
</html>
