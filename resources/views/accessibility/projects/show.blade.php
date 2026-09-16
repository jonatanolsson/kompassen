<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading level="1">{{ $project->name }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">WCAG {{ $project->target_wcag_level }} • {{ __(Str::title(str_replace(['_', '-'], ' ', $project->status))) }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('accessibility-projects.preview', $project) }}" target="_blank" icon="eye" variant="primary">
                    {{ __('Preview') }}
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
                        <flux:modal.trigger name="share-modal">
                            <flux:menu.item icon="link">{{ __('Share') }}</flux:menu.item>
                        </flux:modal.trigger>
                        <flux:menu.item href="{{ route('accessibility-projects.edit', $project) }}" icon="pencil" wire:navigate>
                            {{ __('Edit') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                        <form action="{{ route('accessibility-projects.destroy', $project) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                            @csrf
                            @method('DELETE')
                            <flux:menu.item as="button" type="submit" variant="danger" icon="trash">
                                {{ __('Delete') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        @if ($project->description)
            <section class="mb-8">
                <flux:heading level="2" class="mb-3">{{ __('Description') }}</flux:heading>
                <x-user-content :content="$project->description" />
            </section>
        @endif

        <flux:separator class="my-8" />

        <!-- Pages Section -->
        <div class="mb-8">
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
                                <form action="{{ route('accessibility-pages.destroy', [$project, $page]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                <flux:button type="submit" variant="ghost" size="sm" icon="trash" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</flux:button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <flux:separator class="my-8" />

        <!-- Issues Section -->
        <div>
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

                            <flux:table.cell>{{ $issue->created_at->format('Y-m-d') }}</flux:table.cell>

                            <flux:table.cell class="flex gap-2 items-center">
                                <flux:dropdown align="end">
                                    <flux:button variant="subtle" size="sm" icon="ellipsis-vertical" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('accessibility-issues.show', [$project, $issue]) }}" icon="eye" wire:navigate>{{ __('View') }}</flux:menu.item>
                                        <flux:menu.item href="{{ route('accessibility-issues.edit', [$project, $issue]) }}" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.item as="button" icon="check" onclick="document.getElementById('resolve-fixed-{{ $issue->id }}').submit()">{{ __('Mark as fixed') }}</flux:menu.item>
                                        <flux:menu.item as="button" icon="x-mark" class="text-red-600" onclick="document.getElementById('resolve-wontfix-{{ $issue->id }}').submit()">{{ __('Mark as wontfix') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item as="button" icon="trash" variant="danger" onclick="if(confirm('{{ __('Are you sure?') }}')){ document.getElementById('destroy-{{ $issue->id }}').submit(); }">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>

                                <form id="resolve-fixed-{{ $issue->id }}" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="hidden">@csrf @method('PATCH')<input type="hidden" name="resolution_status" value="fixed" /><input type="hidden" name="resolution_notes" value="" /></form>
                                <form id="resolve-wontfix-{{ $issue->id }}" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="hidden">@csrf @method('PATCH')<input type="hidden" name="resolution_status" value="wontfix" /><input type="hidden" name="resolution_notes" value="" /></form>
                                <form id="destroy-{{ $issue->id }}" action="{{ route('accessibility-issues.destroy', [$project, $issue]) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
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
        </div>

        <flux:separator class="my-8" />

        <!-- Testing Methodology -->
        @livewire('project-methodologies', ['project' => $project])

    </div>
</div>

<!-- Share Modal -->
<flux:modal name="share-modal" class="md:w-96">
    <div class="space-y-6">
        <flux:heading level="2">{{ __('Share Project') }}</flux:heading>

        <flux:separator />

        <!-- Create Share Link Form -->
        <form action="{{ route('project-share-links.store', $project) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Expiration Date (Optional)') }}</flux:label>
                    <flux:input 
                        type="date" 
                        name="expires_at"
                        :min="today()"
                    />
                    <flux:error name="expires_at" />
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">{{ __('Create Share Link') }}</flux:button>
            </div>
        </form>

        <flux:separator />

        <!-- Existing Share Links -->
        <div>
            <flux:heading level="3" class="mb-4">{{ __('Active Share Links') }}</flux:heading>
            @if ($project->shareLinks->count() > 0)
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach ($project->shareLinks as $link)
                        @if (!$link->isExpired())
                            <div class="bg-zinc-50 dark:bg-zinc-800 p-3 rounded-lg flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white break-all">
                                        {{ route('projects.shared', $link->token) }}
                                    </div>
                                    @if ($link->expires_at)
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                        {{ __('Expires') }}: {{ $link->expires_at->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                </div>
            <div class="flex gap-2 ml-2">
                                    <flux:button 
                                        href="{{ route('projects.shared', $link->token) }}"
                                        target="_blank"
                                        icon="eye" 
                                        size="sm"
                                        variant="subtle"
                                    />
                                    <flux:button 
                                        x-on:click="navigator.clipboard.writeText('{{ route('projects.shared', $link->token) }}').then(() => $flux.toast({ text: '{{ __('Copied!') }}', variant: 'success' }))" 
                                        icon="document-duplicate" 
                                        size="sm"
                                        variant="subtle"
                                    />
                                    <form action="{{ route('project-share-links.destroy', [$project, $link]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" icon="trash" size="sm" variant="subtle" />
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('No active share links yet. Create one above.') }}
                </flux:text>
            @endif
        </div>
    </div>
</flux:modal>

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