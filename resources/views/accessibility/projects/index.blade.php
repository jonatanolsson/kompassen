<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <flux:heading level="1">Accessibility Projects</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">Manage your website accessibility audits</flux:text>
                </div>
                <a href="{{ route('accessibility-projects.create') }}">
                    <flux:button variant="primary" icon="plus">New Project</flux:button>
                </a>
            </div>

            @if ($projects->isEmpty())
                <flux:card class="p-12 text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">No projects yet. Create one to get started.</flux:text>
                </flux:card>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($projects as $project)
                        <flux:card class="hover:shadow-lg transition-shadow">
                            <a href="{{ route('accessibility-projects.show', $project) }}" class="block">
                                <flux:heading level="3" class="mb-2">{{ $project->name }}</flux:heading>
                                @if ($project->description)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">{{ Str::limit($project->description, 100) }}</flux:text>
                                @endif
                                
                                <div class="flex items-center gap-4">
                                    <flux:badge color="zinc">{{ $project->target_wcag_level }}</flux:badge>
                                    <flux:badge :color="$project->status === 'completed' ? 'green' : 'amber'">{{ Str::title($project->status) }}</flux:badge>
                                </div>
                            </a>
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
