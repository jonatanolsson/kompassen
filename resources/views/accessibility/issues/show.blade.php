<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-start justify-between mb-8">
            <div>
                <flux:heading level="1" class="mb-2">{{ $issue->title }}</flux:heading>
                <div class="flex gap-2">
                    <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))">
                        {{ Str::title($issue->severity) }}
                    </flux:badge>
                    <flux:badge color="zinc">{{ Str::title($issue->status) }}</flux:badge>
                    <flux:badge color="blue">Difficulty: {{ Str::title($issue->difficulty) }}</flux:badge>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('accessibility-issues.edit', [$project, $issue]) }}">
                    <flux:button variant="outline" icon="pencil">Edit</flux:button>
                </a>
                <a href="{{ route('accessibility-projects.show', $project) }}">
                    <flux:button variant="ghost">Back</flux:button>
                </a>
            </div>
        </div>

        <flux:separator class="my-6" />

        <!-- Description -->
        @if ($issue->description)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">Description</flux:heading>
                <flux:card class="p-6">
                    <x-user-content :content="$issue->description" />
                </flux:card>
            </div>
        @endif

        <!-- Images -->
        @if ($issue->attachments->count() > 0)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">Images</flux:heading>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($issue->attachments as $attachment)
                        <div class="group">
                            <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="block overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 hover:shadow-lg transition">
                                <img 
                                    src="{{ asset('storage/' . $attachment->path) }}" 
                                    alt="{{ $attachment->original_filename }}"
                                    class="w-full h-48 object-cover group-hover:scale-105 transition"
                                />
                            </a>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-2 truncate">{{ $attachment->original_filename }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @if ($issue->page)
                <flux:card class="p-4">
                    <flux:heading level="3" class="text-sm mb-2">Page/Service</flux:heading>
                    <flux:text>{{ $issue->page->name }}</flux:text>
                </flux:card>
            @endif

            @if ($issue->component_area)
                <flux:card class="p-4">
                    <flux:heading level="3" class="text-sm mb-2">Component Area</flux:heading>
                    <flux:text>{{ $issue->component_area }}</flux:text>
                </flux:card>
            @endif
        </div>

        <!-- WCAG Criteria -->
        @if ($wcagCriteria->count() > 0)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">WCAG Success Criteria</flux:heading>
                <div class="space-y-2">
                    @foreach ($wcagCriteria as $criterion)
                        <flux:card class="p-4">
                            <div class="flex items-start gap-3">
                                <span class="font-bold text-blue-600 dark:text-blue-400 min-w-fit">{{ $criterion->number }}</span>
                                <div class="flex-1">
                                    <flux:heading level="4" class="text-sm">{{ $criterion->name_en }}</flux:heading>
                                    <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Level {{ Str::upper($criterion->level) }}</flux:text>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            </div>
        @endif

        <flux:separator class="my-6" />

        <!-- Metadata -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">Created</flux:text>
                <flux:text>{{ $issue->created_at->format('Y-m-d H:i') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">Last Updated</flux:text>
                <flux:text>{{ $issue->updated_at->format('Y-m-d H:i') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">Issue ID</flux:text>
                <flux:text class="font-mono text-xs">{{ $issue->id }}</flux:text>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
