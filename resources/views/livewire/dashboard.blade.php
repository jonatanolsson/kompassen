<div class="product-page space-y-8">
    <div class="product-page-header">
        <div>
            <flux:heading level="1">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</flux:subheading>
        </div>
        <flux:button href="{{ route('accessibility-projects.create') }}" variant="primary" icon="plus">
            {{ __('Create Project') }}
        </flux:button>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('accessibility-projects.index') }}" class="product-stat">
            <flux:text>{{ __('Projects') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['projects'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Projects you can access') }}</flux:text>
        </a>
        <a href="{{ route('accessibility-projects.index') }}" class="product-stat">
            <flux:text>{{ __('Open Issues') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['open_issues'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Issues needing attention') }}</flux:text>
        </a>
        <div class="product-stat">
            <flux:text>{{ __('Total Issues') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['issues'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Across your projects') }}</flux:text>
        </div>
        <div class="product-stat">
            <flux:text>{{ __('Reports') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['reports'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Generated reports') }}</flux:text>
        </div>
    </div>

    <section class="product-section">
        <div class="product-section-heading">
            <div>
                <flux:heading level="2">{{ __('Recent Projects') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Your latest project activity') }}</flux:text>
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
                <flux:button href="{{ route('accessibility-projects.create') }}" variant="primary" class="mt-6" icon="plus">
                    {{ __('Create Project') }}
                </flux:button>
            </div>
        @else
            <div class="mt-6 grid gap-3 lg:grid-cols-2">
                @foreach ($projects as $project)
                    <div wire:key="dashboard-project-{{ $project->id }}" class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <flux:heading level="3" class="truncate">{{ $project->name }}</flux:heading>
                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    {{ __('Updated') }} {{ $project->updated_at->translatedFormat('j M Y') }}
                                </flux:text>
                            </div>
                            <flux:badge :color="$project->status === 'completed' ? 'green' : ($project->status === 'in_progress' ? 'amber' : 'zinc')">
                                {{ __(Str::title(str_replace('_', ' ', $project->status))) }}
                            </flux:badge>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3">
                            <div>
                                <flux:text class="text-xs text-zinc-500">{{ __('Pages & Services') }}</flux:text>
                                <flux:text class="mt-1 font-medium">{{ $project->pages_count }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs text-zinc-500">{{ __('Open Issues') }}</flux:text>
                                <flux:text class="mt-1 font-medium">{{ $project->open_issues_count }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs text-zinc-500">{{ __('Reports') }}</flux:text>
                                <flux:text class="mt-1 font-medium">{{ $project->reports_count }}</flux:text>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <flux:button href="{{ route('accessibility-projects.show', $project) }}" variant="primary" size="sm" icon="eye">
                                {{ __('View Project') }}
                            </flux:button>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal">
                                    {{ __('Actions') }}
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
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section>
        <div>
            <flux:heading level="2">{{ __('Quick Actions') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Start a new audit or find existing project material.') }}</flux:text>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <flux:button href="{{ route('accessibility-projects.create') }}" variant="outline" icon="plus">
                {{ __('Create Project') }}
            </flux:button>
            <flux:button href="{{ route('accessibility-projects.index') }}" variant="outline" icon="document-text">
                {{ __('Browse Projects') }}
            </flux:button>
            <flux:button href="{{ route('wcag.knowledge-base') }}" variant="outline" icon="book-open">
                {{ __('Knowledge Base') }}
            </flux:button>
        </div>
    </section>
</div>
