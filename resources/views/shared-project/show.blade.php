<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $project->name }} - Accessibility Report</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="bg-white dark:bg-zinc-900 min-h-screen">
            <!-- Header -->
            <header class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <div class="max-w-6xl mx-auto px-4 py-8">
                    <div>
                        <flux:heading level="1">{{ $project->name }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mt-2">
                            WCAG {{ $project->target_wcag_level }} • {{ Str::title($project->status) }}
                        </flux:text>
                    </div>
                    @if ($project->description)
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mt-4">
                            {{ $project->description }}
                        </flux:text>
                    @endif
                </div>
            </header>

            <!-- Main Content -->
            <main class="max-w-6xl mx-auto px-4 py-8">
                <!-- Pages Section -->
                @if ($project->pages->count() > 0)
                    <section class="mb-12">
                        <flux:heading level="2" class="mb-4">Pages & Services</flux:heading>
                        <div class="space-y-4">
                            @foreach ($project->pages as $page)
                                <flux:card class="p-6">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <flux:heading level="3">{{ $page->name }}</flux:heading>
                                            @if ($page->url)
                                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                                    <a href="{{ $page->url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                        {{ $page->url }}
                                                    </a>
                                                </flux:text>
                                            @endif
                                        </div>
                                        <flux:badge :color="$page->scope === 'in_scope' ? 'green' : 'amber'">
                                            {{ Str::title(str_replace('_', ' ', $page->scope)) }}
                                        </flux:badge>
                                    </div>
                                    @if ($page->description)
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                                            {{ $page->description }}
                                        </flux:text>
                                    @endif
                                    <!-- Page Issues -->
                                    @if ($page->issues->count() > 0)
                                        <div class="mt-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                            <flux:text class="text-sm font-medium mb-3">Issues on this page:</flux:text>
                                            <div class="space-y-2">
                                                @foreach ($page->issues as $issue)
                                                    <div class="flex items-start gap-3 text-sm">
                                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">
                                                            {{ Str::title($issue->severity) }}
                                                        </flux:badge>
                                                         <div>
                                                            <flux:heading level="4" class="text-sm">{{ $issue->title }}</flux:heading>
                                                            @if ($issue->description)
                                                                <div class="text-zinc-600 dark:text-zinc-400 mt-1">{{ Str::limit(strip_tags($issue->description), 100) }}</div>
                                                            @endif
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

                    <flux:separator />
                @endif

                <!-- Project-wide Issues -->
                @php
                    $projectWideIssues = $project->issues()->whereNull('page_id')->get();
                @endphp
                @if ($projectWideIssues->count() > 0)
                    <section class="mt-12">
                        <flux:heading level="2" class="mb-4">Project-Wide Issues</flux:heading>
                        <div class="space-y-4">
                            @foreach ($projectWideIssues as $issue)
                                <flux:card class="p-6">
                                    <div class="flex items-start justify-between mb-2">
                                        <flux:heading level="3">{{ $issue->title }}</flux:heading>
                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">
                                            {{ Str::title($issue->severity) }}
                                        </flux:badge>
                                    </div>
                                    @if ($issue->description)
                                        <div class="mt-2">
                                            <x-user-content :content="$issue->description" />
                                        </div>
                                    @endif
                                    <div class="mt-4 flex gap-2">
                                        <flux:badge color="zinc">
                                            Difficulty: {{ Str::title($issue->difficulty) }}
                                        </flux:badge>
                                        <flux:badge color="zinc">
                                            Status: {{ Str::title($issue->status) }}
                                        </flux:badge>
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($project->pages->count() === 0 && $projectWideIssues->count() === 0)
                    <flux:card class="p-8 text-center">
                        <flux:icon icon="inbox" class="h-12 w-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" />
                        <flux:heading level="3">No issues reported yet</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mt-2">
                            This project doesn't have any pages or issues documented yet.
                        </flux:text>
                    </flux:card>
                @endif
            </main>

            <!-- Footer -->
            <footer class="bg-zinc-50 dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 mt-16 py-8">
                <div class="max-w-6xl mx-auto px-4 text-center">
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                        This is a shared accessibility report. Last updated: {{ $project->updated_at->format('Y-m-d H:i') }}
                    </flux:text>
                </div>
            </footer>
        </div>
    </body>
</html>
