<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <flux:heading level="1" class="mb-2">{{ $issue ? 'Edit Issue' : 'Report Issue' }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                {{ $issue ? 'Update the issue details and metadata.' : 'Document an accessibility issue found during the audit.' }}
            </flux:text>

            <form wire:submit="submit" enctype="multipart/form-data" class="space-y-6">
                <flux:field>
                    <flux:label>Issue Title</flux:label>
                    <flux:input
                        type="text"
                        wire:model="title"
                        placeholder="e.g., Missing alt text on product images"
                        required
                    />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:editor
                        wire:model="description"
                        placeholder="Describe the issue in detail..."
                    />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Images</flux:label>
                    <input 
                        type="file"
                        wire:model="attachments"
                        multiple
                        accept="image/*"
                        class="block w-full px-3 py-2 border border-zinc-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <flux:error name="attachments" />
                </flux:field>

                @if ($issue && $issue->attachments->count() > 0)
                    <div class="space-y-3">
                        <flux:label>Current Images</flux:label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach ($issue->attachments as $attachment)
                                <div class="relative group">
                                    <img 
                                        src="{{ asset('storage/' . $attachment->path) }}" 
                                        alt="{{ $attachment->original_filename }}"
                                        class="w-full h-32 object-cover rounded border border-zinc-200 dark:border-zinc-700"
                                    />
                                    <form 
                                        action="{{ route('accessibility-issue-attachments.destroy', [$project, $issue, $attachment]) }}" 
                                        method="POST" 
                                        class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition rounded"
                                        onsubmit="return confirm('Delete this image?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="danger" size="sm">Delete</flux:button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Page/Service</flux:label>
                        <flux:select wire:model="page_id">
                            <option value="">Not specific to a page</option>
                            @foreach ($project->pages as $page)
                                <option value="{{ $page->id }}">{{ $page->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="page_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Component Area</flux:label>
                        <flux:input
                            type="text"
                            wire:model="component_area"
                            placeholder="e.g., header, footer, main-nav"
                        />
                        <flux:error name="component_area" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label>Severity</flux:label>
                        <flux:select wire:model="severity" required>
                            <option value="critical">Critical</option>
                            <option value="major">Major</option>
                            <option value="moderate">Moderate</option>
                            <option value="minor">Minor</option>
                        </flux:select>
                        <flux:error name="severity" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Difficulty to Fix</flux:label>
                        <flux:select wire:model="difficulty" required>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </flux:select>
                        <flux:error name="difficulty" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="status" required>
                            <option value="open">Open</option>
                            <option value="resolved">Resolved</option>
                            <option value="wont_fix">Won't Fix</option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>WCAG Success Criteria</flux:label>
                    <div class="space-y-1 max-h-64 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                        @foreach ($wcagCriteria->groupBy(fn ($c) => explode('.', $c->number)[0]) as $principle => $group)
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mt-2 mb-1 first:mt-0">
                                Principle {{ $principle }}
                            </p>
                            @foreach ($group as $criterion)
                                <flux:checkbox
                                    wire:model="selectedCriteria"
                                    value="{{ $criterion->id }}"
                                    label="{{ $criterion->number }} ({{ $criterion->level }}) {{ $criterion->name_en }}"
                                />
                            @endforeach
                        @endforeach
                    </div>
                    <flux:error name="wcag_criteria" />
                </flux:field>

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ $issue ? 'Update Issue' : 'Report Issue' }}</flux:button>
                    <a href="{{ $issue ? route('accessibility-projects.show', $project) : route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">Cancel</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
