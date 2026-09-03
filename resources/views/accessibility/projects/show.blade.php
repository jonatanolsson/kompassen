<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <flux:heading level="1">{{ $project->name }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">WCAG {{ $project->target_wcag_level }} • {{ Str::title($project->status) }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('accessibility-projects.preview', $project) }}" target="_blank" icon="eye" variant="ghost">Preview</flux:button>
                <flux:modal.trigger name="share-modal">
                    <flux:button icon="link">Share</flux:button>
                </flux:modal.trigger>
                <a href="{{ route('accessibility-projects.edit', $project) }}">
                    <flux:button variant="outline" icon="pencil">Edit</flux:button>
                </a>
                <form action="{{ route('accessibility-projects.destroy', $project) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger" icon="trash" onclick="return confirm('Are you sure?')">Delete</flux:button>
                </form>
            </div>
        </div>

        @if ($project->description)
            <flux:card class="mb-8 p-6">
                <flux:heading level="3" class="mb-2">Description</flux:heading>
                <flux:text>{{ $project->description }}</flux:text>
            </flux:card>
        @endif

        <flux:separator class="my-8" />

        <!-- Pages Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <flux:heading level="2">Pages</flux:heading>
                <a href="{{ route('accessibility-pages.create', $project) }}">
                    <flux:button variant="primary" icon="plus" size="sm">Add Page</flux:button>
                </a>
            </div>

            @if ($project->pages->isEmpty())
                <flux:card class="p-8 text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">No pages added yet.</flux:text>
                </flux:card>
            @else
                <div class="space-y-3">
                    @foreach ($project->pages as $page)
                        <flux:card class="p-4 flex items-center justify-between hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <div>
                                <flux:heading level="4">{{ $page->name }}</flux:heading>
                                @if ($page->url)
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $page->url }}</flux:text>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('accessibility-pages.edit', [$project, $page]) }}">
                                    <flux:button variant="ghost" size="sm" icon="pencil">Edit</flux:button>
                                </a>
                                <form action="{{ route('accessibility-pages.destroy', [$project, $page]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="ghost" size="sm" icon="trash" onclick="return confirm('Are you sure?')">Delete</flux:button>
                                </form>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>

        <flux:separator class="my-8" />

        <!-- Issues Section -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <flux:heading level="2">Issues</flux:heading>
                <a href="{{ route('accessibility-issues.create', $project) }}">
                    <flux:button variant="primary" icon="plus" size="sm">Report Issue</flux:button>
                </a>
            </div>

            @if ($project->issues->isEmpty())
                <flux:card class="p-8 text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">No issues reported yet.</flux:text>
                </flux:card>
            @else
                <div class="space-y-3">
                    @foreach ($project->issues as $issue)
                        <flux:card class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <flux:heading level="4" class="mb-2">{{ $issue->title }}</flux:heading>
                                    <div class="flex gap-2 mb-3">
                                        <flux:badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'major' ? 'amber' : 'green')">
                                            {{ Str::title($issue->severity) }}
                                        </flux:badge>
                                        <flux:badge color="zinc">{{ Str::title($issue->status) }}</flux:badge>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('accessibility-issues.show', [$project, $issue]) }}">
                                        <flux:button variant="ghost" size="sm" icon="eye">View</flux:button>
                                    </a>
                                    <a href="{{ route('accessibility-issues.edit', [$project, $issue]) }}">
                                        <flux:button variant="ghost" size="sm" icon="pencil">Edit</flux:button>
                                    </a>
                                    <form action="{{ route('accessibility-issues.destroy', [$project, $issue]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="ghost" size="sm" icon="trash" onclick="return confirm('Are you sure?')">Delete</flux:button>
                                    </form>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>

        <flux:separator class="my-8" />

        <!-- Testing Methodology -->
        @livewire('project-methodologies', ['project' => $project])

    </div>
</div>

<!-- Share Modal -->
<flux:modal name="share-modal" class="md:w-96">
    <div class="space-y-6">
        <flux:heading level="2">Share Project</flux:heading>

        <flux:separator />

        <!-- Create Share Link Form -->
        <form action="{{ route('project-share-links.store', $project) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Expiration Date (Optional)</flux:label>
                    <flux:input 
                        type="date" 
                        name="expires_at"
                        :min="today()"
                    />
                    <flux:error name="expires_at" />
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">Create Share Link</flux:button>
            </div>
        </form>

        <flux:separator />

        <!-- Existing Share Links -->
        <div>
            <flux:heading level="3" class="mb-4">Active Share Links</flux:heading>
            @if ($project->shareLinks->count() > 0)
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach ($project->shareLinks as $link)
                        @if (!$link->isExpired())
                            <div class="bg-zinc-50 dark:bg-zinc-800 p-3 rounded-lg flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white break-all">
                                        {{ route('projects.shared', $link->token) }}
                                    </div>
                                    @if ($link->expires_at)
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                            Expires: {{ $link->expires_at->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                </div>
            <div class="flex gap-2 ml-2">
                                    <flux:button 
                                        href="{{ route('projects.shared', $link->token) }}"
                                        target="_blank"
                                        icon="eye" 
                                        size="sm"
                                        variant="subtle"
                                    />
                                    <flux:button 
                                        x-on:click="navigator.clipboard.writeText('{{ route('projects.shared', $link->token) }}').then(() => $flux.toast({ text: 'Copied!', variant: 'success' }))" 
                                        icon="document-duplicate" 
                                        size="sm"
                                        variant="subtle"
                                    />
                                    <form action="{{ route('project-share-links.destroy', [$project, $link]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" icon="trash" size="sm" variant="subtle" />
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    No active share links yet. Create one above.
                </flux:text>
            @endif
        </div>
    </div>
</flux:modal>

</x-app-layout>