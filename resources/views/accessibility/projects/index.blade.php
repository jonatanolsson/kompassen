<x-app-layout>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8 space-y-6">
            <div class="flex flex-col gap-4 border-b border-zinc-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-800">
                <div>
                    <flux:heading level="1">{{ __('Accessibility Projects') }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ __('Manage your website accessibility audits') }}
                    </flux:text>
                </div>
                <flux:button href="{{ route('accessibility-projects.create') }}" variant="primary" icon="plus">{{ __('New Project') }}</flux:button>
            </div>

            <form method="GET" action="{{ route('accessibility-projects.index') }}" class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 flex flex-col gap-4 sm:flex-row sm:items-end">
                <flux:field class="flex-1 sm:max-w-xl">
                    <flux:label>{{ __('Search projects') }}</flux:label>
                    <flux:input
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ __('Search by project name, domain or description...') }}"
                        icon="magnifying-glass"
                    />
                </flux:field>
                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ __('Search') }}</flux:button>
                    @if ($search !== '')
                        <flux:button href="{{ route('accessibility-projects.index') }}" variant="ghost">
                            {{ __('Clear') }}
                        </flux:button>
                    @endif
                </div>
            </form>

            @if ($projects->isEmpty())
                <div class="rounded-2xl border border-zinc-200/80 bg-white/80 shadow-sm shadow-zinc-950/5 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 p-12 text-center">
                    <flux:icon name="folder-open" class="mx-auto mb-4 size-10 text-zinc-400" />
                    <flux:heading level="2" size="sm">
                        {{ $search !== '' ? __('No projects match your search.') : __('No projects yet. Create one to get started.') }}
                    </flux:heading>
                    @if ($search === '')
                        <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                            {{ __('Create your first project to start an accessibility audit.') }}
                        </flux:text>
                    @endif
                </div>
            @else
                <div
                    x-data="{
                        open: false,
                        columns: {
                            pages: false,
                            issues: false,
                            wcag: false,
                            status: false,
                        },
                    }"
                    class="space-y-3"
                >
                    <div class="flex items-center justify-between gap-4">
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $projects->count() }} {{ __('Projects') }}
                        </flux:text>
                        <div class="relative">
                            <flux:button
                                type="button"
                                variant="outline"
                                size="sm"
                                x-on:click="open = !open"
                                x-bind:aria-expanded="open"
                                aria-controls="project-columns-menu"
                            >
                                {{ __('Columns') }}
                            </flux:button>

                            <div
                                id="project-columns-menu"
                                x-cloak
                                x-show="open"
                                x-transition
                                x-on:click.outside="open = false"
                                x-on:keydown.escape.window="open = false"
                                class="absolute right-0 z-20 mt-2 w-64 rounded-xl border border-zinc-200 bg-white p-4 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <flux:heading size="sm">{{ __('Show columns') }}</flux:heading>
                                <div class="mt-3 space-y-3">
                                    <flux:checkbox x-model="columns.pages" label="{{ __('Pages & Services') }}" />
                                    <flux:checkbox x-model="columns.issues" label="{{ __('Issues') }}" />
                                    <flux:checkbox x-model="columns.wcag" label="{{ __('WCAG Level') }}" />
                                    <flux:checkbox x-model="columns.status" label="{{ __('Status') }}" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200/80 bg-white/80 shadow-sm shadow-zinc-950/5 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 w-full overflow-x-auto p-1 sm:p-2">
                    <flux:table class="w-full table-auto!">
                            <flux:table.columns>
                                <flux:table.column>{{ __('Project') }}</flux:table.column>
                                <flux:table.column>{{ __('Domain') }}</flux:table.column>
                                <flux:table.column x-cloak x-show="columns.pages" class="whitespace-nowrap">{{ __('Pages & Services') }}</flux:table.column>
                                <flux:table.column x-cloak x-show="columns.issues" class="whitespace-nowrap">{{ __('Issues') }}</flux:table.column>
                                <flux:table.column x-cloak x-show="columns.wcag" class="whitespace-nowrap">{{ __('WCAG Level') }}</flux:table.column>
                                <flux:table.column x-cloak x-show="columns.status" class="whitespace-nowrap">{{ __('Status') }}</flux:table.column>
                                <flux:table.column class="whitespace-nowrap">{{ __('Updated') }}</flux:table.column>
                                <flux:table.column class="whitespace-nowrap">{{ __('Actions') }}</flux:table.column>
                            </flux:table.columns>

                            @foreach ($projects as $project)
                                <flux:table.row class="align-middle">
                                    <flux:table.cell class="py-4">
                                        <div>
                                            <flux:link href="{{ route('accessibility-projects.show', $project) }}" class="font-medium">
                                                {{ $project->name }}
                                            </flux:link>
                                            @if ($project->description)
                                                <flux:text class="mt-1 max-w-xs truncate text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ Str::of($project->description)->replace(['</p>', '</li>', '</div>', '<br>', '<br/>', '<br />'], ' ')->stripTags()->squish()->limit(90) }}
                                                </flux:text>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-normal break-all py-4">
                                        @if ($project->domains !== [])
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                                {{ implode(', ', $project->domains) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">
                                                {{ __('No domain added') }}
                                            </span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell x-cloak x-show="columns.pages" class="whitespace-nowrap py-4">{{ $project->pages_count }}</flux:table.cell>
                                    <flux:table.cell x-cloak x-show="columns.issues" class="whitespace-nowrap py-4">{{ $project->issues_count }}</flux:table.cell>
                                    <flux:table.cell x-cloak x-show="columns.wcag" class="whitespace-nowrap py-4">
                                        <flux:badge color="zinc">{{ $project->target_wcag_level }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell x-cloak x-show="columns.status" class="whitespace-nowrap py-4">
                                        <flux:badge :color="$project->status === 'completed' ? 'green' : ($project->status === 'in_progress' ? 'blue' : 'amber')">
                                            {{ __(Str::title(str_replace(['_', '-'], ' ', $project->status))) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $project->updated_at->translatedFormat('j M Y') }}
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap py-4">
                                        <div class="flex items-center gap-1">
                                            <flux:button
                                                href="{{ route('accessibility-projects.show', $project) }}"
                                                variant="subtle"
                                                size="sm"
                                                icon="eye"
                                            >
                                                {{ __('View') }}
                                            </flux:button>
                                            <flux:button
                                                href="{{ route('accessibility-projects.edit', $project) }}"
                                                variant="subtle"
                                                size="sm"
                                                icon="pencil"
                                            >
                                                {{ __('Edit') }}
                                            </flux:button>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                    </flux:table>
                    </div>
                </div>
            @endif
    </div>
</x-app-layout>
