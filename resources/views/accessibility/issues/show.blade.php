<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-start justify-between mb-8">
            <div>
                <flux:heading level="1" class="mb-2">{{ $issue->title }}</flux:heading>
                <div class="flex gap-2">
                    <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))">
                        {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->severity))) }}
                    </flux:badge>
                    <flux:badge color="zinc">{{ __(Str::title(str_replace(['_', '-'], ' ', $issue->status))) }}</flux:badge>
                    <flux:badge color="blue">{{ __('Difficulty') }}: {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) }}</flux:badge>
                </div>
            </div>
            <div class="flex gap-2 items-center">
                <a href="{{ route('accessibility-projects.show', $project) }}">
                    <flux:button variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
                </a>

                <!-- Actions Dropdown -->
                <flux:dropdown>
                    <flux:button variant="outline" icon="ellipsis-horizontal">{{ __('Actions') }}</flux:button>

                    <flux:menu>
                        <flux:menu.group>
                            <!-- Edit -->
                            <flux:menu.item href="{{ route('accessibility-issues.edit', [$project, $issue]) }}" icon="pencil-square" wire:navigate>
                                {{ __('Edit') }}
                            </flux:menu.item>

                            <!-- Export -->
                            <flux:menu.item
                                as="button"
                                @click="$dispatch('openModal', 'export-issue-modal')"
                                icon="arrow-down-tray"
                            >
                                {{ __('Export') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <!-- Mark as fixed -->
                            <flux:menu.item
                                as="button"
                                onclick="submitResolution('fixed', 'resolve-fixed-form')"
                                icon="check"
                            >
                                {{ __('Mark as fixed') }}
                            </flux:menu.item>

                            <!-- Mark as wontfix -->
                            <flux:menu.item
                                as="button"
                                onclick="submitResolution('wontfix', 'resolve-wontfix-form')"
                                icon="x-mark"
                                class="text-red-600"
                            >
                                {{ __('Mark as wontfix') }}
                            </flux:menu.item>
                        </flux:menu.group>
                    </flux:menu>
                </flux:dropdown>

                <!-- Hidden forms for resolution -->
                <form id="resolve-fixed-form" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="hidden">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="resolution_status" value="" />
                    <input type="hidden" name="resolution_notes" value="" />
                </form>

                <form id="resolve-wontfix-form" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="hidden">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="resolution_status" value="" />
                    <input type="hidden" name="resolution_notes" value="" />
                </form>
            </div>

            <script>
                function submitResolution(status, formId) {
                    if (!confirm('{{ __('Are you sure you want to change resolution status?') }}')) return false;
                    const notes = prompt('{{ __('Add resolution notes (optional)') }}');
                    const form = document.getElementById(formId);
                    if (!form) return false;
                    form.querySelector('input[name="resolution_status"]').value = status;
                    form.querySelector('input[name="resolution_notes"]').value = notes ? notes : '';
                    form.submit();
                }
            </script>
        </div>

        <flux:separator class="my-6" />

        <!-- Description -->
        @if ($issue->description)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Description') }}</flux:heading>
                <x-markdown :markdown="$issue->description" />
            </div>
        @endif

        @if ($issue->resolution_notes)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Resolution Notes') }}</flux:heading>
                <flux:text class="whitespace-pre-line">{{ $issue->resolution_notes }}</flux:text>
            </div>
        @endif

        <!-- Images -->
        @if ($issue->attachments->count() > 0)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Images') }}</flux:heading>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach ($issue->attachments as $attachment)
                        <div class="group min-w-0">
                            <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="block aspect-square overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                                <img 
                                    src="{{ asset('storage/' . $attachment->path) }}" 
                                    alt="{{ $attachment->original_filename }}"
                                    class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                />
                            </a>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-2 truncate">{{ $attachment->original_filename }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-8">
            @if ($issue->page)
                <div>
                    <flux:heading level="3" class="text-sm mb-2">{{ __('Page/Service') }}</flux:heading>
                    <flux:text>{{ $issue->page->name }}</flux:text>
                </div>
            @endif

            @if ($issue->component_area)
                <div>
                    <flux:heading level="3" class="text-sm mb-2">{{ __('Component Area') }}</flux:heading>
                    <flux:text>{{ $issue->component_area }}</flux:text>
                </div>
            @endif

            <!-- Assignee -->
            <div>
                <flux:heading level="3" class="text-sm mb-2">{{ __('Assigned To') }}</flux:heading>
                @if ($issue->assignedTo)
                    <div class="flex items-center justify-between">
                        <flux:text>{{ $issue->assignedTo->name }}</flux:text>
                        <form action="{{ route('accessibility-issues.assign', [$project, $issue]) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="assigned_to" value="">
                            <flux:button type="submit" variant="ghost" size="sm" icon="x-mark">
                                {{ __('Unassign') }}
                            </flux:button>
                        </form>
                    </div>
                @else
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Unassigned') }}</flux:text>
                @endif
            </div>
        </div>

        <!-- WCAG Criteria -->
        @if ($wcagCriteria->count() > 0)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">{{ __('WCAG Success Criteria') }}</flux:heading>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($wcagCriteria as $criterion)
                        <div class="py-4 first:pt-0 last:pb-0">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3 flex-1">
                                        <span class="font-bold text-blue-600 dark:text-blue-400 min-w-fit">{{ $criterion->number }}</span>
                                        <div class="flex-1 min-w-0">
                                            <flux:heading level="4" class="text-sm">{{ $criterion->name_sv ?? $criterion->name_en }}</flux:heading>
                                            <div class="flex items-center gap-2 mt-1">
                                                <flux:badge color="blue" class="text-xs">{{ Str::upper($criterion->level) }}</flux:badge>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($criterion->url)
                                        <a href="{{ $criterion->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs font-medium whitespace-nowrap">
                                            W3C →
                                        </a>
                                    @endif
                                </div>
                                @if ($criterion->description_sv || $criterion->description_en)
                                    <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                        {{ $criterion->description_sv ?? $criterion->description_en }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <flux:separator class="my-6" />

        <!-- Metadata -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Created') }}</flux:text>
                <flux:text>{{ $issue->created_at->format('Y-m-d H:i') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Last Updated') }}</flux:text>
                <flux:text>{{ $issue->updated_at->format('Y-m-d H:i') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Issue ID') }}</flux:text>
                <flux:text class="font-mono text-xs">{{ $issue->id }}</flux:text>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal Component -->
<livewire:export-issue-modal :project="$project" :issue="$issue" />

</x-app-layout>
