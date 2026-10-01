<x-app-layout>
    <div class="product-page">
    <div class="w-full">
        <div class="product-page-header">
            <div>
                <flux:heading level="1">{{ $project->name }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">WCAG {{ $project->target_wcag_level }} • {{ __(Str::title(str_replace(['_', '-'], ' ', $project->status))) }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
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
            <section class="product-section mb-8">
                <flux:heading level="2" class="mb-3">{{ __('Description') }}</flux:heading>
                <x-user-content :content="$project->description" />
            </section>
        @endif

        <flux:separator class="my-8" />

        <!-- Pages Section -->
        <div class="product-section mb-8">
            <div class="flex items-center justify-between mb-4">
                <flux:heading level="2">{{ __('Pages & Services') }}</flux:heading>
                <flux:button href="{{ route('accessibility-pages.create', $project) }}" variant="primary" icon="plus" size="sm">{{ __('Add Page or Service') }}</flux:button>
            </div>

            @if ($project->pages->isEmpty())
                <div class="py-4">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No pages or services added yet.') }}</flux:text>
                </div>
            @else
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($project->pages as $page)
                        @php
                            $resourceType = $page->resource_type ?? 'page';
                            $accessContext = $page->access_context ?? 'not_applicable';
                        @endphp
                        <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                            <div>
                                <div class="flex items-center gap-2">
                                    <flux:heading level="4">{{ $page->name }}</flux:heading>
                                    <flux:badge size="sm" color="{{ $resourceType === 'service' ? 'blue' : 'zinc' }}">
                                        {{ __($resourceType === 'service' ? 'Service' : 'Page') }}
                                    </flux:badge>
                                    <flux:badge size="sm" :color="$page->scope === 'in_scope' ? 'green' : 'amber'">
                                        {{ __($page->scope === 'in_scope' ? 'In Scope' : 'Out of Scope') }}
                                    </flux:badge>
                                    @if ($accessContext !== 'not_applicable')
                                        <flux:badge size="sm" color="purple">
                                            {{ __($accessContext === 'authenticated' ? 'Authenticated' : ($accessContext === 'mixed' ? 'Public and authenticated' : 'Public')) }}
                                        </flux:badge>
                                    @endif
                                </div>
                                @if ($page->url)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $page->url }}</flux:text>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('accessibility-pages.edit', [$project, $page]) }}">
                                <flux:button variant="ghost" size="sm" icon="pencil">{{ __('Edit') }}</flux:button>
                                </a>
                                <flux:modal.trigger name="delete-page-{{ $page->id }}">
                                    <flux:button type="button" variant="ghost" size="sm" icon="trash" aria-label="{{ __('Delete') }}" />
                                </flux:modal.trigger>
                            </div>
                        </div>
                    @endforeach
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

        <flux:separator class="my-8" />

        <!-- Issues Section -->
        <div class="product-section">
            <div class="flex items-center justify-between mb-4">
            <flux:heading level="2">{{ __('Issues') }}</flux:heading>
                <flux:button href="{{ route('accessibility-issues.create', $project) }}" variant="primary" icon="plus" size="sm">{{ __('Report Issue') }}</flux:button>
            </div>

            @if ($project->issues->isEmpty())
                <div class="py-4">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No issues reported yet.') }}</flux:text>
                </div>
            @else
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex gap-2 items-center">
                        <flux:input id="issue-search" placeholder="{{ __('Search issues...') }}" class="max-w-sm" />
                        <flux:select id="issue-severity-filter" class="w-40">
                            <option value="">{{ __('All severities') }}</option>
                            <option value="critical">{{ __('Critical') }}</option>
                            <option value="major">{{ __('Major') }}</option>
                            <option value="moderate">{{ __('Moderate') }}</option>
                            <option value="minor">{{ __('Minor') }}</option>
                        </flux:select>
                        <flux:select id="issue-status-filter" class="w-40">
                            <option value="">{{ __('All statuses') }}</option>
                            <option value="open">{{ __('Open') }}</option>
                            <option value="resolved">{{ __('Resolved') }}</option>
                            <option value="wont_fix">{{ __('Won\'t Fix') }}</option>
                        </flux:select>
                    </div>
                </div>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column></flux:table.column>
                        <flux:table.column>{{ __('ID') }}</flux:table.column>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Severity') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Page/Component') }}</flux:table.column>
                        <flux:table.column>{{ __('WCAG') }}</flux:table.column>
                        <flux:table.column>{{ __('Created') }}</flux:table.column>
                        <flux:table.column>{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                    @foreach ($project->issues as $issue)
                        <flux:table.row data-issue-row data-issue-id="{{ $issue->id }}" data-title="{{ Str::lower(e($issue->title)) }}" data-desc="{{ Str::lower(e(strip_tags($issue->description))) }}" data-page="{{ $issue->page ? Str::lower(e($issue->page->name)) : '' }}" data-wcag="{{ $issue->wcagCriteria->pluck('number')->join(' ') }}" data-severity="{{ $issue->severity }}" data-status="{{ $issue->status }}">
                            <flux:table.cell class="pr-2">
                                <button type="button" onclick="toggleIssueDetails('{{ $issue->id }}', this)" aria-expanded="false" aria-controls="issue-details-{{ $issue->id }}" class="p-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500/30" aria-label="{{ __('Toggle details') }}">
                                    <flux:icon icon="chevron-down" class="w-4 h-4 transition-transform duration-150" />
                                </button>
                            </flux:table.cell>

                            <flux:table.cell class="font-mono text-xs">{{ Str::substr($issue->id, 0, 8) }}</flux:table.cell>

                            <flux:table.cell>
                                <div class="font-medium">{{ $issue->title }}</div>
                                <div class="text-xs text-zinc-500 mt-1">{{ Str::limit(strip_tags($issue->description), 100) }}</div>
                                @if ($issue->page?->scope === 'out_of_scope')
                                    <flux:text class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                        {{ __('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.') }}
                                    </flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">{{ __(Str::title($issue->severity)) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge color="zinc">{{ __(Str::title($issue->status)) }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($issue->page)
                                    <flux:link href="{{ route('accessibility-pages.edit', [$project, $issue->page]) }}" class="text-sm">{{ $issue->page->name }}</flux:link>
                                @elseif($issue->component_area)
                                    <flux:text class="text-sm">{{ $issue->component_area }}</flux:text>
                                @else
                                    <flux:text class="text-sm text-zinc-500">{{ __('Not specific to a page') }}</flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    @foreach ($issue->wcagCriteria->take(3) as $criterion)
                                        <flux:badge size="sm" color="zinc">{{ $criterion->number }}</flux:badge>
                                    @endforeach
                                    @if ($issue->wcagCriteria->count() > 3)
                                        <flux:badge size="sm" color="zinc">+{{ $issue->wcagCriteria->count() - 3 }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>{{ $issue->created_at->translatedFormat('j M Y') }}</flux:table.cell>

                            <flux:table.cell class="flex gap-2 items-center">
                                <flux:dropdown align="end">
                                    <flux:button variant="subtle" size="sm" icon="ellipsis-vertical" />
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
                            </flux:table.cell>
                        </flux:table.row>

                        <flux:table.row id="issue-details-{{ $issue->id }}" data-issue-details="{{ $issue->id }}" role="region" aria-label="{{ __('Issue details') }}" class="bg-zinc-50 dark:bg-zinc-800 hidden">
                            <flux:table.cell colspan="9">
                                <div class="p-4">
                                    <x-user-content :content="$issue->description" class="mb-3 text-sm" />

                                    @if ($issue->wcagCriteria->isNotEmpty())
                                        <div class="mb-3">
                                            <div class="text-xs font-semibold mb-2">{{ __('WCAG Criteria') }}</div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($issue->wcagCriteria as $c)
                                                    <div class="flex items-center gap-2 p-2 bg-white dark:bg-zinc-700 rounded">
                                                        <flux:badge size="sm" color="zinc">{{ $c->number }}</flux:badge>
                                                        <div class="text-sm">{{ $c->name }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if (isset($issue->relatedResources) && $issue->relatedResources->isNotEmpty())
                                        <div>
                                            <div class="text-xs font-semibold mb-2">{{ __('Related resources') }}</div>
                                            <ul class="list-disc list-inside text-sm">
                                                @foreach ($issue->relatedResources as $res)
                                                    <li><a href="{{ $res->url }}" target="_blank" class="text-blue-600">{{ $res->title }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                        </tbody>
                    @endforeach
                    </flux:table.rows>
                </flux:table>
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

        <flux:separator class="my-8" />

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
            const id = row.getAttribute('data-issue-id');

            const matchesQuery = q === '' || title.includes(q) || desc.includes(q) || page.includes(q) || wcag.includes(q);
            const matchesSeverity = selectedSeverity === '' || rowSeverity === selectedSeverity;
            const matchesStatus = selectedStatus === '' || rowStatus === selectedStatus;
            const match = matchesQuery && matchesSeverity && matchesStatus;

            row.classList.toggle('hidden', !match);

            const details = document.getElementById('issue-details-' + id);
            details?.classList.toggle('hidden', !match);
        });
    };

    input.addEventListener('input', filterRows);
    severity?.addEventListener('change', filterRows);
    status?.addEventListener('change', filterRows);

    // Toggle function for expand/collapse
    window.toggleIssueDetails = function(id, btn) {
        const details = document.getElementById('issue-details-' + id);
        if (!details) return;
        const open = details.classList.toggle('hidden') ? false : true;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');

        // rotate icon inside button
        const icon = btn.querySelector('svg');
        if (icon) {
            if (open) icon.classList.add('transform', 'rotate-180');
            else icon.classList.remove('transform', 'rotate-180');
        }
    }
});
</script>

</x-app-layout>