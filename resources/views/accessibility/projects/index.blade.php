<x-app-layout> {{ __('') }} <div class="flex items-center justify-between mb-8">
                <div>
                    <flux:heading level="1">{{ __('Accessibility Projects') }}</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Manage your website accessibility audits') }}</flux:text> {{ __('') }} <flux:button variant="primary" icon="plus">{{ __('New Project') }}</flux:button> {{ __('') }} <flux:card class="p-12 text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No projects yet. Create one to get started.') }}</flux:text>
                </flux:card>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> {{ __('') }} <flux:heading level="3" class="mb-2">{{ $project->name }}</flux:heading>
                                @if ($project->description)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">{{ Str::limit($project->description, 100) }}</flux:text> {{ __('') }} </flux:badge>
                                    <flux:badge :color="$project->status === 'completed' ? 'green' : 'amber'">{{ Str::title($project-> {{ __('') }} </a>
                        </flux:card>
                    @endforeach
                </div> {{ __('') }} </x-app-layout>
