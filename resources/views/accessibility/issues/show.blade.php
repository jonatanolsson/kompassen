<x-app-layout>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    <div class="w-full">
        <div class="flex flex-col gap-4 border-b border-zinc-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-800">
            <div>
                <flux:heading level="1" class="mb-2">{{ $issue->title }}</flux:heading>
                <div class="flex gap-2">
                    <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : ($issue->severity === 'moderate' ? 'yellow' : 'green'))">
                        {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->severity))) }}
                    </flux:badge>
                    <flux:badge color="zinc">{{ __(Str::title(str_replace(['_', '-'], ' ', $issue->status))) }}</flux:badge>
                    <flux:badge color="blue">{{ __('Estimated remediation effort') }}: {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) }}</flux:badge>
                </div>
            </div>
            <div class="flex gap-2 items-center">
                <flux:button href="{{ route('accessibility-projects.show', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>

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
        @if (filled($issue->summary))
            <div class="mb-8 rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10">
                <flux:heading level="2" class="mb-4">{{ __('Summary') }}</flux:heading>
                <flux:text class="whitespace-pre-line">{{ $issue->summary }}</flux:text>
            </div>
        @endif

        @if ($issue->description)
            <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Description') }}</flux:heading>
                <x-markdown :markdown="$issue->description" />
            </div>
        @endif

        @if ($issue->problem_code_snippet || $issue->corrected_code_snippet)
            <div class="mb-8 rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10">
                <flux:heading level="2" class="mb-4">{{ __('Issue-specific code') }}</flux:heading>
                <div class="space-y-4">
                    @if ($issue->problem_code_snippet)
                        <div>
                            <flux:heading level="3" class="mb-2 text-sm">{{ __('Code causing the issue') }}</flux:heading>
                            <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-950 p-4 text-sm text-zinc-100"><code>{{ $issue->problem_code_snippet }}</code></pre>
                        </div>
                    @endif
                    @if ($issue->corrected_code_snippet)
                        <div>
                            <flux:heading level="3" class="mb-2 text-sm">{{ __('Suggested corrected code') }}</flux:heading>
                            <pre class="max-h-96 overflow-auto rounded-lg bg-zinc-950 p-4 text-sm text-zinc-100"><code>{{ $issue->corrected_code_snippet }}</code></pre>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($issue->mermaid_diagram)
            <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
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
            <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Resolution Notes') }}</flux:heading>
                <flux:text class="whitespace-pre-line">{{ $issue->resolution_notes }}</flux:text>
            </div>
        @endif

        <!-- Images -->
        @if ($issue->attachments->count() > 0)
            <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
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
        <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8 grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
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
                @can('update', $project)
                    <div class="flex flex-wrap items-center gap-2">
                        <form action="{{ route('accessibility-issues.assign', [$project, $issue]) }}" method="POST" class="flex flex-wrap items-center gap-2">
                            @csrf
                            @method('PUT')
                            <flux:select name="assigned_to" aria-label="{{ __('Assign to a member') }}" required>
                                <option value="">{{ __('Select a project member') }}</option>
                                @foreach ($projectMembers as $member)
                                    <option value="{{ $member->user_id }}" @selected((string) $issue->assigned_to === (string) $member->user_id)>{{ $member->user->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:button type="submit" variant="outline" size="sm">
                                {{ $issue->assignedTo ? __('Update assignment') : __('Assign') }}
                            </flux:button>
                        </form>
                        @if ($issue->assignedTo)
                            <form action="{{ route('accessibility-issues.assign', [$project, $issue]) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="assigned_to" value="">
                                <flux:button type="submit" variant="ghost" size="sm" icon="x-mark">
                                    {{ __('Unassign') }}
                                </flux:button>
                            </form>
                        @endif
                    </div>
                @else
                    <div>
                        @if ($issue->assignedTo)
                            <flux:text>{{ $issue->assignedTo->name }}</flux:text>
                        @else
                            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Unassigned') }}</flux:text>
                        @endif
                    </div>
                @endcan
            </div>
        </div>

        <!-- WCAG Criteria -->
        @if ($wcagCriteria->count() > 0)
            <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
                <flux:heading level="2" class="mb-4">{{ __('WCAG Success Criteria') }}</flux:heading>
                <flux:table class="w-full table-auto! text-sm">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Criterion') }}</flux:table.column>
                        <flux:table.column class="whitespace-nowrap">{{ __('Level') }}</flux:table.column>
                        <flux:table.column class="whitespace-nowrap">{{ __('W3C') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($wcagCriteria as $criterion)
                            <flux:table.row>
                                <flux:table.cell class="min-w-0 py-2">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <span class="font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $criterion->standard }} · {{ $criterion->number }}</span>
                                        @if ($criterion->isOutsideProjectTargetVersion($project->target_wcag_version))
                                            <flux:badge color="amber" size="sm">{{ __('Nice to have') }}</flux:badge>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-medium">{{ $criterion->name_sv ?? $criterion->name_en }}</span>
                                    </div>
                                    @if ($criterion->description_sv || $criterion->description_en)
                                        <flux:text size="sm" class="mt-1 whitespace-normal text-zinc-600 dark:text-zinc-400">
                                            {{ $criterion->description_sv ?? $criterion->description_en }}
                                        </flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap py-2">
                                    <flux:badge color="blue">{{ Str::upper($criterion->level) }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap py-2">
                                    @if ($criterion->url)
                                        <a href="{{ $criterion->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            {{ __('W3C') }} &rarr;
                                        </a>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @endif

        <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
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

        <!-- Metadata -->
        <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
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
