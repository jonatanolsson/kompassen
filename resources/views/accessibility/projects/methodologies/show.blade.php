<x-app-layout>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="w-full">
            <x-ui.page-header :title="$methodology->name" :description="__('Project').': '.$project->name">
                <x-slot:actions>
                    <flux:button
                        href="{{ route('accessibility-projects.show', $project) }}#testing-methodology"
                        variant="ghost"
                        icon="arrow-left"
                    >
                        {{ __('Back') }}
                    </flux:button>
                </x-slot:actions>
            </x-ui.page-header>

            <x-ui.section :title="__('Details')" class="mb-6">
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Category') }}</dt>
                        <dd class="text-sm font-medium">{{ $methodology->category_label }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Version / Device / Notes') }}</dt>
                        <dd class="whitespace-pre-line text-sm font-medium">{{ $methodology->pivot->notes ?: __('Not specified') }}</dd>
                    </div>
                </dl>
            </x-ui.section>

            <x-ui.section :title="__('Description')">
                @if ($methodology->description)
                    <flux:text class="whitespace-pre-line">{{ $methodology->description }}</flux:text>
                @else
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No description provided.') }}</flux:text>
                @endif
            </x-ui.section>
        </div>
    </div>
</x-app-layout>
