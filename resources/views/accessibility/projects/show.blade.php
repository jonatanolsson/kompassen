<x-app-layout> {{ __('') }} <div class="flex items-center justify-between mb-8"> {{ __('') }} </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">WCAG {{ $project->target_wcag_level }} • {{ Str::title($project-> {{ __('') }} <div class="flex gap-2">
                <flux:button href="{{ route('accessibility-projects.preview', $project) }}" target="_blank" icon="eye" variant="ghost">{{ __('Preview') }}</flux:button>
                <flux:modal.trigger name="share-modal">
                    <flux:button icon="link">{{ __('Share') }}</flux:button> {{ __('') }} <flux:button variant="outline" icon="pencil">{{ __('Edit') }}</flux:button> {{ __('') }} <flux:button type="submit" variant="danger" icon="trash" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</flux:button> {{ __('') }} </div>

        @if ($project->description)
            <flux:card class="mb-8 p-6">
                <flux:heading level="3" class="mb-2">{{ __('Description') }}</flux:heading>
                <flux:text>{{ $project-> {{ __('') }} <flux:separator class="my-8" /> {{ __('') }} <div class="flex items-center justify-between mb-4">
                <flux:heading level="2">{{ __('Pages') }}</flux:heading>
                <a href="{{ route('accessibility-pages.create', $project) }}">
                    <flux:button variant="primary" icon="plus" size="sm">{{ __('Add Page') }}</flux:button> {{ __('') }} <flux:card class="p-8 text-center">
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No pages added yet.') }}</flux:text>
                </flux:card>
            @else
                <div class="space-y-3">
                    @foreach ($project-> {{ __('') }} <flux:heading level="4">{{ $page->name }}</flux:heading>
                                @if ($page->url)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $page->url }}</flux:text> {{ __('') }} <a href="{{ route('accessibility-pages.edit', [$project, $page]) }}">
                                <flux:button variant="ghost" size="sm" icon="pencil">{{ __('Edit') }}</flux:button> {{ __('') }} <flux:button type="submit" variant="ghost" size="sm" icon="trash" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</flux:button> {{ __('') }} </flux:card>
                    @endforeach
                </div> {{ __('') }} <!-- Issues Section --> {{ __('') }} <flux:heading level="2">{{ __('Issues') }}</flux:heading>
                <a href="{{ route('accessibility-issues.create', $project) }}">
                <flux:button variant="primary" icon="plus" size="sm">{{ __('Report Issue') }}</flux:button> {{ __('') }} <flux:card class="p-8 text-center">
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No issues reported yet.') }}</flux:text>
                </flux:card>
            @else
                <div class="space-y-3">
                    @foreach ($project-> {{ __('') }} <div class="flex-1">
                                    <flux:heading level="4" class="mb-2">{{ $issue-> {{ __('') }} <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">
                                            {{ Str::title($issue-> {{ __('') }} </flux:badge> {{ __('') }} <div class="flex gap-2 items-center"> {{ __('') }} <flux:menu>
                                            <flux:menu.item href="{{ route('accessibility-issues.show', [$project, $issue]) }}" icon="eye" wire:navigate>
                                                {{ __('View') }}
                                            </flux:menu.item>

                                            <flux:menu.item href="{{ route('accessibility-issues.edit', [$project, $issue]) }}" icon="pencil" wire:navigate>
                                                {{ __('Edit') }}
                                            </flux:menu.item>

                                            <flux:menu.item as="button" icon="check" onclick="document.getElementById('resolve-fixed-{{ $issue->id }}').submit()">
                                                {{ __('Mark as fixed') }}
                                            </flux:menu.item>

                                            <flux:menu.item as="button" icon="x-mark" class="text-red-600" onclick="document.getElementById('resolve-wontfix-{{ $issue->id }}').submit()">
                                                {{ __('Mark as wontfix') }}
                                            </flux:menu.item>

                                            <flux:menu.separator />

                                            <flux:menu.item as="button" icon="trash" variant="danger" onclick="if(confirm('{{ __('Are you sure?') }}')){ document.getElementById('destroy-{{ $issue->id }}').submit(); }">
                                                {{ __('Delete') }}
                                            </flux:menu.item> {{ __('') }} <form id="resolve-fixed-{{ $issue->id }}" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="hidden"> {{ __('') }} </form>

                                    <form id="resolve-wontfix-{{ $issue->id }}" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="hidden"> {{ __('') }} </form>

                                    <form id="destroy-{{ $issue->id }}" action="{{ route('accessibility-issues.destroy', [$project, $issue]) }}" method="POST" class="hidden"> {{ __('') }} </div>
                        </flux:card>
                    @endforeach
                </div> {{ __('') }} <!-- Testing Methodology -->
        @livewire('project-methodologies', ['project' => $project])

    </div> {{ __('') }} <flux:modal name="share-modal" class="md:w-96">
    <div class="space-y-6">
        <flux:heading level="2">{{ __('Share Project') }}</flux:heading> {{ __('') }} <form action="{{ route('project-share-links.store', $project) }}" method="POST"> {{ __('') }} <flux:label>{{ __('Expiration Date (Optional)') }}</flux:label> {{ __('') }} </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">{{ __('Create Share Link') }}</flux:button> {{ __('') }} <flux:separator /> {{ __('') }} <flux:heading level="3" class="mb-4">{{ __('Active Share Links') }}</flux:heading>
            @if ($project->shareLinks->count() > 0)
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach ($project->shareLinks as $link)
                        @if (!$link-> {{ __('') }} <div class="text-sm font-medium text-zinc-900 dark:text-white break-all">
                                        {{ route('projects.shared', $link->token) }}
                                    </div>
                                    @if ($link->expires_at)
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                        {{ __('Expires') }}: {{ $link->expires_at->format('Y-m-d H:i') }}
                                        </div> {{ __('') }} <flux:button 
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
                                    <form action="{{ route('project-share-links.destroy', [$project, $link]) }}" method="POST" class="inline"> {{ __('') }} </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('No active share links yet. Create one above.') }}
                </flux:text> {{ __('') }} </flux:modal>

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

</x-app-layout>