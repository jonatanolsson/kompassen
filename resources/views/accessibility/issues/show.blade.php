<x-app-layout>
    <div class="product-page">
    <div class="w-full">
        <div class="product-page-header">
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
                            @can('update', $project)
                                <flux:menu.item href="{{ route('accessibility-issues.edit', [$project, $issue]) }}" icon="pencil-square" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:menu.item>
                            @endcan

                            <!-- Export -->
                            <flux:menu.item
                                as="button"
                                @click="$dispatch('openModal', 'export-issue-modal')"
                                icon="arrow-down-tray"
                            >
                                {{ __('Export') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            @can('update', $project)
                                <flux:modal.trigger name="resolve-issue-modal">
                                    <flux:menu.item icon="check-circle">
                                        {{ __('Change resolution') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>
                            @endcan
                        </flux:menu.group>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        <flux:separator class="my-6" />

        @if ($issue->page?->scope === 'out_of_scope')
            <flux:callout color="amber" class="mb-6">
                <flux:callout.text>{{ __('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.') }}</flux:callout.text>
            </flux:callout>
        @endif

        @if ($errors->has('resolution_notes') || $errors->has('resolution_status'))
            <flux:callout color="red" class="mb-6">
                <flux:callout.text>{{ __('Could not change status. Open the resolution action and correct the highlighted fields.') }}</flux:callout.text>
            </flux:callout>
        @endif

        <!-- Description -->
        @if ($issue->description)
            <div class="product-section mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Description') }}</flux:heading>
                <x-markdown :markdown="$issue->description" />
            </div>
        @endif

        @if ($issue->mermaid_diagram)
            <div class="product-section mb-8">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <flux:heading level="2">{{ __('Issue diagram') }}</flux:heading>
                    <flux:badge color="zinc">Mermaid</flux:badge>
                </div>
                <pre
                    id="issue-mermaid-{{ $issue->id }}"
                    data-mermaid-diagram
                    role="img"
                    aria-label="{{ __('Issue diagram') }}"
                    class="mermaid min-h-24 overflow-auto rounded-lg bg-white px-4 py-3 text-sm text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100"
                >{{ $issue->mermaid_diagram }}</pre>
            </div>
        @endif

        @if ($issue->resolution_notes)
            <div class="product-section mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Resolution Notes') }}</flux:heading>
                <flux:text class="whitespace-pre-line">{{ $issue->resolution_notes }}</flux:text>
            </div>
        @endif

        <!-- Images -->
        @if ($issue->attachments->count() > 0)
            <div class="product-section mb-8">
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
        <div class="product-section mb-8 grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
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
                        @can('update', $project)
                            <form action="{{ route('accessibility-issues.assign', [$project, $issue]) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="assigned_to" value="">
                                <flux:button type="submit" variant="ghost" size="sm" icon="x-mark">
                                    {{ __('Unassign') }}
                                </flux:button>
                            </form>
                        @endcan
                    </div>
                @else
                    @can('update', $project)
                        <form action="{{ route('accessibility-issues.assign', [$project, $issue]) }}" method="POST" class="flex flex-wrap items-center gap-2">
                            @csrf
                            @method('PUT')
                            <flux:select name="assigned_to" aria-label="{{ __('Assign to a member') }}" required>
                                <option value="">{{ __('Select a project member') }}</option>
                                @foreach ($projectMembers as $member)
                                    <option value="{{ $member->user_id }}">{{ $member->user->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:button type="submit" variant="outline" size="sm">{{ __('Assign') }}</flux:button>
                        </form>
                    @else
                        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Unassigned') }}</flux:text>
                    @endcan
                @endif
            </div>
        </div>

        <!-- WCAG Criteria -->
        @if ($wcagCriteria->count() > 0)
            <div class="product-section mb-8">
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

        <div class="product-section mb-8">
            @if ($issue->examples->isNotEmpty())
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <flux:heading level="2">{{ __('Knowledge Base Examples') }}</flux:heading>
                        <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ __('Reusable guidance linked to this issue.') }}
                        </flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:badge color="blue">{{ $issue->examples->count() }}</flux:badge>
                        @can('update', $project)
                            <flux:button
                                href="{{ route('accessibility-issues.edit', [$project, $issue]) }}"
                                variant="ghost"
                                size="sm"
                                icon="pencil"
                                wire:navigate
                            >
                                {{ __('Edit examples') }}
                            </flux:button>
                        @endcan
                    </div>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($issue->examples as $example)
                        <article class="space-y-3 py-4 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <flux:heading level="3" class="text-base">{{ $example->title }}</flux:heading>
                                    @if ($example->criterion)
                                        <flux:text size="sm" class="mt-1 text-zinc-500 dark:text-zinc-400">
                                            {{ $example->criterion->standard }} · {{ $example->criterion->number }} · {{ $example->criterion->name_sv ?? $example->criterion->name_en }}
                                        </flux:text>
                                    @endif
                                </div>
                                @if ($example->code_language)
                                    <flux:badge size="sm" color="zinc">{{ strtoupper($example->code_language) }}</flux:badge>
                                @endif
                            </div>

                            @if ($example->description)
                                <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                    <x-user-content :content="$example->description" />
                                </div>
                            @endif

                            @if ($example->attachments->isNotEmpty())
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    @foreach ($example->attachments as $attachment)
                                        <a
                                            href="{{ asset('storage/'.$attachment->path) }}"
                                            target="_blank"
                                            class="group overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
                                        >
                                            <img
                                                src="{{ asset('storage/'.$attachment->path) }}"
                                                alt="{{ $attachment->alt_text ?: $attachment->original_filename }}"
                                                class="aspect-video w-full object-cover transition duration-200 group-hover:scale-105"
                                            />
                                            <span class="block truncate px-2 py-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                                                {{ $attachment->original_filename }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if ($example->code_snippet)
                                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="bg-zinc-100 px-3 py-1.5 dark:bg-zinc-800">
                                        <flux:text size="xs" class="font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                            {{ __('Code Snippet') }}
                                        </flux:text>
                                    </div>
                                    @if ($example->code_language === 'mermaid')
                                        <pre
                                            id="issue-mermaid-{{ $example->id }}"
                                            data-mermaid-diagram
                                            role="img"
                                            aria-label="{{ $example->title }}"
                                            class="mermaid min-h-24 overflow-auto bg-white px-4 py-3 text-sm text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100"
                                        >{{ $example->code_snippet }}</pre>
                                    @else
                                        <pre class="max-h-56 overflow-auto bg-zinc-950 px-4 py-3 text-sm text-zinc-100"><code>{{ $example->code_snippet }}</code></pre>
                                    @endif
                                </div>
                            @endif

                            @if ($example->referenceLinks->isNotEmpty())
                                <div class="flex flex-wrap gap-x-4 gap-y-2">
                                    @foreach ($example->referenceLinks as $link)
                                        <flux:link href="{{ $link->url }}" target="_blank" class="text-sm" icon-trailing="arrow-top-right-on-square">
                                            {{ $link->label ?: $link->url }}
                                        </flux:link>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading level="2">{{ __('Knowledge Base Examples') }}</flux:heading>
                        <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ __('No examples linked to this issue yet.') }}
                        </flux:text>
                    </div>
                    @can('update', $project)
                        <flux:button
                            href="{{ route('accessibility-issues.edit', [$project, $issue]) }}"
                            variant="outline"
                            size="sm"
                            icon="plus"
                            wire:navigate
                        >
                            {{ __('Add examples') }}
                        </flux:button>
                    @endcan
                </div>
            @endif
        </div>

        <flux:separator class="my-6" />

        <!-- Metadata -->
        <div class="product-section grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Created') }}</flux:text>
                <flux:text>{{ $issue->created_at->translatedFormat('j M Y H:i') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Last Updated') }}</flux:text>
                <flux:text>{{ $issue->updated_at->translatedFormat('j M Y H:i') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Issue ID') }}</flux:text>
                <flux:text class="font-mono text-xs">{{ $issue->id }}</flux:text>
            </div>
        </div>
    </div>
</div>

@can('update', $project)
<flux:modal name="resolve-issue-modal" class="md:w-[32rem]">
    <form action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="space-y-6">
        @csrf
        @method('PATCH')

        <div>
            <flux:heading level="2">{{ __('Change resolution') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Choose a resolution status and add context for the audit trail.') }}</flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            <flux:select name="resolution_status">
                <option value="open">{{ __('Open') }}</option>
                <option value="fixed" @selected($issue->resolution_status === 'fixed')>{{ __('Mark as fixed') }}</option>
                <option value="wontfix" @selected($issue->resolution_status === 'wontfix')>{{ __('Mark as wontfix') }}</option>
            </flux:select>
            <flux:error name="resolution_status" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Resolution Notes') }}</flux:label>
            <flux:textarea name="resolution_notes" rows="4">{{ old('resolution_notes', $issue->resolution_notes) }}</flux:textarea>
            <flux:description>{{ __('A note is required when closing an issue.') }}</flux:description>
            <flux:error name="resolution_notes" />
        </flux:field>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
        </div>
    </form>
</flux:modal>
@endcan

<!-- Export Modal Component -->
<livewire:export-issue-modal :project="$project" :issue="$issue" />

</x-app-layout>
