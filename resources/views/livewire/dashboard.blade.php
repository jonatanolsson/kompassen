<div class="product-page space-y-8">
    <div class="product-page-header">
        <div>
            <flux:heading level="1">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Your latest project activity') }}</flux:subheading>
        </div>
        <flux:button href="{{ route('accessibility-projects.create') }}" variant="primary" icon="plus">
            {{ __('Create Project') }}
        </flux:button>
    </div>

    <div class="flex flex-wrap gap-x-6 gap-y-2 py-3 text-sm">
        <span><strong class="font-semibold text-zinc-950 dark:text-white">{{ $summary['open_issues'] }}</strong> {{ __('Open Issues') }}</span>
        <span><strong class="font-semibold text-zinc-950 dark:text-white">{{ $summary['projects'] }}</strong> {{ __('Projects') }}</span>
        <span><strong class="font-semibold text-zinc-950 dark:text-white">{{ $summary['reports'] }}</strong> {{ __('Reports') }}</span>
    </div>

    <section class="product-section">
        <div class="product-section-heading">
            <div>
                <flux:heading level="2">{{ __('Recent Projects') }}</flux:heading>
            </div>
            <flux:button href="{{ route('accessibility-projects.index') }}" variant="ghost" icon="arrow-right">
                {{ __('View All Projects') }}
            </flux:button>
        </div>

        @if ($projects->isEmpty())
            <div class="mt-6 rounded-xl border border-dashed border-zinc-300 px-6 py-10 text-center dark:border-zinc-700">
                <flux:icon name="folder-open" class="mx-auto size-10 text-zinc-400 dark:text-zinc-600" />
                <flux:heading level="2" class="mt-4">{{ __('No projects yet') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    {{ __('Create a project to start your accessibility audit.') }}
                </flux:text>
            </div>
        @else
            <ul class="mt-4 space-y-1">
                @foreach ($projects as $project)
                    <li wire:key="dashboard-project-{{ $project->id }}" class="grid gap-4 py-4 lg:grid-cols-[minmax(0,1fr)_auto_auto] lg:items-center">
                        <div class="min-w-0">
                            <flux:link href="{{ route('accessibility-projects.show', $project) }}" class="font-medium text-zinc-950 dark:text-white">
                                {{ $project->name }}
                            </flux:link>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <flux:text class="text-sm text-zinc-500">
                                    {{ __('Updated') }} {{ $project->updated_at->translatedFormat('j M Y') }}
                                </flux:text>
                                <flux:badge size="sm" :color="$project->status === 'completed' ? 'green' : ($project->status === 'in_progress' ? 'amber' : 'zinc')">
                                    {{ __(Str::title(str_replace('_', ' ', $project->status))) }}
                                </flux:badge>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <span>{{ $project->pages_count }} {{ __('Pages & Services') }}</span>
                            <span>{{ $project->open_issues_count }} {{ __('Open Issues') }}</span>
                            <span>{{ $project->reports_count }} {{ __('Reports') }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            @can('update', $project)
                                @if ($project->pages_count === 0)
                                    <flux:button href="{{ route('accessibility-pages.create', $project) }}" variant="subtle" size="sm">
                                        {{ __('Add Page or Service') }}
                                    </flux:button>
                                @elseif ($project->open_issues_count === 0)
                                    <flux:button href="{{ route('accessibility-issues.create', $project) }}" variant="subtle" size="sm">
                                        {{ __('Report Issue') }}
                                    </flux:button>
                                @endif
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="{{ __('Actions') }}">
                                </flux:button>
                                <flux:menu>
                                    <flux:menu.item href="{{ route('accessibility-issues.create', $project) }}" icon="plus">
                                        {{ __('Report Issue') }}
                                    </flux:menu.item>
                                    <flux:menu.item href="{{ route('accessibility-reports.create', $project) }}" icon="document-plus">
                                        {{ __('Generate Report') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                            @endcan
                            <flux:button href="{{ route('accessibility-projects.show', $project) }}" variant="ghost" size="sm" icon="arrow-right" aria-label="{{ __('View Project') }}" />
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
