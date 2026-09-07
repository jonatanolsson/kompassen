<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $project->name }} - {{ __('Accessibility Report') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased" x-data="{ selectedImage: '', showModal: false }">
        <div class="bg-white dark:bg-zinc-900 min-h-screen">
            <!-- Header -->
            <header class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <div class="max-w-6xl mx-auto px-4 py-8">
                    <div class="flex items-start gap-6">
                        @if ($project->client_logo)
                            <img 
                                src="{{ asset('storage/' . $project->client_logo) }}" 
                                alt="{{ $project->name }}" 
                                class="h-16 w-auto object-contain"
                            />
                        @endif
                        <div class="flex-1">
                            <flux:heading level="1">{{ $project->name }}</flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mt-2">
                                WCAG {{ $project->target_wcag_level }} • {{ Str::title($project->status) }}
                            </flux:text>
                            @if ($project->description)
                                <flux:text class="text-zinc-600 dark:text-zinc-400 mt-4">
                                    {{ $project->description }}
                                </flux:text>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="max-w-6xl mx-auto px-4 py-8">
                <!-- Pages Section -->
                @if ($project->pages->count() > 0)
                    <section class="mb-12">
                        <flux:heading level="2" class="mb-4">{{ __('Pages & Services') }}</flux:heading>
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
                                            <flux:text class="text-sm font-medium mb-3">{{ __('Issues on this page:') }}</flux:text>
                                            <div class="space-y-2">
                                                @foreach ($page->issues as $issue)
                                                    <div class="flex items-start gap-3 text-sm space-y-3">
                                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">
                                                            {{ Str::title($issue->severity) }}
                                                        </flux:badge>
                                                         <div class="flex-1 space-y-4">
                                                            <div>
                                                                <flux:heading level="4" class="text-sm">{{ $issue->title }}</flux:heading>
                                                                @if ($issue->description)
                                                                    <div class="text-zinc-600 dark:text-zinc-400 mt-1 text-xs">{{ Str::limit(strip_tags($issue->description), 100) }}</div>
                                                                @endif
                                                            </div>

                                                            @if ($issue->attachments->count() > 0)
                                                                <div x-data="{ open: false }" class="border border-zinc-200 dark:border-zinc-700 rounded">
                                                                    <button
                                                                        type="button"
                                                                        @click="open = !open"
                                                                        class="w-full flex items-center justify-between p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 text-sm"
                                                                    >
                                                                        <span class="font-medium">{{ __('Screenshots') }} ({{ $issue->attachments->count() }})</span>
                                                                        <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                                        </svg>
                                                                    </button>
                                                                    <div x-show="open" class="border-t border-zinc-200 dark:border-zinc-700 p-3 grid grid-cols-4 gap-1">
                                                                        @foreach ($issue->attachments->take(3) as $attachment)
                                                                            <button
                                                                                type="button"
                                                                                @click="selectedImage = '{{ asset('storage/' . $attachment->path) }}'; showModal = true"
                                                                                class="rounded overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:shadow transition cursor-pointer aspect-square"
                                                                            >
                                                                                <img 
                                                                                    src="{{ asset('storage/' . $attachment->path) }}"
                                                                                    alt="{{ $attachment->original_filename }}"
                                                                                    class="w-full h-full object-cover"
                                                                                />
                                                                            </button>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
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
                        <flux:heading level="2" class="mb-4">{{ __('Project-Wide Issues') }}</flux:heading>
                        <div class="space-y-4">
                            @foreach ($projectWideIssues as $issue)
                                <flux:card class="p-6">
                                    <div class="flex items-start justify-between mb-2">
                                        <flux:heading level="3">{{ $issue->title }}</flux:heading>
                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">
                                            {{ Str::title($issue->severity) }}
                                        </flux:badge>
                                    </div>

                                    <div class="space-y-2">
                                        <!-- Description -->
                                        @if ($issue->description)
                                            <div x-data="{ open: false }" class="border border-zinc-200 dark:border-zinc-700 rounded">
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="w-full flex items-center justify-between p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 text-sm"
                                                >
                                                    <span class="font-medium">{{ __('Description') }}</span>
                                                    <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                    </svg>
                                                </button>
                                                <div x-show="open" class="border-t border-zinc-200 dark:border-zinc-700 p-3">
                                                    <x-user-content :content="$issue->description" />
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Screenshots -->
                                        @if ($issue->attachments->count() > 0)
                                            <div x-data="{ open: false }" class="border border-zinc-200 dark:border-zinc-700 rounded">
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="w-full flex items-center justify-between p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 text-sm"
                                                >
                                                    <span class="font-medium">{{ __('Screenshots') }} ({{ $issue->attachments->count() }})</span>
                                                    <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                    </svg>
                                                </button>
                                                <div x-show="open" class="border-t border-zinc-200 dark:border-zinc-700 p-3 grid grid-cols-4 sm:grid-cols-6 gap-1">
                                                    @foreach ($issue->attachments as $attachment)
                                                        <button
                                                            type="button"
                                                            @click="selectedImage = '{{ asset('storage/' . $attachment->path) }}'; showModal = true"
                                                            class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:shadow-lg transition cursor-pointer aspect-square"
                                                        >
                                                            <img 
                                                                src="{{ asset('storage/' . $attachment->path) }}"
                                                                alt="{{ $attachment->original_filename }}"
                                                                class="w-full h-full object-cover"
                                                            />
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Metadata -->
                                        <div x-data="{ open: false }" class="border border-zinc-200 dark:border-zinc-700 rounded">
                                            <button
                                                type="button"
                                                @click="open = !open"
                                                class="w-full flex items-center justify-between p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 text-sm"
                                            >
                                                <span class="font-medium">{{ __('Details') }}</span>
                                                <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                            </button>
                                            <div x-show="open" class="border-t border-zinc-200 dark:border-zinc-700 p-3 space-y-2">
                                                <div class="flex gap-2">
                                                    <flux:badge color="zinc">
                                                        {{ __('Difficulty') }}: {{ Str::title($issue->difficulty) }}
                                                    </flux:badge>
                                                    <flux:badge color="zinc">
                                                        {{ __('Status') }}: {{ Str::title($issue->status) }}
                                                    </flux:badge>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($project->pages->count() === 0 && $projectWideIssues->count() === 0)
                    <flux:card class="p-8 text-center">
                        <flux:icon icon="inbox" class="h-12 w-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" />
                        <flux:heading level="3">{{ __('No issues reported yet') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mt-2">
                            {{ __('This project does not have any pages or issues documented yet.') }}
                        </flux:text>
                    </flux:card>
                @endif
            </main>

            <!-- Footer -->
            <footer class="bg-zinc-50 dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 mt-16 py-8">
                <div class="max-w-6xl mx-auto px-4 text-center">
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('This is a shared accessibility report. Last updated: :date', ['date' => $project->updated_at->format('Y-m-d H:i')]) }}
                    </flux:text>
                </div>
            </footer>
        </div>

        <!-- Image Modal -->
        <div
            x-show="showModal"
            @keydown.escape.window="showModal = false"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-lg max-w-lg w-full max-h-[70vh] flex flex-col">
                <div class="flex justify-end p-3">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto px-3 pb-3 flex items-center justify-center">
                    <img
                        :src="selectedImage"
                        alt="Full size preview"
                        class="max-w-full max-h-full rounded"
                    />
                </div>
            </div>
        </div>
    </body>
</html>
