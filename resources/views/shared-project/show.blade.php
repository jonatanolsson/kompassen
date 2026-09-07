<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{ __('') }} <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $project->name }} - {{ __('Accessibility Report') }}</title> {{ __('') }} <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"> {{ __('') }} <body class="font-sans antialiased" x-data="{ selectedImage: '', showModal: false }"> {{ __('') }} <header class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700"> {{ __('') }} <img 
                                src="{{ asset('storage/' . $project->client_logo) }}" 
                                alt="{{ $project->name }}" 
                                class="h-16 w-auto object-contain"
                            /> {{ __('') }} </flux:heading>
                            <flux:text class="text-zinc-600 dark:text-zinc-400 mt-2">
                                WCAG {{ $project->target_wcag_level }} • {{ Str::title($project->status) }}
                            </flux:text>
                            @if ($project->description)
                                <flux:text class="text-zinc-600 dark:text-zinc-400 mt-4">
                                    {{ $project->description }}
                                </flux:text> {{ __('') }} </div> {{ __('') }} <main class="max-w-6xl mx-auto px-4 py-8">
                <!-- Pages Section -->
                @if ($project->pages->count() > 0)
                    <section class="mb-12">
                        <flux:heading level="2" class="mb-4">{{ __('Pages & Services') }}</flux:heading>
                        <div class="space-y-4">
                            @foreach ($project-> {{ __('') }} <div>
                                            <flux:heading level="3">{{ $page->name }}</flux:heading>
                                            @if ($page-> {{ __('') }} </a>
                                                </flux:text> {{ __('') }} </flux:badge>
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
                                                @foreach ($page-> {{ __('') }} </flux:badge> {{ __('') }} <flux:heading level="4" class="text-sm">{{ $issue->title }}</flux:heading>
                                                                @if ($issue->description)
                                                                    <div class="text-zinc-600 dark:text-zinc-400 mt-1 text-xs">{{ Str::limit(strip_tags($issue->description), 100) }}</div>
                                                                @endif
                                                            </div>

                                                            @if ($issue->attachments->count() > {{ __('') }} <span class="font-medium">{{ __('Screenshots') }} ({{ $issue->attachments-> {{ __('') }} <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /> {{ __('') }} <div x-show="open" class="border-t border-zinc-200 dark:border-zinc-700 p-3 grid grid-cols-4 gap-1">
                                                                        @foreach ($issue->attachments->take(3) as $attachment)
                                                                            <button
                                                                                type="button"
                                                                                @click="selectedImage = '{{ asset('storage/' . $attachment-> {{ __('') }} </button> {{ __('') }} </div>
                                                    </div> {{ __('') }} </flux:card> {{ __('') }} <flux:separator />
                @endif

                <!-- Project-wide Issues -->
                @php
                    $projectWideIssues = $project->issues()->whereNull('page_id')->get();
                @endphp
                @if ($projectWideIssues->count() > 0)
                    <section class="mt-12">
                        <flux:heading level="2" class="mb-4">{{ __('Project-Wide Issues') }}</flux:heading>
                        <div class="space-y-4"> {{ __('') }} <flux:heading level="3">{{ $issue-> {{ __('') }} </flux:badge> {{ __('') }} <!-- Description -->
                                        @if ($issue-> {{ __('') }} <span class="font-medium">{{ __('Description') }}</span> {{ __('') }} </svg> {{ __('') }} <x-user-content :content="$issue->description" /> {{ __('') }} <!-- Screenshots -->
                                        @if ($issue->attachments->count() > {{ __('') }} <span class="font-medium">{{ __('Screenshots') }} ({{ $issue->attachments-> {{ __('') }} <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /> {{ __('') }} <div x-show="open" class="border-t border-zinc-200 dark:border-zinc-700 p-3 grid grid-cols-4 sm:grid-cols-6 gap-1">
                                                    @foreach ($issue->attachments as $attachment)
                                                        <button
                                                            type="button"
                                                            @click="selectedImage = '{{ asset('storage/' . $attachment-> {{ __('') }} </button> {{ __('') }} <!-- Metadata --> {{ __('') }} <span class="font-medium">{{ __('Details') }}</span> {{ __('') }} </svg> {{ __('') }} <div class="flex gap-2">
                                                    <flux:badge color="zinc">
                                                        {{ __('Difficulty') }}: {{ Str::title($issue->difficulty) }}
                                                    </flux:badge>
                                                    <flux:badge color="zinc">
                                                        {{ __('Status') }}: {{ Str::title($issue-> {{ __('') }} </div> {{ __('') }} </flux:card> {{ __('') }} <flux:card class="p-8 text-center">
                        <flux:icon icon="inbox" class="h-12 w-12 mx-auto text-zinc-400 dark:text-zinc-600 mb-4" />
                        <flux:heading level="3">{{ __('No issues reported yet') }}</flux:heading>
                        <flux:text class="text-zinc-600 dark:text-zinc-400 mt-2">
                            {{ __('This project does not have any pages or issues documented yet.') }}
                        </flux:text>
                    </flux:card> {{ __('') }} <footer class="bg-zinc-50 dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 mt-16 py-8">
                <div class="max-w-6xl mx-auto px-4 text-center">
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('This is a shared accessibility report. Last updated: :date', ['date' => $project->updated_at-> {{ __('') }} </footer> {{ __('') }} <div
            x-show="showModal"
            @keydown.escape.window="showModal = false"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            style="display: none;"
        > {{ __('') }} <button
                        type="button"
                        @click="showModal = false"
                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200"
                    > {{ __('') }} </svg> {{ __('') }} <div class="flex-1 overflow-auto px-3 pb-3 flex items-center justify-center"> {{ __('') }} </div> {{ __('') }} </html>
