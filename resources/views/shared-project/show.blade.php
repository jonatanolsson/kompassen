<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $project->name }} - {{ __('Accessibility Report') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100 print:text-black" x-data="{ selectedImage: '', showModal: false, selectedIssue: null, selectedCriterion: null, showIssueModal: false, showCriterionModal: false }">
        <!-- Header -->
        <header class="border-b border-zinc-200 dark:border-zinc-700 print:border-zinc-300 print:page-break-after-avoid">
            <div class="max-w-4xl mx-auto px-6 py-8 print:py-6">
                <div class="flex items-start gap-6 print:gap-4">
                    @if ($project->client_logo)
                        <img 
                            src="{{ asset('storage/' . $project->client_logo) }}" 
                            alt="{{ $project->name }}" 
                            class="h-16 w-auto object-contain print:h-12"
                        />
                    @endif
                    <div class="flex-1 min-w-0">
                        <flux:heading level="1" class="print:mb-2">{{ $project->name }}</flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 print:mt-1">
                            WCAG {{ $project->target_wcag_level }} • {{ __(Str::title(str_replace(['_', '-'], ' ', $project->status))) }}
                        </flux:text>
                        @if ($project->description)
                            <div class="text-base mt-3 print:mt-2 print:text-sm prose prose-sm dark:prose-invert max-w-none">
                                {!! \App\Helpers\MarkdownHelper::toHtml($project->description) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-6 py-12 print:py-8">
            <!-- Summary Statistics -->
            <section class="mb-12 print:mb-8 print:page-break-inside-avoid">
                <flux:heading level="2" class="mb-6 print:mb-4 print:border-b print:border-zinc-300 print:pb-2">{{ __('Summary') }}</flux:heading>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 print:grid-cols-4 print:gap-3">
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

                    <div class="bg-zinc-50 dark:bg-zinc-800 print:bg-white print:border print:border-zinc-300 p-4 print:p-3 rounded-lg print:rounded">
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white print:text-2xl print:text-black">{{ $stats['total_pages'] }}</div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 mt-1">{{ __('Pages Tested') }}</div>
                    </div>
                </div>
            </section>

            <!-- Pages & Issues -->
            @if ($project->pages->count() > 0)
                <section class="mb-12 print:mb-8">
                    <flux:heading level="2" class="mb-6 print:mb-4 print:border-b print:border-zinc-300 print:pb-2 print:page-break-after-avoid">{{ __('Pages & Services') }}</flux:heading>
                    
                    <div class="space-y-6 print:space-y-4">
                        @foreach ($project->pages as $page)
                            <flux:card class="border border-zinc-200 dark:border-zinc-700 print:border-zinc-300 rounded-lg overflow-hidden print:rounded print:page-break-inside-avoid">
                                <!-- Page Header -->
                                <div class="bg-zinc-50 dark:bg-zinc-800 print:bg-zinc-100 p-4 print:p-3">
                                    <div class="flex items-start justify-between gap-4 print:gap-2">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white print:text-black print:text-base truncate">{{ $page->name }}</h3>
                                            @if ($page->url)
                                                <a href="{{ $page->url }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 print:text-blue-700 hover:underline print:underline mt-1 break-all">
                                                    {{ $page->url }}
                                                </a>
                                            @endif
                                        </div>
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded print:text-xs print:px-2 print:py-1 {{ $page->scope === 'in_scope' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 print:bg-green-50 print:border print:border-green-300 print:text-green-800' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 print:bg-amber-50 print:border print:border-amber-300 print:text-amber-800' }}">
                                            {{ __(Str::title(str_replace(['_', '-'], ' ', $page->scope))) }}
                                        </span>
                                    </div>
                                    @if ($page->description)
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 mt-2 print:mt-1 prose prose-sm dark:prose-invert max-w-none">
                                            {!! \App\Helpers\MarkdownHelper::toHtml($page->description) !!}
                                        </div>
                                    @endif
                                </div>

                                <!-- Page Issues -->
                                @if ($page->issues->count() > 0)
                                    <div class="pt-4 print:pt-3 border-t border-zinc-200 dark:border-zinc-700 print:border-zinc-300">
                                        <flux:heading level="4" class="text-sm mb-3 print:mb-2">{{ __('Issues') }} ({{ $page->issues->count() }})</flux:heading>
                                        
                                        <div class="space-y-3 print:space-y-2">
                                            @foreach ($page->issues as $issue)
                                                @php
                                                    $issueData = $issue->only('id', 'title', 'description', 'severity', 'difficulty', 'status', 'component_area');
                                                    $issueData['description'] = \App\Helpers\MarkdownHelper::toHtml($issue->description);
                                                    $issueData['attachments'] = $issue->attachments->map(fn($a) => ['path' => asset('storage/' . $a->path), 'filename' => $a->original_filename])->all();
                                                    $issueData['wcag'] = $issue->wcagCriteria->map(fn($c) => ['number' => $c->number, 'name' => $c->name_sv ?? $c->name_en, 'level' => $c->level, 'description' => $c->description_sv ?? $c->description_en, 'url' => $c->url])->all();
                                                @endphp
                                                <button 
                                                    type="button"
                                                    @click="selectedIssue = @json($issueData); showIssueModal = true"
                                                    class="w-full text-left bg-white dark:bg-zinc-800 print:bg-white border border-zinc-200 dark:border-zinc-700 print:border-zinc-300 rounded-lg p-3 print:p-2.5 hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-600 print:hover:shadow-none transition print:hover:border-zinc-300"
                                                >
                                                    <!-- Issue Header -->
                                                    <div class="flex items-start justify-between gap-3 mb-2 print:mb-1.5">
                                                        <div class="flex-1 min-w-0">
                                                            <flux:heading level="5" class="text-sm font-semibold mb-1">{{ $issue->title }}</flux:heading>
                                                        </div>
                                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))" class="text-xs whitespace-nowrap print:text-xs">
                                                            {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->severity))) }}
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
                                                                {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) }}
                                                            </flux:badge>
                                                        @endif
                                                        @if ($issue->status)
                                                            <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                                                {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->status))) }}
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
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <flux:text class="text-sm">{{ __('No issues on this page') }}</flux:text>
                                @endif

                                <!-- Screenshots -->
                                @if ($project->pages->first()?->issues->sum(fn($i) => $i->attachments->count()) > 0 || $page->issues->sum(fn($i) => $i->attachments->count()) > 0)
                                    @php $attachments = $page->issues->flatMap(fn($i) => $i->attachments)->take(4); @endphp
                                    @if ($attachments->count() > 0)
                                        <div class="border-t border-zinc-200 dark:border-zinc-700 print:border-zinc-300 -m-4 mt-4 print:mt-4 p-4 print:p-3 bg-zinc-50 dark:bg-zinc-800 print:bg-zinc-50">
                                            <flux:heading level="4" class="text-xs mb-3 print:mb-2 uppercase">{{ __('Screenshots') }}</flux:heading>
                                            <div class="grid grid-cols-2 print:grid-cols-3 gap-2 print:gap-1.5">
                                                @foreach ($attachments as $attachment)
                                                    <button 
                                                        type="button"
                                                        @click="selectedImage = '{{ asset('storage/' . $attachment->path) }}'; showModal = true"
                                                        class="print:hidden rounded border border-zinc-300 overflow-hidden hover:shadow-md transition"
                                                    >
                                                        <img 
                                                            src="{{ asset('storage/' . $attachment->path) }}"
                                                            alt="{{ $attachment->original_filename }}"
                                                            class="w-full h-24 object-cover"
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
                                @endif
                            </flux:card>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Project-Wide Issues -->
            @php $projectWideIssues = $project->issues()->whereNull('page_id')->get(); @endphp
            @if ($projectWideIssues->count() > 0)
                <section class="mb-12 print:mb-8">
                    <flux:heading level="2" class="mb-6 print:mb-4 print:border-b print:border-zinc-300 print:pb-2 print:page-break-after-avoid">{{ __('Project-Wide Issues') }}</flux:heading>
                    
                    <div class="space-y-6 print:space-y-4">
                        @foreach ($projectWideIssues as $issue)
                            @php
                                $issueData = $issue->only('id', 'title', 'description', 'severity', 'difficulty', 'status', 'component_area');
                                $issueData['description'] = \App\Helpers\MarkdownHelper::toHtml($issue->description);
                                $issueData['attachments'] = $issue->attachments->map(fn($a) => ['path' => asset('storage/' . $a->path), 'filename' => $a->original_filename])->all();
                                $issueData['wcag'] = $issue->wcagCriteria->map(fn($c) => ['number' => $c->number, 'name' => $c->name_sv ?? $c->name_en, 'level' => $c->level, 'description' => $c->description_sv ?? $c->description_en, 'url' => $c->url])->all();
                            @endphp
                            <button
                                type="button"
                                @click="selectedIssue = @json($issueData); showIssueModal = true"
                                class="w-full text-left"
                            >
                                <flux:card class="print:page-break-inside-avoid print:border print:border-zinc-300 hover:shadow-lg hover:border-zinc-300 dark:hover:border-zinc-600 print:hover:shadow-none transition cursor-pointer">
                                    <!-- Header Row -->
                                    <div class="flex items-start justify-between gap-4 print:gap-2 mb-3 print:mb-2">
                                        <flux:heading level="3" class="flex-1 min-w-0 print:text-base">{{ $issue->title }}</flux:heading>
                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))" class="whitespace-nowrap print:text-xs">
                                            {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->severity))) }}
                                        </flux:badge>
                                    </div>

                                    <!-- Meta Badges -->
                                    <div class="flex flex-wrap gap-2 mb-3 print:mb-2 print:gap-1.5 print:text-xs">
                                        <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                            {{ __('Difficulty') }}: {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) }}
                                        </flux:badge>
                                        <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                            {{ __('Status') }}: {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->status))) }}
                                        </flux:badge>
                                        @if ($issue->component_area)
                                            <flux:badge color="zinc" variant="outline" class="text-xs print:text-xs print:px-1.5 print:py-0.5">
                                                {{ $issue->component_area }}
                                            </flux:badge>
                                        @endif
                                    </div>

                                    <!-- Description -->
                                    @if ($issue->description)
                                        <div class="prose prose-sm dark:prose-invert print:prose-sm max-w-none mb-3 print:mb-2 text-sm">
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
                                                    @click="selectedImage = '{{ asset('storage/' . $attachment->path) }}'; showModal = true"
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
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- WCAG Mapping -->
            @if ($stats['wcag_mapping']->count() > 0)
                <section class="print:page-break-inside-avoid">
                    <div class="mb-6 print:mb-4">
                        <flux:heading level="2" class="print:border-b print:border-zinc-300 print:pb-2 print:page-break-after-avoid">{{ __('Accessibility Standards') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700 mt-2 print:mt-1">
                            {{ __('Which accessibility standards are affected') }}
                        </flux:text>
                    </div>
                    
                    <div class="space-y-3 print:space-y-2">
                        @foreach ($stats['wcag_mapping'] as $number => $mappings)
                            @php 
                                $criterion = $mappings->first()['criterion'];
                                $criterionData = $criterion->only('id', 'number', 'name_en', 'name_sv', 'level', 'description_en', 'description_sv', 'url');
                                $issuesData = $mappings->map(fn($m) => $m['issue']->only('id', 'title', 'severity'))->all();
                            @endphp
                            <button
                                type="button"
                                @click="selectedCriterion = @json(array_merge($criterionData, ['issues' => $issuesData])); showCriterionModal = true"
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
                                            <li>• {{ Str::limit($mapping['issue']->title, 60) }}</li>
                                        @endforeach
                                        @if ($mappings->count() > 3)
                                            <li class="italic text-zinc-500">{{ __('+ :count more', ['count' => $mappings->count() - 3]) }}</li>
                                        @endif
                                    </ul>
                                @endif
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
            <div class="max-w-4xl mx-auto px-6">
                <flux:text class="text-sm">
                    {{ __('This is a shared accessibility report. Last updated:') }} <strong>{{ $project->updated_at->format('Y-m-d H:i') }}</strong>
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
            @keydown.escape.window="selectedIssue = null; showIssueModal = false"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:hidden"
            style="display: none;"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-lg max-w-2xl w-full flex flex-col max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div class="flex justify-between items-start p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white" x-text="selectedIssue?.title || ''"></h2>
                    </div>
                    <button
                        type="button"
                        @click="selectedIssue = null"
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
                            x-text="selectedIssue?.severity ? selectedIssue.severity.charAt(0).toUpperCase() + selectedIssue.severity.slice(1) : ''"
                        ></span>
                        <span x-show="selectedIssue?.difficulty" class="px-3 py-1 rounded-full text-sm font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            <span class="font-semibold">{{ __('Difficulty') }}:</span>
                            <span x-text="selectedIssue?.difficulty ? selectedIssue.difficulty.charAt(0).toUpperCase() + selectedIssue.difficulty.slice(1) : ''"></span>
                        </span>
                        <span x-show="selectedIssue?.status" class="px-3 py-1 rounded-full text-sm font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white">
                            <span class="font-semibold">{{ __('Status') }}:</span>
                            <span x-text="selectedIssue?.status ? selectedIssue.status.charAt(0).toUpperCase() + selectedIssue.status.slice(1) : ''"></span>
                        </span>
                        <span x-show="selectedIssue?.component_area" class="px-3 py-1 rounded-full text-sm font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-white" x-text="selectedIssue?.component_area || ''"></span>
                    </div>

                    <!-- Description -->
                    <div x-show="selectedIssue?.description" class="prose prose-sm dark:prose-invert max-w-none">
                        <div class="text-sm text-zinc-700 dark:text-zinc-300" x-html="selectedIssue?.description || ''"></div>
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
                                    <img :src="attachment.path" :alt="attachment.filename" class="rounded max-w-full h-auto cursor-pointer hover:opacity-75 transition" @click="selectedImage = attachment.path; showModal = true" />
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
            @keydown.escape.window="selectedCriterion = null; showCriterionModal = false"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:hidden"
            style="display: none;"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-lg max-w-2xl w-full flex flex-col max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div class="flex justify-between items-start p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            <span class="text-blue-600 dark:text-blue-400 font-bold" x-text="selectedCriterion?.number || ''"></span>
                            –
                            <span x-text="selectedCriterion?.name_sv || selectedCriterion?.name_en || ''"></span>
                        </h2>
                    </div>
                    <button
                        type="button"
                        @click="selectedCriterion = null"
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
                            Öppna →
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
            @keydown.escape.window="showModal = false"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 print:hidden"
            style="display: none;"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-lg max-w-2xl w-full flex flex-col">
                <div class="flex justify-between items-center p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Full Size View') }}</h3>
                    <button
                        type="button"
                        @click="showModal = false"
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
    </body>
</html>
