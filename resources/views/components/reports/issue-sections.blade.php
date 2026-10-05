@props(['statusGroups', 'severityOrder', 'severityLabels', 'sampleScopeLabels', 'issueStatuses', 'reportOptions', 'descriptionSummaries', 'targetWcagVersion'])

@foreach ($issueStatuses as $status => $statusLabel)
    @php
        $statusIssues = collect($statusGroups)->get($status, collect());
        $severityGroups = $statusIssues->groupBy('severity')->sortBy(function ($severityIssues, $severity) use ($severityOrder) {
            $order = array_search($severity, $severityOrder, true);

            return $order === false ? count($severityOrder) : $order;
        });
    @endphp

    @if ($statusIssues->isNotEmpty())
        <h4 class="report-document__status">{{ $statusLabel }}</h4>
        @foreach ($severityGroups as $severity => $severityIssues)
            <h5 class="report-document__detail-title">{{ $severityLabels[$severity] ?? $severity }}</h5>
            <ul class="report-document__issue-list">
                @foreach ($severityIssues as $issue)
                    @php
                        $criteria = collect($issue->wcagCriteria ?? []);
                        $severityLabel = $severityLabels[$issue->severity] ?? $issue->severity;
                    @endphp
                    <li id="issue-{{ $issue->getKey() }}" class="report-document__issue">
                        <h6 class="report-document__issue-heading">
                            <span class="report-document__badge report-document__severity-{{ strtolower($issue->severity) }}">{{ $severityLabel }}</span>
                            {{ $issue->title }}
                        </h6>
                        <p class="report-document__issue-meta">
                            {{ $issue->page?->name ?? __('Project-wide issue') }}
                            @if ($issue->component_area)
                                · {{ __('Component') }}: {{ $issue->component_area }}
                            @endif
                            · {{ __('Status') }}: {{ match ($status) {
                                'open' => __('Open report status'),
                                'resolved' => __('Resolved report status'),
                                default => __('Unfixed report status'),
                            } }}
                        </p>

                        @if ($issue->description)
                            <div class="report-document__detail">
                                <p class="report-document__detail-title">{{ __('Summary') }}</p>
                                <p>{{ $descriptionSummaries[$issue->getKey()]['excerpt'] }}</p>
                            </div>
                        @endif

                        @if (filled($issue->impact ?? null))
                            <div class="report-document__detail">
                                <p class="report-document__detail-title">{{ __('Impact') }}</p>
                                <div class="report-document__rich-text">{!! \App\Helpers\MarkdownHelper::toHtml($issue->impact) !!}</div>
                            </div>
                        @endif

                        @if ($issue->solution_suggestions)
                            <div class="report-document__detail">
                                <p class="report-document__detail-title">{{ __('Recommended action') }}</p>
                                <div class="report-document__rich-text">{!! \App\Helpers\MarkdownHelper::toHtml($issue->solution_suggestions) !!}</div>
                            </div>
                        @endif

                        @if ($criteria->isNotEmpty())
                            <div class="report-document__detail">
                                <p class="report-document__detail-title">{{ __('WCAG Criteria') }}</p>
                                @foreach ($criteria as $criterion)
                                    @if ($reportOptions['wcag_index'] ?? false)
                                        <a class="report-document__link" href="#wcag-{{ Str::slug($criterion->standard.'-'.$criterion->number) }}">{{ $criterion->standard }} {{ $criterion->number }}</a>
                                    @else
                                        <span>{{ $criterion->standard }} {{ $criterion->number }}</span>
                                    @endif
                                    <span> · {{ $criterion->name_sv ?? $criterion->name_en }}</span>
                                    @if ($criterion->isOutsideProjectTargetVersion($targetWcagVersion))
                                        <strong> — {{ __('Nice to have') }}</strong>
                                    @endif
                                    @if (! $loop->last)
                                        <span>, </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if (($reportOptions['scope'] ?? false) && $issue->sample_scope)
                            <div class="report-document__detail">
                                <p class="report-document__detail-title">{{ __('Sample scope') }}</p>
                                <p>{{ $sampleScopeLabels[$issue->sample_scope] ?? throw new \UnexpectedValueException('Unsupported issue sample scope') }}</p>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endforeach
    @endif
@endforeach
