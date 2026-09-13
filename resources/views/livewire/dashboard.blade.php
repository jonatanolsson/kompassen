<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading level="1">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading class="mt-2">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</flux:subheading>
        </div>
        <flux:button href="{{ route('accessibility-projects.create') }}" variant="primary" icon="plus">
            {{ __('Create Project') }}
        </flux:button>
    </div>

    <flux:separator />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <flux:text>{{ __('Projects') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['projects'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Projects you can access') }}</flux:text>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Open Issues') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['open_issues'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Issues needing attention') }}</flux:text>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Total Issues') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['issues'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Across your projects') }}</flux:text>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Reports') }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $summary['reports'] }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Generated reports') }}</flux:text>
        </flux:card>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading level="2">{{ __('Recent Projects') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Your latest project activity') }}</flux:text>
        </div>
        <flux:button href="{{ route('accessibility-projects.index') }}" variant="ghost" icon="arrow-right">
            {{ __('View All Projects') }}
        </flux:button>
    </div>

    @if ($projects->isEmpty())
        <flux:card class="p-8 text-center">
            <flux:icon name="folder-open" class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-600" />
            <flux:heading level="2" class="mt-4">{{ __('No projects yet') }}</flux:heading>
            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                {{ __('Create a project to start your accessibility audit.') }}
            </flux:text>
            <flux:button href="{{ route('accessibility-projects.create') }}" variant="primary" class="mt-6" icon="plus">
                {{ __('Create Project') }}
            </flux:button>
        </flux:card>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($projects as $project)
                <flux:card wire:key="dashboard-project-{{ $project->id }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <flux:heading level="3" class="truncate">{{ $project->name }}</flux:heading>
                            <flux:text class="mt-1 text-sm text-zinc-500">
                                {{ __('Updated') }} {{ $project->updated_at->diffForHumans() }}
                            </flux:text>
                        </div>
                        <flux:badge :color="$project->status === 'completed' ? 'green' : ($project->status === 'in_progress' ? 'amber' : 'zinc')">
                            {{ __(Str::title(str_replace('_', ' ', $project->status))) }}
                        </flux:badge>
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-3">
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

                    <flux:separator class="my-5" />

                    <div class="flex flex-wrap gap-2">
                        <flux:button href="{{ route('accessibility-projects.show', $project) }}" variant="primary" size="sm" icon="eye">
                            {{ __('View Project') }}
                        </flux:button>
                        <flux:button href="{{ route('accessibility-issues.create', $project) }}" variant="ghost" size="sm" icon="plus">
                            {{ __('Report Issue') }}
                        </flux:button>
                        <flux:button href="{{ route('accessibility-reports.create', $project) }}" variant="ghost" size="sm" icon="document-plus">
                            {{ __('Generate Report') }}
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    <flux:card>
        <flux:heading level="2">{{ __('Quick Actions') }}</flux:heading>
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
    </flux:card>
</div>
