<x-app-layout>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    <div class="w-full">
        <div class="flex flex-col gap-4 border-b border-zinc-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-800">
            <div>
                <flux:heading level="1">{{ $project->name }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">WCAG {{ $project->target_wcag_level }} • {{ __(Str::title(str_replace(['_', '-'], ' ', $project->status))) }}</flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:button href="{{ route('accessibility-projects.index') }}" variant="ghost" icon="arrow-left">
                    {{ __('Back') }}
                </flux:button>
                <flux:button href="{{ route('accessibility-projects.preview', $project) }}" target="_blank" icon="eye" variant="primary">
                    {{ __('Current project preview') }}
                </flux:button>

                <flux:dropdown align="end">
                    <flux:button variant="outline" icon="ellipsis-horizontal">
                        {{ __('Actions') }}
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item href="{{ route('accessibility-reports.create', $project) }}" icon="document-text" wire:navigate>
                            {{ __('Generate Report') }}
                        </flux:menu.item>
                        <flux:menu.item href="{{ route('project-members.index', $project) }}" icon="users" wire:navigate>
                            {{ __('Manage Members') }}
                        </flux:menu.item>
                        <flux:menu.item href="{{ route('accessibility-reports.index', $project) }}" icon="link">
                            {{ __('Customer reports and links') }}
                        </flux:menu.item>
                        @if ($project->shareLinks->whereNull('report_id')->isNotEmpty())
                            <flux:modal.trigger name="share-modal">
                                <flux:menu.item icon="exclamation-triangle">{{ __('Legacy links') }}</flux:menu.item>
                            </flux:modal.trigger>
                        @endif
                        <flux:menu.item href="{{ route('accessibility-projects.edit', $project) }}" icon="pencil" wire:navigate>
                            {{ __('Edit') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                        <flux:modal.trigger name="delete-project-modal">
                            <flux:menu.item as="button" type="button" variant="danger" icon="trash">
                                {{ __('Delete') }}
                            </flux:menu.item>
                        </flux:modal.trigger>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        @if ($errors->has('resolution_notes') || $errors->has('resolution_status'))
            <flux:callout color="red" class="mb-6">
                <flux:callout.text>{{ __('Could not change status. Open the resolution action and correct the highlighted fields.') }}</flux:callout.text>
                @if ($project->issues->contains(fn ($issue) => (string) $issue->id === old('resolution_issue_id')))
                    <flux:modal.trigger name="resolve-project-issue-{{ old('resolution_issue_id') }}">
                        <flux:button type="button" size="sm" variant="outline">{{ __('Change resolution') }}</flux:button>
                    </flux:modal.trigger>
                @endif
            </flux:callout>
        @endif

        <flux:modal name="delete-project-modal" class="md:w-[28rem]">
            <form action="{{ route('accessibility-projects.destroy', $project) }}" method="POST" class="space-y-6">
                @csrf
                @method('DELETE')

                <div>
                    <flux:heading level="2">{{ __('Delete this project?') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                </div>
            </form>
        </flux:modal>

        @if ($project->description)
            <section class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
                <flux:heading level="2" class="mb-3">{{ __('Project background') }}</flux:heading>
                <x-user-content :content="$project->description" />
            </section>
        @endif

        @if ($project->audit_scope || $project->scope_limitations)
            <section class="mb-8 space-y-4">
                <flux:heading level="2">{{ __('Audit scope') }}</flux:heading>
                @if ($project->audit_scope)
                    <div>
                        <flux:heading level="3" size="sm">{{ __('Purpose and included user journeys') }}</flux:heading>
                        <flux:text class="mt-1 whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $project->audit_scope }}</flux:text>
                    </div>
                @endif
                @if ($project->scope_limitations)
                    <div>
                        <flux:heading level="3" size="sm">{{ __('Project limitations') }}</flux:heading>
                        <flux:text class="mt-1 whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $project->scope_limitations }}</flux:text>
                    </div>
                @endif
            </section>
        @endif

        <!-- Pages Section -->
        <div id="pages-services" class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
            <div class="flex items-center justify-between mb-4">
                <flux:heading level="2">{{ __('Pages & Services') }}</flux:heading>
                <flux:button href="{{ route('accessibility-pages.create', $project) }}" variant="primary" icon="plus" size="sm">{{ __('Add Page or Service') }}</flux:button>
            </div>

            @if ($project->pages->isEmpty())
                <div class="py-4">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No pages or services added yet.') }}</flux:text>
                </div>
            @else
                <div class="max-w-full overflow-x-auto">
                    <flux:table class="w-full table-auto! text-sm">
                        <flux:table.columns>
                            <flux:table.column class="py-2">{{ __('Name') }}</flux:table.column>
                            <flux:table.column class="whitespace-nowrap py-2">{{ __('Scope') }}</flux:table.column>
                            <flux:table.column class="whitespace-nowrap py-2 text-right">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        @foreach ($project->pages as $page)
                            @php
                                $resourceType = $page->resource_type ?? 'page';
                            @endphp
                            <flux:table.row>
                                <flux:table.cell class="min-w-0 py-2">
                                    <div class="flex min-w-0 flex-col gap-1">
                                        <span class="truncate font-medium">{{ $page->name }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __($resourceType === 'service' ? 'Service' : 'Page') }}</span>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap py-2 text-sm">
                                    <div>{{ __($page->scope === 'in_scope' ? 'In Scope' : 'Out of Scope') }}</div>
                                    @if ($page->scope === 'out_of_scope' && $page->scope_exclusion_reason)
                                        <div class="max-w-sm whitespace-normal text-xs text-zinc-500 dark:text-zinc-400">{{ $page->scope_exclusion_reason }}</div>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap py-2 text-right">
                                    <div class="inline-flex items-center justify-end gap-1">
                                        <flux:button href="{{ route('accessibility-pages.show', [$project, $page]) }}" variant="ghost" size="sm">
                                            {{ __('View details') }}
                                        </flux:button>
                                        <flux:button href="{{ route('accessibility-pages.edit', [$project, $page]) }}" variant="ghost" size="sm" icon="pencil">{{ __('Edit') }}</flux:button>
                                        <flux:modal.trigger name="delete-page-{{ $page->id }}">
                                            <flux:button type="button" variant="ghost" size="sm" icon="trash" aria-label="{{ __('Delete') }}" />
                                        </flux:modal.trigger>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table>
                </div>
            @endif

            @foreach ($project->pages as $page)
                <flux:modal name="delete-page-{{ $page->id }}" class="md:w-[28rem]">
                    <form action="{{ route('accessibility-pages.destroy', [$project, $page]) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('DELETE')

                        <div>
                            <flux:heading level="2">{{ __('Delete this page or service?') }}</flux:heading>
                            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                        </div>

                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                        </div>
                    </form>
                </flux:modal>
            @endforeach
        </div>

        <!-- Issues Section -->
        <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 mb-8">
            <div class="flex items-center justify-between mb-4">
            <flux:heading level="2">{{ __('Issues') }}</flux:heading>
                <flux:button href="{{ route('accessibility-issues.create', $project) }}" variant="primary" icon="plus" size="sm">{{ __('Report Issue') }}</flux:button>
            </div>

            @if ($project->issues->isEmpty())
                <div class="py-4">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No issues reported yet.') }}</flux:text>
                </div>
            @else
                <div class="mb-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <div class="grid w-full grid-cols-1 gap-2 sm:flex sm:flex-wrap sm:items-center">
                        <flux:input id="issue-search" placeholder="{{ __('Search issues...') }}" class="w-full sm:max-w-sm" />
                        <flux:select id="issue-severity-filter" class="w-full sm:w-40">
                            <option value="">{{ __('All severities') }}</option>
                            <option value="critical">{{ __('Critical') }}</option>
                            <option value="major">{{ __('Major') }}</option>
                            <option value="moderate">{{ __('Moderate') }}</option>
                            <option value="minor">{{ __('Minor') }}</option>
                        </flux:select>
                        <flux:select id="issue-status-filter" class="w-full sm:w-40">
                            <option value="">{{ __('All statuses') }}</option>
                            <option value="open">{{ __('Open') }}</option>
                            <option value="resolved">{{ __('Resolved') }}</option>
                            <option value="wont_fix">{{ __('Won\'t Fix') }}</option>
                        </flux:select>
                    </div>
                </div>

                <div class="max-w-full overflow-x-auto">
                <flux:table class="w-full table-auto!">
                    <flux:table.columns>
                        <flux:table.column class="hidden lg:table-cell">{{ __('ID') }}</flux:table.column>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Severity') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="hidden lg:table-cell">{{ __('Page/Component') }}</flux:table.column>
                        <flux:table.column class="hidden lg:table-cell">{{ __('WCAG') }}</flux:table.column>
                        <flux:table.column class="hidden lg:table-cell">{{ __('Created') }}</flux:table.column>
                        <flux:table.column>{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                    @foreach ($project->issues as $issue)
                        <flux:table.row wire:key="project-issue-{{ $issue->id }}" data-issue-row data-title="{{ Str::lower(e($issue->title)) }}" data-desc="{{ Str::lower(e(strip_tags(($issue->summary ?? '').' '.($issue->description ?? '')))) }}" data-page="{{ $issue->page ? Str::lower(e($issue->page->name)) : '' }}" data-wcag="{{ $issue->wcagCriteria->pluck('number')->join(' ') }}" data-severity="{{ $issue->severity }}" data-status="{{ $issue->status }}">
                            <flux:table.cell class="hidden font-mono text-xs lg:table-cell">{{ Str::substr($issue->id, 0, 8) }}</flux:table.cell>

                            <flux:table.cell class="min-w-32 whitespace-normal">
                                <flux:link href="{{ route('accessibility-issues.show', [$project, $issue]) }}" wire:navigate class="font-medium">
                                    {{ $issue->title }}
                                </flux:link>
                                @if ($issue->page?->scope === 'out_of_scope')
                                    <flux:text class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                        {{ __('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.') }}
                                    </flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">{{ __(Str::title($issue->severity)) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <flux:badge color="zinc">{{ __(Str::title($issue->status)) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                @if ($issue->page)
                                    <flux:link href="{{ route('accessibility-pages.edit', [$project, $issue->page]) }}" class="text-sm">{{ $issue->page->name }}</flux:link>
                                @elseif($issue->component_area)
                                    <flux:text class="text-sm">{{ $issue->component_area }}</flux:text>
                                @else
                                    <flux:text class="text-sm text-zinc-500">{{ __('Not specific to a page') }}</flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                <div class="flex items-center gap-1">
                                    @foreach ($issue->wcagCriteria->take(3) as $criterion)
                                        <flux:badge size="sm" color="zinc">{{ $criterion->standard }} · {{ $criterion->number }}</flux:badge>
                                        @if ($criterion->isOutsideProjectTargetVersion($project->target_wcag_version))
                                            <flux:badge size="sm" color="amber">{{ __('Nice to have') }}</flux:badge>
                                        @endif
                                    @endforeach
                                    @if ($issue->wcagCriteria->count() > 3)
                                        <flux:badge size="sm" color="zinc">+{{ $issue->wcagCriteria->count() - 3 }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="hidden whitespace-nowrap lg:table-cell">{{ $issue->created_at->translatedFormat('j M Y') }}</flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:dropdown align="end">
                                        <flux:button type="button" variant="subtle" size="sm" icon="ellipsis-vertical" aria-label="{{ __('Actions') }}: {{ $issue->title }}" />
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('accessibility-issues.show', [$project, $issue]) }}" icon="eye" wire:navigate>{{ __('View') }}</flux:menu.item>
                                            <flux:menu.item href="{{ route('accessibility-issues.edit', [$project, $issue]) }}" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                            <flux:modal.trigger name="resolve-project-issue-{{ $issue->id }}">
                                                <flux:menu.item icon="check-circle">{{ __('Change resolution') }}</flux:menu.item>
                                            </flux:modal.trigger>
                                            <flux:menu.separator />
                                            <flux:modal.trigger name="delete-issue-{{ $issue->id }}">
                                                <flux:menu.item as="button" type="button" icon="trash" variant="danger">{{ __('Delete') }}</flux:menu.item>
                                            </flux:modal.trigger>
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                    </flux:table.rows>
                </flux:table>
                </div>
            @endif

            @foreach ($project->issues as $issue)
                <flux:modal name="resolve-project-issue-{{ $issue->id }}" class="md:w-[32rem]">
                    <form action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="resolution_issue_id" value="{{ $issue->id }}">

                        <div>
                            <flux:heading level="2">{{ __('Change resolution') }}</flux:heading>
                            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Choose a resolution status and add context for the audit trail.') }}</flux:text>
                        </div>

                        <flux:field>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <flux:select name="resolution_status">
                                <option value="open" @selected((old('resolution_issue_id') === $issue->id ? old('resolution_status') : $issue->resolution_status) === 'open')>{{ __('Open') }}</option>
                                <option value="fixed" @selected((old('resolution_issue_id') === $issue->id ? old('resolution_status') : $issue->resolution_status) === 'fixed')>{{ __('Mark as fixed') }}</option>
                                <option value="wontfix" @selected((old('resolution_issue_id') === $issue->id ? old('resolution_status') : $issue->resolution_status) === 'wontfix')>{{ __('Mark as wontfix') }}</option>
                            </flux:select>
                            @if (old('resolution_issue_id') === $issue->id)
                                <flux:error name="resolution_status" />
                            @endif
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Resolution Notes') }}</flux:label>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('A resolution note is required when marking an issue fixed or not planned for fixing.') }}</flux:text>
                            <flux:textarea name="resolution_notes" rows="4">{{ old('resolution_issue_id') === $issue->id ? old('resolution_notes', $issue->resolution_notes) : $issue->resolution_notes }}</flux:textarea>
                            @if (old('resolution_issue_id') === $issue->id)
                                <flux:error name="resolution_notes" />
                            @endif
                        </flux:field>

                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                        </div>
                    </form>
                </flux:modal>
            @endforeach

            @foreach ($project->issues as $issue)
                <flux:modal name="delete-issue-{{ $issue->id }}" class="md:w-[28rem]">
                    <form action="{{ route('accessibility-issues.destroy', [$project, $issue]) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('DELETE')

                        <div>
                            <flux:heading level="2">{{ __('Delete this issue?') }}</flux:heading>
                            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                        </div>

                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                        </div>
                    </form>
                </flux:modal>
            @endforeach
        </div>

        <!-- Testing Methodology -->
        @livewire('project-methodologies', ['project' => $project])

    </div>
</div>

<!-- Legacy links remain revocable; they never expose the changing project view. -->
@if ($project->shareLinks->whereNull('report_id')->isNotEmpty())
<flux:modal name="share-modal" class="md:w-96">
    <div class="space-y-6">
        <flux:heading level="2">{{ __('Legacy links') }}</flux:heading>
        <flux:callout variant="warning">{{ __('Old project links no longer show live data. Create a new customer link from a saved report version.') }}</flux:callout>
        @foreach ($project->shareLinks->whereNull('report_id') as $link)
            <div class="flex items-center justify-between gap-2">
                <flux:text class="break-all">{{ route('projects.shared', $link->token) }}</flux:text>
                <flux:modal.trigger name="delete-share-link-{{ $link->id }}">
                    <flux:button type="button" icon="trash" size="sm" variant="subtle" aria-label="{{ __('Revoke share link') }}" />
                </flux:modal.trigger>
            </div>
        @endforeach
    </div>
</flux:modal>
@endif

@foreach ($project->shareLinks->whereNull('report_id') as $link)
        <flux:modal name="delete-share-link-{{ $link->id }}" class="md:w-[28rem]">
            <form action="{{ route('project-share-links.destroy', [$project, $link]) }}" method="POST" class="space-y-6">
                @csrf
                @method('DELETE')

                <div>
                    <flux:heading level="2">{{ __('Revoke this share link?') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('Anyone using this link will lose access.') }}</flux:text>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger">{{ __('Revoke link') }}</flux:button>
                </div>
            </form>
        </flux:modal>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('issue-search');
    if (!input) return;

    const severity = document.getElementById('issue-severity-filter');
    const status = document.getElementById('issue-status-filter');
    const rows = Array.from(document.querySelectorAll('[data-issue-row]'));

    const filterRows = () => {
        const q = input.value.trim().toLowerCase();
        const selectedSeverity = severity?.value ?? '';
        const selectedStatus = status?.value ?? '';

        rows.forEach((row) => {
            const title = row.getAttribute('data-title') || '';
            const desc = row.getAttribute('data-desc') || '';
            const page = row.getAttribute('data-page') || '';
            const wcag = row.getAttribute('data-wcag') || '';
            const rowSeverity = row.getAttribute('data-severity') || '';
            const rowStatus = row.getAttribute('data-status') || '';
            const matchesQuery = q === '' || title.includes(q) || desc.includes(q) || page.includes(q) || wcag.includes(q);
            const matchesSeverity = selectedSeverity === '' || rowSeverity === selectedSeverity;
            const matchesStatus = selectedStatus === '' || rowStatus === selectedStatus;
            const match = matchesQuery && matchesSeverity && matchesStatus;

            row.classList.toggle('hidden', !match);
        });
    };

    input.addEventListener('input', filterRows);
    severity?.addEventListener('change', filterRows);
    status?.addEventListener('change', filterRows);

});
</script>

</x-app-layout>