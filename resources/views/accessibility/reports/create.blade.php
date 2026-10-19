<x-app-layout>
    <x-slot:title>{{ __('Generate Report for :project', ['project' => $project->name]) }}</x-slot:title>

    <div class="mx-auto w-full max-w-[1600px] space-y-6 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="flex flex-col gap-4 border-b border-zinc-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-800">
            <div>
                <flux:heading level="1">{{ __('Generate Report for :project', ['project' => $project->name]) }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Generated reports') }}</flux:text>
            </div>
            <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
        </div>

        <div class="max-w-3xl rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10">
            <section aria-labelledby="report-readiness-heading" class="mb-8 space-y-4">
                <div>
                    <flux:heading id="report-readiness-heading" level="2">{{ __('Review report readiness') }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ __('Warnings do not prevent report creation. Creating a report saves an immutable snapshot; later project or issue changes require a new report version.') }}
                    </flux:text>
                </div>

                <dl class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Included pages and services') }}</dt>
                        <dd class="mt-1 text-lg font-semibold">{{ $reportPreflight['included_pages']->count() }}</dd>
                    </div>
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Excluded pages and services') }}</dt>
                        <dd class="mt-1 text-lg font-semibold">{{ $reportPreflight['excluded_pages']->count() }}</dd>
                    </div>
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Findings included') }}</dt>
                        <dd class="mt-1 text-lg font-semibold">{{ $reportPreflight['included_issue_count'] }}</dd>
                    </div>
                </dl>

                @if ($reportPreflight['has_warnings'])
                    <div class="space-y-3 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-100" role="status">
                        <flux:heading level="3" size="sm">{{ __('Review recommended') }}</flux:heading>
                        <ul class="list-disc space-y-3 pl-5">
                            @if (! $reportPreflight['scope_recorded'])
                                <li>
                                    {{ __('No structured project scope is recorded. The report will still include the target WCAG version and resource list.') }}
                                    <a class="font-medium underline" href="{{ route('accessibility-projects.edit', $project) }}">{{ __('Edit project scope') }}</a>
                                </li>
                            @endif

                            @if ($reportPreflight['included_pages']->isEmpty())
                                <li>
                                    {{ __('No pages or services are marked as included. The report will contain project-wide findings only.') }}
                                    <a class="font-medium underline" href="{{ route('accessibility-projects.show', $project) }}#pages-services">{{ __('Review pages and services') }}</a>
                                </li>
                            @endif

                            @if ($reportPreflight['pages_without_scope_decision']->isNotEmpty())
                                <li>
                                    {{ __('Pages or services without a valid scope decision: :count.', ['count' => $reportPreflight['pages_without_scope_decision']->count()]) }}
                                    @foreach ($reportPreflight['pages_without_scope_decision'] as $page)
                                        <a class="font-medium underline" href="{{ route('accessibility-pages.edit', [$project, $page]) }}">{{ $page->name }}</a>@if (! $loop->last); @endif
                                    @endforeach
                                </li>
                            @endif

                            @if ($reportPreflight['excluded_pages_without_reason']->isNotEmpty())
                                <li>
                                    {{ __('Excluded pages or services without an explanation: :count.', ['count' => $reportPreflight['excluded_pages_without_reason']->count()]) }}
                                    @foreach ($reportPreflight['excluded_pages_without_reason'] as $page)
                                        <a class="font-medium underline" href="{{ route('accessibility-pages.edit', [$project, $page]) }}">{{ $page->name }}</a>@if (! $loop->last); @endif
                                    @endforeach
                                </li>
                            @endif

                            @if ($reportPreflight['issues_without_summary']->isNotEmpty())
                                <li>
                                    {{ __('Findings without a short summary: :count.', ['count' => $reportPreflight['issues_without_summary']->count()]) }}
                                    <ul class="mt-1 list-disc space-y-1 pl-5">
                                        @foreach ($reportPreflight['issues_without_summary']->take(5) as $issue)
                                            <li><a class="font-medium underline" href="{{ route('accessibility-issues.edit', [$project, $issue]) }}">{{ $issue->title }}</a></li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if ($reportPreflight['issues_without_wcag']->isNotEmpty())
                                <li>
                                    {{ __('Findings without a WCAG success criterion: :count.', ['count' => $reportPreflight['issues_without_wcag']->count()]) }}
                                    <ul class="mt-1 list-disc space-y-1 pl-5">
                                        @foreach ($reportPreflight['issues_without_wcag']->take(5) as $issue)
                                            <li><a class="font-medium underline" href="{{ route('accessibility-issues.edit', [$project, $issue]) }}">{{ $issue->title }}</a></li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if ($reportPreflight['excluded_issues']->isNotEmpty())
                                <li>
                                    {{ __('Findings on excluded pages are omitted from this report: :count.', ['count' => $reportPreflight['excluded_issues']->count()]) }}
                                    <a class="font-medium underline" href="{{ route('accessibility-projects.show', $project) }}#pages-services">{{ __('Review pages and services') }}</a>
                                </li>
                            @endif

                            @if ($reportPreflight['issues_with_stale_diagrams']->isNotEmpty())
                                <li>
                                    {{ __('Issue diagrams need to be rendered again before they can be included: :count.', ['count' => $reportPreflight['issues_with_stale_diagrams']->count()]) }}
                                    @foreach ($reportPreflight['issues_with_stale_diagrams'] as $issue)
                                        <a class="font-medium underline" href="{{ route('accessibility-issues.edit', [$project, $issue]) }}">{{ $issue->title }}</a>@if (! $loop->last); @endif
                                    @endforeach
                                </li>
                            @endif

                            @if ($reportPreflight['examples_with_stale_diagrams']->isNotEmpty())
                                <li>
                                    {{ __('Knowledge base diagrams need to be rendered again before they can be included: :count.', ['count' => $reportPreflight['examples_with_stale_diagrams']->count()]) }}
                                    <a class="font-medium underline" href="{{ route('wcag.knowledge-base') }}">{{ __('Open WCAG knowledge base') }}</a>
                                </li>
                            @endif

                            @if ($reportPreflight['methodology_count'] === 0)
                                <li>
                                    {{ __('No testing methodology is linked to this project.') }}
                                    <a class="font-medium underline" href="{{ route('accessibility-projects.show', $project) }}#testing-methodology">{{ __('Review testing methodologies') }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @else
                    <div class="rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-950 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-100" role="status">
                        {{ __('No report readiness warnings were found.') }}
                    </div>
                @endif
            </section>

            <flux:callout color="zinc" class="mb-6">
                <flux:callout.text>{{ __('The project scope, target WCAG level, included and excluded resources, and core issue details are always included.') }}</flux:callout.text>
            </flux:callout>

            <form method="POST" action="{{ route('accessibility-reports.store', $project) }}" class="space-y-6">
                @csrf
                <flux:error name="report"/>

                <flux:field>
                    <flux:label>{{ __('Report Title') }}</flux:label>
                    <flux:input name="title" type="text" placeholder="{{ __('e.g., Initial Accessibility Audit') }}" value="{{ old('title') }}" required/>
                    <flux:error name="title"/>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Report-specific notes') }}</flux:label>
                    <flux:textarea name="scope_notes" placeholder="{{ __('Add context that applies only to this report version...') }}" rows="4">{{ old('scope_notes', old('scope')) }}</flux:textarea>
                    <flux:error name="scope_notes"/>
                    <flux:description>{{ __('The report automatically includes the project scope, target WCAG level, and included or excluded pages and services.') }}</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Primary website for report icon') }}</flux:label>
                    <flux:select name="site_url">
                        <option value="">{{ __('No website icon') }}</option>
                        @foreach ($reportSites as $siteUrl => $label)
                            <option value="{{ $siteUrl }}" @selected(old('site_url', $project->report_site_url) === $siteUrl)>{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:description>{{ __('Select an HTTPS website from this project. Its icon is saved in each new report version.') }}</flux:description>
                    <flux:error name="site_url" />
                </flux:field>

                @php
                    $optionGroups = [
                        [
                            'title' => __('Problem details'),
                            'description' => __('Add structured evidence and status information for the findings included in the report.'),
                            'options' => [
                                'evidence' => [
                                    'label' => __('Evidence and failure type'),
                                    'description' => __('WCAG criteria, assessment method, and criterion-linked notes.'),
                                ],
                                'sample_scope' => [
                                    'label' => __('Issue sample scope'),
                                    'description' => __('Whether each finding applies to all or selected occurrences.'),
                                ],
                                'resolution' => [
                                    'label' => __('Resolution details'),
                                    'description' => __('Resolution notes recorded for findings.'),
                                ],
                            ],
                        ],
                        [
                            'title' => __('Attachments and examples'),
                            'description' => __('Include supporting media and references attached to findings and linked examples.'),
                            'options' => [
                                'images' => [
                                    'label' => __('Screenshots and attachments'),
                                    'description' => __('Issue screenshots, criterion screenshots, and knowledge-base attachments.'),
                                ],
                                'code' => [
                                    'label' => __('Code snippets'),
                                    'description' => __('Issue-specific code, criterion code, and non-diagram example code.'),
                                ],
                                'diagrams' => [
                                    'label' => __('Diagrams and source'),
                                    'description' => __('Saved Mermaid diagrams and their rendered images.'),
                                ],
                                'examples' => [
                                    'label' => __('Examples and references'),
                                    'description' => __('Linked knowledge-base examples, descriptions, links, and references.'),
                                ],
                            ],
                        ],
                        [
                            'title' => __('Methodology and review indexes'),
                            'description' => __('Add project-level testing context and navigational summaries.'),
                            'options' => [
                                'methodologies' => [
                                    'label' => __('Testing methodologies'),
                                    'description' => __('Testing methods linked to the project, including project notes.'),
                                ],
                                'clean_pages' => [
                                    'label' => __('Tested pages without issues'),
                                    'description' => __('Included pages and services with no reportable findings.'),
                                ],
                                'wcag_index' => [
                                    'label' => __('WCAG index'),
                                    'description' => __('An index of success criteria linked to report findings.'),
                                ],
                            ],
                        ],
                    ];
                @endphp

                <flux:accordion transition>
                    <flux:accordion.item>
                        <flux:accordion.heading class="min-h-12 py-3">
                            {{ __('Advanced report content') }}
                        </flux:accordion.heading>
                        <flux:accordion.content>
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                {{ __('These selections become the project defaults for future report versions.') }}
                            </flux:text>
                            <div class="mt-5 space-y-6">
                                @foreach ($optionGroups as $group)
                                    <fieldset class="space-y-3">
                                        <legend class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $group['title'] }}</legend>
                                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $group['description'] }}</flux:text>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            @foreach ($group['options'] as $key => $option)
                                                <flux:field>
                                                    <input type="hidden" name="options[{{ $key }}]" value="0">
                                                    <flux:checkbox
                                                        name="options[{{ $key }}]"
                                                        value="1"
                                                        :checked="(bool) old('options.'.$key, $reportOptions[$key])"
                                                        :label="$option['label']"
                                                    />
                                                    <flux:description>{{ $option['description'] }}</flux:description>
                                                    @unless ($reportPreflight['available_options'][$key])
                                                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('No matching content is currently recorded.') }}</flux:text>
                                                    @endunless
                                                </flux:field>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endforeach
                            </div>
                            <flux:error name="options"/>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>

                <div class="flex justify-end gap-3">
                    <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Generate Report') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
