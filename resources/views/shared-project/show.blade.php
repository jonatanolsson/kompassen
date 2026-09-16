<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $project->name }} - {{ __('Accessibility Report') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body
        class="bg-white text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100 print:text-black"
        x-data="{
            selectedImage: '',
            showModal: false,
            selectedIssue: null,
            selectedCriterion: null,
            modalTrigger: null,
            openModal(property, value, trigger) {
                this.modalTrigger = trigger ? (trigger.currentTarget || trigger) : null;
                this[property] = value;
                this.$nextTick(() => this.$refs[property + 'Modal']?.focus());
            },
            closeModal(property) {
                this[property] = property === 'selectedImage' ? '' : null;
                this.showModal = false;
                this.$nextTick(() => {
                    if (this.modalTrigger && typeof this.modalTrigger.focus === 'function') {
                        this.modalTrigger.focus();
                    }
                });
            },
            trapFocus(event) {
                if (event.key !== 'Tab') {
                    return;
                }

                const focusable = [...event.currentTarget.querySelectorAll('a[href], button:not([disabled]), [tabindex]')]
                    .filter(element => element.tabIndex >= 0);
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (!first || !last) {
                    return;
                }

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        }"
    >
        <!-- Header -->
        <header class="border-b border-zinc-200 dark:border-zinc-700 print:border-zinc-300 print:page-break-after-avoid">
            <div class="max-w-5xl mx-auto px-6 py-8 print:py-6">
                <div class="flex items-start gap-6 print:gap-4">
                    @if ($project->client_logo)
                        <img 
                            src="{{ asset('storage/' . $project->client_logo) }}" 
                            alt="{{ $project->name }}" 
                            class="h-16 w-auto object-contain print:h-12"
                        />
                    @endif
                    <div class="flex-1 min-w-0">
                        <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 print:text-zinc-700">
                            {{ __('Accessibility Report') }}
                        </flux:text>
                        <flux:heading level="1" class="print:mb-2">{{ $project->name }}</flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 print:mt-1">
                            WCAG {{ $project->target_wcag_version }} nivå {{ $project->target_wcag_level }} • {{ __(Str::title(str_replace(['_', '-'], ' ', $project->status))) }}
                        </flux:text>
                        <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-2 text-sm text-zinc-600 dark:text-zinc-400 sm:grid-cols-3 print:grid-cols-3 print:mt-3 print:text-zinc-700">
                            <div>
                                <dt class="font-medium text-zinc-900 dark:text-zinc-200 print:text-black">{{ __('Audit Date') }}</dt>
                                <dd>{{ $project->audit_date?->format('Y-m-d') ?? __('Not specified') }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-zinc-900 dark:text-zinc-200 print:text-black">{{ __('Target WCAG Level') }}</dt>
                                <dd>WCAG {{ $project->target_wcag_version }} {{ $project->target_wcag_level }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-zinc-900 dark:text-zinc-200 print:text-black">{{ __('Last updated') }}</dt>
                                <dd>{{ $project->updated_at->format('Y-m-d H:i') }}</dd>
                            </div>
                        </dl>
                        @if ($project->description)
                            <x-user-content :content="$project->description" class="mt-4 print:mt-3 text-base print:text-sm" />
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-5xl mx-auto px-6 py-10 print:py-8">
            <!-- Executive Summary -->
            <section id="executive-summary" class="mb-10 rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/60 print:border-zinc-300 print:bg-white print:page-break-inside-avoid">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="max-w-3xl">
                        <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 print:text-zinc-700">
                            {{ __('Executive summary') }}
                        </flux:text>
                        <flux:heading level="2" class="mt-1 text-2xl print:text-xl">{{ $stats['risk_level'] }}</flux:heading>
                        <flux:text class="mt-3 text-base leading-7 text-zinc-700 dark:text-zinc-300 print:text-zinc-700">
                            {{ $stats['risk_summary'] }}
                        </flux:text>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 text-sm dark:border-zinc-700 dark:bg-zinc-900 print:border-zinc-300">
                        <div class="font-semibold text-zinc-900 dark:text-white print:text-black">{{ __('Recommended next step') }}</div>
                        <div class="mt-1 text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ $stats['recommended_next_step'] }}</div>
                    </div>
                </div>
            </section>

            <!-- Summary Statistics -->
            <section id="summary" class="mb-12 print:mb-8 print:page-break-inside-avoid">
                <flux:heading level="2" class="mb-6 print:mb-4 print:border-b print:border-zinc-300 print:pb-2">{{ __('Summary') }}</flux:heading>
                
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 print:grid-cols-3 print:gap-3">
                    <div class="bg-zinc-50 dark:bg-zinc-800 print:bg-white print:border print:border-zinc-300 p-4 print:p-3 rounded-lg print:rounded">
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white print:text-2xl print:text-black">{{ $stats['total_issues'] }}</div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 mt-1">{{ __('Total Issues') }}</div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 print:bg-white print:border print:border-red-300 p-4 print:p-3 rounded-lg print:rounded">
                        <div class="text-3xl font-bold text-red-600 dark:text-red-400 print:text-red-700 print:text-2xl">{{ $stats['severity_counts']['critical'] ?? 0 }}</div>
                        <div class="text-sm text-red-700 dark:text-red-300 print:text-red-800 mt-1">{{ __('Critical') }}</div>
                    </div>

                    <div class="bg-amber-50 dark:bg-amber-900/20 print:bg-white print:border print:border-amber-300 p-4 print:p-3 rounded-lg print:rounded">
                        <div class="text-3xl font-bold text-amber-600 dark:text-amber-400 print:text-amber-700 print:text-2xl">{{ $stats['severity_counts']['major'] ?? 0 }}</div>
                        <div class="text-sm text-amber-700 dark:text-amber-300 print:text-amber-800 mt-1">{{ __('Major') }}</div>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 print:bg-white print:border print:border-yellow-300 p-4 print:p-3 rounded-lg print:rounded">
                        <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 print:text-yellow-700 print:text-2xl">{{ $stats['severity_counts']['moderate'] ?? 0 }}</div>
                        <div class="text-sm text-yellow-700 dark:text-yellow-300 print:text-yellow-800 mt-1">{{ __('Moderate') }}</div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 print:bg-white print:border print:border-green-300 p-4 print:p-3 rounded-lg print:rounded">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400 print:text-green-700 print:text-2xl">{{ $stats['severity_counts']['minor'] ?? 0 }}</div>
                        <div class="text-sm text-green-700 dark:text-green-300 print:text-green-800 mt-1">{{ __('Minor') }}</div>
                    </div>

                    <div class="bg-zinc-50 dark:bg-zinc-800 print:bg-white print:border print:border-zinc-300 p-4 print:p-3 rounded-lg print:rounded">
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white print:text-2xl print:text-black">{{ $stats['total_pages'] }}</div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 mt-1">{{ __('Pages & Services Tested') }}</div>
                    </div>
                </div>

                @if ($stats['total_out_of_scope_pages'] > 0)
                    <flux:text class="mt-3 text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700">
                        {{ __(':count pages or services are outside the audit scope.', ['count' => $stats['total_out_of_scope_pages']]) }}
                    </flux:text>
                @endif
            </section>

            <nav aria-label="{{ __('Report sections') }}" class="mb-12 print:hidden">
                <flux:heading level="2" class="mb-4">{{ __('Report sections') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    <a href="#executive-summary" class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:border-blue-300 hover:text-blue-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-blue-600 dark:hover:text-blue-300">{{ __('Executive summary') }}</a>
                    <a href="#summary" class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:border-blue-300 hover:text-blue-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-blue-600 dark:hover:text-blue-300">{{ __('Summary') }}</a>
                    @if ($stats['priority_issues']->count() > 0)
                        <a href="#priority-actions" class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:border-blue-300 hover:text-blue-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-blue-600 dark:hover:text-blue-300">{{ __('Prioritized actions') }}</a>
                    @endif
                    @if ($inScopePages->count() > 0)
                        <a href="#pages-services" class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:border-blue-300 hover:text-blue-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-blue-600 dark:hover:text-blue-300">{{ __('Pages & Services') }}</a>
                    @endif
                    @if ($outOfScopePages->count() > 0)
                        <a href="#out-of-scope" class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:border-blue-300 hover:text-blue-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-blue-600 dark:hover:text-blue-300">{{ __('Out of Scope') }}</a>
                    @endif
                    @if ($projectWideIssues->count() > 0)
                        <a href="#project-wide-issues" class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:border-blue-300 hover:text-blue-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-blue-600 dark:hover:text-blue-300">{{ __('Project-Wide Issues') }}</a>
                    @endif
                    @if ($stats['wcag_mapping']->count() > 0)
                        <a href="#accessibility-standards" class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:border-blue-300 hover:text-blue-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-blue-600 dark:hover:text-blue-300">{{ __('Affected WCAG requirements') }}</a>
                    @endif
                </div>
            </nav>

            <!-- Priority Actions -->
            @if ($stats['priority_issues']->count() > 0)
                <section id="priority-actions" class="mb-12 print:mb-8 print:page-break-inside-avoid">
                    <div class="mb-4">
                        <flux:heading level="2">{{ __('Prioritized actions') }}</flux:heading>
                        <flux:text class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700">
                            {{ __('Start with these issues to reduce the most accessibility risk.') }}
                        </flux:text>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3 print:grid-cols-3">
                        @foreach ($stats['priority_issues'] as $issue)
                            @php
                                $severityLabel = __(Str::title(str_replace(['_', '-'], ' ', $issue->severity)));
                                $issueData = $issue->only('id', 'title', 'description', 'severity', 'difficulty', 'status', 'component_area', 'solution_suggestions');
                                $issueData['description'] = \App\Helpers\MarkdownHelper::toHtml($issue->description);
                                $issueData['severity_label'] = $severityLabel;
                                $issueData['difficulty_label'] = $issue->difficulty ? __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) : null;
                                $issueData['status_label'] = $issue->status ? __($issue->status) : null;
                                $issueData['page_name'] = $issue->page?->name ?? __('Project-wide issue');
                                $issueData['attachments'] = $issue->attachments->map(fn($a) => ['path' => asset('storage/' . $a->path), 'filename' => $a->original_filename])->all();
                                $issueData['wcag'] = $issue->wcagCriteria->map(fn($c) => ['number' => $c->number, 'name' => $c->name_sv ?? $c->name_en, 'level' => $c->level, 'description' => $c->description_sv ?? $c->description_en, 'url' => $c->url])->all();
                            @endphp
                            <button
                                type="button"
                                data-issue="{{ json_encode($issueData) }}"
                                @click="openModal('selectedIssue', JSON.parse($el.dataset.issue), $event)"
                                class="rounded-xl border border-zinc-200 bg-white p-4 text-left shadow-sm transition hover:border-blue-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-blue-600 print:border-zinc-300 print:shadow-none"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-semibold text-zinc-900 dark:text-white print:text-black">{{ $issue->title }}</div>
                                    <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))" class="shrink-0">
                                        {{ $severityLabel }}
                                    </flux:badge>
                                </div>
                                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700">
                                    {{ $issue->page?->name ?? __('Project-wide issue') }}
                                </div>
                                <div class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                                    {{ __('View details') }} <span aria-hidden="true">→</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Pages & Issues -->
            @if ($inScopePages->count() > 0)
                <section id="pages-services" class="mb-12 print:mb-8">
                    <flux:heading level="2" class="mb-6 print:mb-4 print:border-b print:border-zinc-300 print:pb-2 print:page-break-after-avoid">{{ __('Pages & Services') }}</flux:heading>
                    
                    <div class="space-y-6 print:space-y-4">
                        @foreach ($inScopePages as $page)
                            @php
                                $resourceType = $page->resource_type ?? 'page';
                                $accessContext = $page->access_context ?? 'not_applicable';
                            @endphp
                            <flux:card class="border border-zinc-200 dark:border-zinc-700 print:border-zinc-300 rounded-lg overflow-hidden print:rounded print:page-break-inside-avoid">
                                <!-- Page Header -->
                                <div class="bg-zinc-50 dark:bg-zinc-800 print:bg-zinc-100 p-4 print:p-3">
                                    <div class="flex items-start justify-between gap-4 print:gap-2">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white print:text-black print:text-base truncate">{{ $page->name }}</h3>
                                            <div class="mt-1 flex flex-wrap gap-2">
                                                <span class="inline-block px-2 py-1 text-xs font-medium rounded bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200 print:bg-white print:border print:border-zinc-300">
                                                    {{ __($resourceType === 'service' ? 'Service' : 'Page') }}
                                                </span>
                                                @if ($accessContext !== 'not_applicable')
                                                    <span class="inline-block px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 print:bg-purple-50 print:border print:border-purple-300">
                                                        {{ __($accessContext === 'authenticated' ? 'Authenticated' : ($accessContext === 'mixed' ? 'Public and authenticated' : 'Public')) }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($page->url)
                                                <a href="{{ $page->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 dark:text-blue-400 print:text-blue-700 hover:underline print:underline mt-1 break-all">
                                                    {{ $page->url }}
                                                </a>
                                            @endif
                                        </div>
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded print:text-xs print:px-2 print:py-1 {{ $page->scope === 'in_scope' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 print:bg-green-50 print:border print:border-green-300 print:text-green-800' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 print:bg-amber-50 print:border print:border-amber-300 print:text-amber-800' }}">
                                            {{ __(Str::title(str_replace(['_', '-'], ' ', $page->scope))) }}
                                        </span>
                                    </div>
                                    @if ($page->description)
                                        <x-user-content :content="$page->description" class="mt-2 print:mt-1 text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700" />
                                    @endif
                                </div>

                                <!-- Page Issues -->
                                @if ($page->issues->count() > 0)
                                    <div class="pt-4 print:pt-3 border-t border-zinc-200 dark:border-zinc-700 print:border-zinc-300">
                                        <flux:heading level="4" class="text-sm mb-3 print:mb-2">{{ __('Issues') }} ({{ $page->issues->count() }})</flux:heading>
                                        
                                        <div class="space-y-3 print:space-y-2">
                                            @foreach ($page->issues as $issue)
                                                @php
                                                    $severityLabel = __(Str::title(str_replace(['_', '-'], ' ', $issue->severity)));
                                                    $difficultyLabel = $issue->difficulty ? __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) : null;
                                                    $statusLabel = $issue->status ? __($issue->status) : null;
                                                    $issueData = $issue->only('id', 'title', 'description', 'severity', 'difficulty', 'status', 'component_area', 'solution_suggestions');
                                                    $issueData['description'] = \App\Helpers\MarkdownHelper::toHtml($issue->description);
                                                    $issueData['severity_label'] = $severityLabel;
                                                    $issueData['difficulty_label'] = $difficultyLabel;
                                                    $issueData['status_label'] = $statusLabel;
                                                    $issueData['page_name'] = $page->name;
                                                    $issueData['attachments'] = $issue->attachments->map(fn($a) => ['path' => asset('storage/' . $a->path), 'filename' => $a->original_filename])->all();
                                                    $issueData['wcag'] = $issue->wcagCriteria->map(fn($c) => ['number' => $c->number, 'name' => $c->name_sv ?? $c->name_en, 'level' => $c->level, 'description' => $c->description_sv ?? $c->description_en, 'url' => $c->url])->all();
                                                @endphp
                                                <button 
                                                    type="button"
                                                    data-issue="{{ json_encode($issueData) }}"
                                                    @click="openModal('selectedIssue', JSON.parse($el.dataset.issue), $event)"
                                                    aria-label="{{ __('View issue details: :title', ['title' => $issue->title]) }}"
                                                    class="w-full text-left bg-white dark:bg-zinc-800 print:bg-white border border-zinc-200 dark:border-zinc-700 print:border-zinc-300 rounded-lg p-3 print:p-2.5 hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-600 print:hover:shadow-none transition print:hover:border-zinc-300"
                                                >
                                                    <!-- Issue Header -->
                                                    <div class="flex items-start justify-between gap-3 mb-2 print:mb-1.5">
                                                        <div class="flex-1 min-w-0">
                                                            <flux:heading level="5" class="text-sm font-semibold mb-1">{{ $issue->title }}</flux:heading>
                                                        </div>
                                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))" class="text-xs whitespace-nowrap print:text-xs">
                                                            {{ $severityLabel }}
                                                        </flux:badge>
                                                    </div>

                                                    <!-- Issue Meta Row -->
                                                    <div class="flex flex-wrap gap-2 mb-2 print:mb-1.5 print:gap-1.5">
                                                        @if ($issue->component_area)
                                                            <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                                                {{ $issue->component_area }}
                                                            </flux:badge>
                                                        @endif
                                                        @if ($issue->difficulty)
                                                            <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                                                {{ $difficultyLabel }}
                                                            </flux:badge>
                                                        @endif
                                                        @if ($issue->status)
                                                            <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                                                {{ $statusLabel }}
                                                            </flux:badge>
                                                        @endif
                                                    </div>

                                                    <!-- Issue Description -->
                                                    @if ($issue->description)
                                                        <div class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 print:text-xs mb-2 print:mb-1.5 line-clamp-2">
                                                            {{ Str::limit(strip_tags($issue->description), 150) }}
                                                        </div>
                                                    @endif

                                                    <!-- WCAG Criteria -->
                                                    @if ($issue->wcagCriteria->count() > 0)
                                                        <div class="flex flex-wrap gap-1 print:gap-0.5">
                                                            @foreach ($issue->wcagCriteria as $criterion)
                                                                <flux:badge color="blue" class="text-xs print:text-xs print:px-1 print:py-0.5" variant="subtle">
                                                                    {{ $criterion->number }}
                                                                </flux:badge>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <div class="mt-3 flex items-center justify-end text-sm font-medium text-blue-700 dark:text-blue-300 print:hidden">
                                                        {{ __('View details') }} <span class="ml-1" aria-hidden="true">→</span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <flux:text class="text-sm">{{ __('No issues on this page') }}</flux:text>
                                @endif

                                <!-- Screenshots -->
                                @if ($page->issues->sum(fn($i) => $i->attachments->count()) > 0)
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 print:border-zinc-300 -m-4 mt-4 print:mt-4 p-4 print:p-3 bg-zinc-50 dark:bg-zinc-800 print:bg-zinc-50">
                                        <flux:heading level="4" class="text-xs mb-3 print:mb-2 uppercase">{{ __('Screenshots by issue') }}</flux:heading>
                                        <div class="space-y-4">
                                            @foreach ($page->issues->filter(fn($issue) => $issue->attachments->count() > 0) as $issueWithAttachments)
                                                <div>
                                                    <div class="mb-2 text-sm font-medium text-zinc-900 dark:text-white print:text-black">
                                                        {{ __('Screenshots for :issue', ['issue' => $issueWithAttachments->title]) }}
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2 print:grid-cols-3 print:gap-1.5">
                                                        @foreach ($issueWithAttachments->attachments->take(4) as $attachment)
                                                            <button
                                                                type="button"
                                                                @click="openModal('selectedImage', '{{ asset('storage/' . $attachment->path) }}', $event); showModal = true"
                                                                aria-label="{{ __('View image: :filename', ['filename' => $attachment->original_filename]) }}"
                                                                class="print:hidden overflow-hidden rounded border border-zinc-300 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            >
                                                                <img
                                                                    src="{{ asset('storage/' . $attachment->path) }}"
                                                                    alt="{{ __('Screenshot for :issue', ['issue' => $issueWithAttachments->title]) }}"
                                                                    class="h-24 w-full object-cover"
                                                                />
                                                            </button>
                                                            <img
                                                                src="{{ asset('storage/' . $attachment->path) }}"
                                                                alt="{{ __('Screenshot for :issue', ['issue' => $issueWithAttachments->title]) }}"
                                                                class="hidden w-full rounded border border-zinc-300 print:block"
                                                            />
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </flux:card>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Out-of-Scope Resources -->
            @if ($outOfScopePages->count() > 0)
                <section id="out-of-scope" class="mb-12 print:mb-8">
                    <flux:heading level="2" class="mb-6 print:mb-4 print:border-b print:border-zinc-300 print:pb-2 print:page-break-after-avoid">{{ __('Out of Scope') }}</flux:heading>
                    <flux:text class="mb-4 text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700">
                        {{ __('These pages and services were documented but excluded from this audit.') }}
                    </flux:text>
                    <div class="space-y-3 print:space-y-2">
                        @foreach ($outOfScopePages as $page)
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20 print:border-amber-300 print:bg-white">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="font-semibold text-zinc-900 dark:text-white print:text-black">{{ $page->name }}</h3>
                                    <span class="text-xs font-medium text-amber-800 dark:text-amber-200 print:text-amber-800">{{ __('Out of Scope') }}</span>
                                </div>
                                @if ($page->url)
                                    <a href="{{ $page->url }}" target="_blank" rel="noopener noreferrer" class="mt-1 block break-all text-sm text-blue-700 underline dark:text-blue-300 print:text-blue-800">
                                        {{ $page->url }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Project-Wide Issues -->
            @if ($projectWideIssues->count() > 0)
                <section id="project-wide-issues" class="mb-12 print:mb-8">
                    <flux:heading level="2" class="mb-6 print:mb-4 print:border-b print:border-zinc-300 print:pb-2 print:page-break-after-avoid">{{ __('Project-Wide Issues') }}</flux:heading>
                    
                    <div class="space-y-6 print:space-y-4">
                        @foreach ($projectWideIssues as $issue)
                            @php
                                $severityLabel = __(Str::title(str_replace(['_', '-'], ' ', $issue->severity)));
                                $difficultyLabel = $issue->difficulty ? __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) : null;
                                $statusLabel = $issue->status ? __($issue->status) : null;
                                $issueData = $issue->only('id', 'title', 'description', 'severity', 'difficulty', 'status', 'component_area', 'solution_suggestions');
                                $issueData['description'] = \App\Helpers\MarkdownHelper::toHtml($issue->description);
                                $issueData['severity_label'] = $severityLabel;
                                $issueData['difficulty_label'] = $difficultyLabel;
                                $issueData['status_label'] = $statusLabel;
                                $issueData['page_name'] = $issue->page?->name ?? __('Project-wide issue');
                                $issueData['attachments'] = $issue->attachments->map(fn($a) => ['path' => asset('storage/' . $a->path), 'filename' => $a->original_filename])->all();
                                $issueData['wcag'] = $issue->wcagCriteria->map(fn($c) => ['number' => $c->number, 'name' => $c->name_sv ?? $c->name_en, 'level' => $c->level, 'description' => $c->description_sv ?? $c->description_en, 'url' => $c->url])->all();
                            @endphp
                            <flux:card
                                role="button"
                                tabindex="0"
                                data-issue="{{ json_encode($issueData) }}"
                                @click="openModal('selectedIssue', JSON.parse($el.dataset.issue), $event)"
                                @keydown.enter="openModal('selectedIssue', JSON.parse($el.dataset.issue), $event)"
                                @keydown.space.prevent="openModal('selectedIssue', JSON.parse($el.dataset.issue), $event)"
                                aria-label="{{ __('View issue details: :title', ['title' => $issue->title]) }}"
                                class="w-full text-left print:page-break-inside-avoid print:border print:border-zinc-300 hover:shadow-lg hover:border-zinc-300 dark:hover:border-zinc-600 print:hover:shadow-none transition cursor-pointer"
                            >
                                    <!-- Header Row -->
                                    <div class="flex items-start justify-between gap-4 print:gap-2 mb-3 print:mb-2">
                                        <flux:heading level="3" class="flex-1 min-w-0 print:text-base">{{ $issue->title }}</flux:heading>
                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))" class="whitespace-nowrap print:text-xs">
                                            {{ $severityLabel }}
                                        </flux:badge>
                                    </div>

                                    <!-- Meta Badges -->
                                    <div class="flex flex-wrap gap-2 mb-3 print:mb-2 print:gap-1.5 print:text-xs">
                                        <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                            {{ __('Difficulty') }}: {{ $difficultyLabel }}
                                        </flux:badge>
                                        <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                            {{ __('Status') }}: {{ $statusLabel }}
                                        </flux:badge>
                                        @if ($issue->component_area)
                                            <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                                {{ $issue->component_area }}
                                            </flux:badge>
                                        @endif
                                    </div>

                                    <!-- Description -->
                                    @if ($issue->description)
                                        <div class="max-w-none mb-3 print:mb-2 text-sm">
                                            <x-user-content :content="$issue->description" />
                                        </div>
                                    @endif

                                    <!-- WCAG Criteria -->
                                    @if ($issue->wcagCriteria->count() > 0)
                                        <div class="mb-3 print:mb-2 pt-3 print:pt-2 border-t border-zinc-200 dark:border-zinc-700 print:border-zinc-300">
                                            <flux:heading level="4" class="text-sm mb-2 print:mb-1.5 print:text-xs">{{ __('WCAG Criteria') }}</flux:heading>
                                            <div class="flex flex-wrap gap-1.5 print:gap-1">
                                                @foreach ($issue->wcagCriteria as $criterion)
                                                <div class="bg-blue-50 dark:bg-blue-900/20 print:bg-white print:border print:border-blue-300 px-2 py-1 print:px-1.5 print:py-0.5 rounded text-xs print:text-xs">
                                                    <span class="font-semibold text-blue-700 dark:text-blue-300 print:text-blue-800">{{ $criterion->number }}</span>
                                                    <span class="text-zinc-600 dark:text-zinc-400 print:text-zinc-700 text-xs">{{ $criterion->name_sv ?? $criterion->name_en }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Screenshots -->
                                @if ($issue->attachments->count() > 0)
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 print:border-zinc-300 -m-4 mt-3 print:mt-2.5 p-4 print:p-2.5 bg-zinc-50 dark:bg-zinc-800 print:bg-zinc-50">
                                        <flux:heading level="4" class="text-xs mb-2 print:mb-1.5 uppercase">{{ __('Screenshots') }} ({{ $issue->attachments->count() }})</flux:heading>
                                        <div class="grid grid-cols-3 print:grid-cols-4 gap-2 print:gap-1">
                                            @foreach ($issue->attachments as $attachment)
                                                <button 
                                                    type="button"
                                                    @click="openModal('selectedImage', '{{ asset('storage/' . $attachment->path) }}', $event); showModal = true"
                                                    aria-label="{{ __('View image: :filename', ['filename' => $attachment->original_filename]) }}"
                                                    class="print:hidden rounded border border-zinc-300 overflow-hidden hover:shadow-md transition aspect-square"
                                                >
                                                    <img 
                                                        src="{{ asset('storage/' . $attachment->path) }}"
                                                        alt="{{ $attachment->original_filename }}"
                                                        class="w-full h-full object-cover"
                                                    />
                                                </button>
                                                <img 
                                                    src="{{ asset('storage/' . $attachment->path) }}"
                                                    alt="{{ $attachment->original_filename }}"
                                                    class="hidden print:block rounded border border-zinc-300 w-full h-auto"
                                                />
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </flux:card>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- WCAG Mapping -->
            @if ($stats['wcag_mapping']->count() > 0)
                <section id="accessibility-standards" class="print:page-break-inside-avoid">
                    <div class="mb-6 print:mb-4">
                        <flux:heading level="2" class="print:border-b print:border-zinc-300 print:pb-2 print:page-break-after-avoid">{{ __('Affected WCAG requirements') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 mt-2 print:mt-1">
                            {{ __('These WCAG requirements are affected by the issues above.') }}
                        </flux:text>
                    </div>
                    
                    <div class="space-y-3 print:space-y-2">
                        @foreach ($stats['wcag_mapping'] as $number => $mappings)
                            @php 
                                $criterion = $mappings->first()['criterion'];
                                $criterionData = $criterion->only('id', 'number', 'name_en', 'name_sv', 'level', 'description_en', 'description_sv', 'url');
                                $issuesData = $mappings->map(fn($m) => [
                                    ...$m['issue']->only('id', 'title', 'severity'),
                                    'severity_label' => __(Str::title(str_replace(['_', '-'], ' ', $m['issue']->severity))),
                                ])->all();
                            @endphp
                            <button
                                type="button"
                                data-criterion="{{ json_encode(array_merge($criterionData, ['issues' => $issuesData])) }}"
                                @click="openModal('selectedCriterion', JSON.parse($el.dataset.criterion), $event)"
                                aria-label="{{ __('View criterion details: :criterion', ['criterion' => $criterion->number]) }}"
                                class="w-full text-left border-l-4 border-blue-500 print:border-blue-700 pl-4 print:pl-3 py-2 print:py-1.5 bg-blue-50 dark:bg-blue-900/20 print:bg-white print:border print:border-blue-300 rounded hover:bg-blue-100 dark:hover:bg-blue-900/40 print:hover:bg-white transition print:hover:border-blue-400"
                            >
                                <flux:heading level="3" class="text-sm print:text-sm font-semibold">
                                    <span class="text-blue-700 dark:text-blue-300 print:text-blue-800 font-bold">{{ $criterion->number }}</span>
                                    – {{ $criterion->name_sv ?? $criterion->name_en }}
                                </flux:heading>
                                <flux:text class="text-xs mt-0.5 print:mt-0.5">
                                    {{ __('Level') }} <span class="font-medium">{{ Str::upper($criterion->level) }}</span>
                                    • <span class="font-medium">{{ $mappings->count() }}</span> {{ __('issue(s)') }}
                                </flux:text>
                                @if ($mappings->count() > 0)
                                    <ul class="text-xs mt-1.5 print:mt-1 space-y-0.5 print:space-y-0">
                                        @foreach ($mappings->take(3) as $mapping)
                                            <li><span class="rounded bg-white/80 px-1.5 py-0.5 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ Str::limit($mapping['issue']->title, 60) }}</span></li>
                                        @endforeach
                                        @if ($mappings->count() > 3)
                                            <li class="italic text-zinc-500">{{ __('+ :count more', ['count' => $mappings->count() - 3]) }}</li>
                                        @endif
                                    </ul>
                                @endif
                                <div class="mt-3 text-xs font-medium text-blue-700 dark:text-blue-300 print:hidden">{{ __('View criterion') }} →</div>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Empty State -->
            @if ($project->pages->count() === 0 && $projectWideIssues->count() === 0)
                <div class="text-center py-12 print:py-8">
                    <flux:heading level="3">{{ __('No issues reported yet') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('This project does not have any pages or issues documented yet.') }}</flux:text>
                </div>
            @endif
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-700 print:border-zinc-300 mt-12 print:mt-8 py-6 print:py-4 text-center print:page-break-before-avoid">
            <div class="max-w-5xl mx-auto px-6">
                <flux:text class="text-sm">
                    {{ __('This is a shared accessibility report. Last updated: :date', ['date' => $project->updated_at->format('Y-m-d H:i')]) }}
                </flux:text>
                <flux:button 
                    type="button"
                    @click="window.print()" 
                    class="mt-4 print:hidden"
                    icon="printer"
                >
                    {{ __('Print / PDF') }}
                </flux:button>
            </div>
        </footer>

        <!-- Issue Detail Modal -->
        <div
            x-show="selectedIssue !== null"
            @keydown.escape.stop="closeModal('selectedIssue')"
            @keydown="trapFocus($event)"
            @click.self="closeModal('selectedIssue')"
            role="dialog"
            aria-modal="true"
            aria-labelledby="issue-modal-title"
            tabindex="-1"
            x-ref="selectedIssueModal"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:hidden"
            style="display: none;"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-lg max-w-2xl w-full flex flex-col max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div class="flex justify-between items-start p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex-1 min-w-0">
                        <h2 id="issue-modal-title" class="text-xl font-semibold text-zinc-900 dark:text-white" x-text="selectedIssue?.title || ''"></h2>
                    </div>
                    <button
                        type="button"
                        @click="closeModal('selectedIssue')"
                        aria-label="{{ __('Close') }}"
                        class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 flex-shrink-0"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-auto p-6 space-y-4">
                    <!-- Severity & Status -->
                    <div class="flex gap-2 flex-wrap">
                        <span
                            class="px-3 py-1 rounded-full text-sm font-medium text-white"
                            :class="selectedIssue?.severity === 'critical' ? 'bg-red-500' : selectedIssue?.severity === 'major' ? 'bg-amber-500' : selectedIssue?.severity === 'moderate' ? 'bg-yellow-500' : 'bg-green-500'"
                            x-text="selectedIssue?.severity_label || ''"
                        ></span>
                        <span x-show="selectedIssue?.difficulty" class="px-3 py-1 rounded-full text-sm font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            <span class="font-semibold">{{ __('Difficulty') }}:</span>
                            <span x-text="selectedIssue?.difficulty_label || ''"></span>
                        </span>
                        <span x-show="selectedIssue?.status" class="px-3 py-1 rounded-full text-sm font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            <span class="font-semibold">{{ __('Status') }}:</span>
                            <span x-text="selectedIssue?.status_label || ''"></span>
                        </span>
                        <span x-show="selectedIssue?.component_area" class="px-3 py-1 rounded-full text-sm font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white" x-text="selectedIssue?.component_area || ''"></span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <h3 class="font-semibold text-sm text-zinc-900 dark:text-white mb-2">{{ __('Problem') }}</h3>
                            <div x-show="selectedIssue?.description" class="user-content max-w-none text-sm text-zinc-700 dark:text-zinc-300">
                                <div x-html="selectedIssue?.description || ''"></div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <h3 class="font-semibold text-sm text-zinc-900 dark:text-white mb-2">{{ __('Impact') }}</h3>
                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('This issue can prevent users from perceiving, understanding, or using the audited page or service as intended.') }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 md:col-span-2">
                            <h3 class="font-semibold text-sm text-zinc-900 dark:text-white mb-2">{{ __('Recommended action') }}</h3>
                            <p x-show="!selectedIssue?.solution_suggestions" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Prioritize remediation based on severity and verify the fix against the linked WCAG requirement.') }}</p>
                            <div x-show="selectedIssue?.solution_suggestions" class="user-content max-w-none text-sm text-zinc-700 dark:text-zinc-300" x-html="selectedIssue?.solution_suggestions || ''"></div>
                        </div>
                    </div>

                    <div x-show="selectedIssue?.page_name" class="rounded-lg bg-zinc-50 p-3 text-sm text-zinc-700 dark:bg-zinc-700/40 dark:text-zinc-300">
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ __('Affected page or service') }}:</span>
                        <span x-text="selectedIssue?.page_name || ''"></span>
                    </div>

                    <!-- WCAG Criteria -->
                    <div x-show="selectedIssue?.wcag && selectedIssue.wcag.length > 0">
                        <h3 class="font-semibold text-sm text-zinc-900 dark:text-white mb-3">{{ __('WCAG Criteria') }}</h3>
                        <div class="space-y-2">
                            <template x-for="criterion in selectedIssue?.wcag || []" :key="criterion.number">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <div class="flex items-start gap-2">
                                            <span class="font-bold text-blue-700 dark:text-blue-300" x-text="criterion.number"></span>
                                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="criterion.name"></span>
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-blue-200 dark:bg-blue-700 text-blue-900 dark:text-blue-100" x-text="criterion.level"></span>
                                        </div>
                                        <a x-show="criterion.url" :href="criterion.url" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs font-medium">
                                            W3C →
                                        </a>
                                    </div>
                                    <p x-show="criterion.description" class="text-xs text-zinc-700 dark:text-zinc-300" x-text="criterion.description"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div x-show="selectedIssue?.attachments && selectedIssue.attachments.length > 0">
                        <h3 class="font-semibold text-sm text-zinc-900 dark:text-white mb-2">{{ __('Screenshots') }}</h3>
                        <div class="space-y-2">
                            <template x-for="attachment in selectedIssue?.attachments || []" :key="attachment.filename">
                                <div class="border border-zinc-200 dark:border-zinc-700 rounded p-2">
                                    <button type="button" class="block w-full" @click="openModal('selectedImage', attachment.path, $event); showModal = true" :aria-label="'{{ __('View image') }}: ' + attachment.filename">
                                        <img :src="attachment.path" :alt="attachment.filename" class="rounded max-w-full h-auto cursor-pointer hover:opacity-75 transition" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WCAG Criterion Detail Modal -->
        <div
            x-show="selectedCriterion !== null"
            @keydown.escape.stop="closeModal('selectedCriterion')"
            @keydown="trapFocus($event)"
            @click.self="closeModal('selectedCriterion')"
            role="dialog"
            aria-modal="true"
            aria-labelledby="criterion-modal-title"
            tabindex="-1"
            x-ref="selectedCriterionModal"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:hidden"
            style="display: none;"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-lg max-w-2xl w-full flex flex-col max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div class="flex justify-between items-start p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex-1 min-w-0">
                        <h2 id="criterion-modal-title" class="text-lg font-semibold text-zinc-900 dark:text-white">
                            <span class="text-blue-600 dark:text-blue-400 font-bold" x-text="selectedCriterion?.number || ''"></span>
                            –
                            <span x-text="selectedCriterion?.name_sv || selectedCriterion?.name_en || ''"></span>
                        </h2>
                    </div>
                    <button
                        type="button"
                        @click="closeModal('selectedCriterion')"
                        aria-label="{{ __('Close') }}"
                        class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 flex-shrink-0"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-auto p-6 space-y-4">
                    <!-- W3C Link -->
                    <div x-show="selectedCriterion?.url" class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('View on W3C') }}</span>
                        <a x-show="selectedCriterion?.url" :href="selectedCriterion?.url" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                            {{ __('Open') }} →
                        </a>
                    </div>

                    <!-- Level -->
                    <div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            <span class="font-semibold">{{ __('Level') }}:</span>
                            <span class="font-mono font-bold text-zinc-900 dark:text-white" x-text="selectedCriterion?.level?.toUpperCase() || ''"></span>
                        </p>
                    </div>

                    <!-- Description (Swedish or English) -->
                    <div x-show="selectedCriterion?.description_sv || selectedCriterion?.description_en">
                        <h3 class="font-semibold text-sm text-zinc-900 dark:text-white mb-2">{{ __('Description') }}</h3>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed" x-text="selectedCriterion?.description_sv || selectedCriterion?.description_en || ''"></p>
                    </div>

                    <!-- Affected Issues -->
                    <div x-show="selectedCriterion?.issues && selectedCriterion.issues.length > 0">
                        <h3 class="font-semibold text-sm text-zinc-900 dark:text-white mb-2">
                            {{ __('Affected issues') }}
                            <span class="text-zinc-600 dark:text-zinc-400 font-normal">(<span x-text="selectedCriterion?.issues?.length || 0"></span>)</span>
                        </h3>
                        <ul class="space-y-2">
                            <template x-for="issue in selectedCriterion?.issues || []" :key="issue.id">
                                <li class="text-sm flex items-center gap-2 p-2 rounded bg-zinc-50 dark:bg-zinc-700/50 border border-zinc-200 dark:border-zinc-600">
                                    <span :class="issue.severity === 'critical' ? 'bg-red-500' : issue.severity === 'major' ? 'bg-amber-500' : issue.severity === 'moderate' ? 'bg-yellow-500' : 'bg-green-500'" class="w-2 h-2 rounded-full flex-shrink-0"></span>
                                    <span class="text-zinc-900 dark:text-zinc-100" x-text="issue.title"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div
            x-show="showModal"
            @keydown.escape.stop="closeModal('selectedImage')"
            @keydown="trapFocus($event)"
            @click.self="closeModal('selectedImage')"
            role="dialog"
            aria-modal="true"
            aria-labelledby="image-modal-title"
            tabindex="-1"
            x-ref="selectedImageModal"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:hidden"
            style="display: none;"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-lg max-w-2xl w-full flex flex-col">
                <div class="flex justify-between items-center p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 id="image-modal-title" class="font-semibold text-zinc-900 dark:text-white">{{ __('Full Size View') }}</h3>
                    <button
                        type="button"
                        @click="closeModal('selectedImage')"
                        aria-label="{{ __('Close') }}"
                        class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-4 flex items-center justify-center max-h-[70vh]">
                    <img
                        :src="selectedImage"
                        alt="{{ __('Full size preview') }}"
                        class="max-w-full max-h-full"
                    />
                </div>
            </div>
        </div>
        @livewireScripts
        @fluxScripts
    </body>
</html>
