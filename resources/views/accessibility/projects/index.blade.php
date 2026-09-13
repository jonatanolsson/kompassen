<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <flux:heading level="1">{{ __('Accessibility Projects') }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                        {{ __('Manage your website accessibility audits') }}
                    </flux:text>
                </div>
                <a href="{{ route('accessibility-projects.create') }}">
                    <flux:button variant="primary" icon="plus">{{ __('New Project') }}</flux:button>
                </a>
            </div>

            <form method="GET" action="{{ route('accessibility-projects.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
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
                <flux:card class="p-12 text-center">
                    <flux:icon name="folder-open" class="mx-auto mb-4 size-10 text-zinc-400" />
                    <flux:heading level="2" size="sm">
                        {{ $search !== '' ? __('No projects match your search.') : __('No projects yet. Create one to get started.') }}
                    </flux:heading>
                    @if ($search === '')
                        <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                            {{ __('Create your first project to start an accessibility audit.') }}
                        </flux:text>
                    @endif
                </flux:card>
            @else
                <div class="overflow-x-auto px-1 py-2 sm:px-2">
                    <flux:table class="min-w-[1100px]">
                            <flux:table.columns>
                                <flux:table.cell class="min-w-56 whitespace-nowrap">{{ __('Project') }}</flux:table.cell>
                                <flux:table.cell class="min-w-48 whitespace-nowrap">{{ __('Domain') }}</flux:table.cell>
                                <flux:table.cell class="min-w-20 whitespace-nowrap">{{ __('Pages & Services') }}</flux:table.cell>
                                <flux:table.cell class="min-w-20 whitespace-nowrap">{{ __('Issues') }}</flux:table.cell>
                                <flux:table.cell class="min-w-28 whitespace-nowrap">{{ __('WCAG Level') }}</flux:table.cell>
                                <flux:table.cell class="min-w-36 whitespace-nowrap">{{ __('Status') }}</flux:table.cell>
                                <flux:table.cell class="min-w-32 whitespace-nowrap">{{ __('Updated') }}</flux:table.cell>
                                <flux:table.cell class="min-w-32 whitespace-nowrap">{{ __('Actions') }}</flux:table.cell>
                            </flux:table.columns>

                            @foreach ($projects as $project)
                                <flux:table.row class="align-middle">
                                    <flux:table.cell class="min-w-56 py-4">
                                        <div class="min-w-48">
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
                                    <flux:table.cell class="min-w-48 py-4">
                                        @if ($project->domains !== [])
                                            <span class="whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                                                {{ implode(', ', $project->domains) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">
                                                {{ __('No domain added') }}
                                            </span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="min-w-20 py-4">{{ $project->pages_count }}</flux:table.cell>
                                    <flux:table.cell class="min-w-20 py-4">{{ $project->issues_count }}</flux:table.cell>
                                    <flux:table.cell class="min-w-28 py-4">
                                        <flux:badge color="zinc">{{ $project->target_wcag_level }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="min-w-36 py-4">
                                        <flux:badge :color="$project->status === 'completed' ? 'green' : ($project->status === 'in_progress' ? 'blue' : 'amber')">
                                            {{ __(Str::title(str_replace(['_', '-'], ' ', $project->status))) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="min-w-32 whitespace-nowrap py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $project->updated_at->format('Y-m-d') }}
                                    </flux:table.cell>
                                    <flux:table.cell class="min-w-32 py-4">
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
            @endif
        </div>
    </div>
</x-app-layout>
