<x-app-layout>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="w-full">
            <x-ui.page-header :title="$page->name" :description="__('Project').': '.$project->name">
                <x-slot:actions>
                    <flux:button
                        href="{{ route('accessibility-projects.show', $project) }}#pages-services"
                        variant="ghost"
                        icon="arrow-left"
                    >
                        {{ __('Back') }}
                    </flux:button>
                    @can('update', $project)
                        <flux:button
                            href="{{ route('accessibility-pages.edit', [$project, $page]) }}"
                            variant="outline"
                            icon="pencil"
                        >
                            {{ __('Edit') }}
                        </flux:button>
                    @endcan
                </x-slot:actions>
            </x-ui.page-header>

            @php
                $resourceType = $page->resource_type ?? 'page';
                $accessContext = $page->access_context ?? 'not_applicable';
            @endphp

            <x-ui.section :title="__('Details')" class="mb-6">
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</dt>
                        <dd class="text-sm font-medium">{{ __($resourceType === 'service' ? 'Service' : 'Page') }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Scope') }}</dt>
                        <dd class="text-sm font-medium">{{ __($page->scope === 'in_scope' ? 'In Scope' : 'Out of Scope') }}</dd>
                    </div>
                    @if ($page->scope === 'out_of_scope' && $page->scope_exclusion_reason)
                        <div class="space-y-1 sm:col-span-2">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Reason for exclusion') }}</dt>
                            <dd class="text-sm font-medium">{{ $page->scope_exclusion_reason }}</dd>
                        </div>
                    @endif
                    <div class="space-y-1">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('URL') }}</dt>
                        <dd class="break-all text-sm font-medium">{{ $page->url ?: __('Not specified') }}</dd>
                    </div>
                    @if ($accessContext !== 'not_applicable')
                        <div class="space-y-1">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Access context') }}</dt>
                            <dd class="text-sm font-medium">
                                {{ __($accessContext === 'authenticated' ? 'Authenticated' : ($accessContext === 'mixed' ? 'Public and authenticated' : 'Public')) }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-ui.section>

            <x-ui.section :title="__('Description')">
                @if ($page->description)
                    <x-user-content :content="$page->description" />
                @else
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No description provided.') }}</flux:text>
                @endif
            </x-ui.section>
        </div>
    </div>
</x-app-layout>
