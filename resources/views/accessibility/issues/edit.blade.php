<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <flux:heading level="1" class="mb-2">Edit Issue</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                Update the issue details and metadata.
            </flux:text>

            <form action="{{ route('accessibility-issues.update', [$project, $issue]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <flux:field>
                    <flux:label>Issue Title</flux:label>
                    <flux:input
                        type="text"
                        name="title"
                        placeholder="e.g., Missing alt text on product images"
                        value="{{ old('title', $issue->title) }}"
                        required
                    />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea
                        name="description"
                        placeholder="Describe the issue in detail..."
                        value="{{ old('description', $issue->description) }}"
                    />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Images</flux:label>
                    <flux:input type="file" name="attachments[]" multiple accept="image/*" />
                    <flux:error name="attachments" />
                </flux:field>

                @if ($issue->attachments->count() > 0)
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
                        <flux:select name="page_id">
                            <option value="">Not specific to a page</option>
                            @foreach ($project->pages as $page)
                                <option value="{{ $page->id }}" @selected(old('page_id', $issue->page_id) === $page->id)>{{ $page->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="page_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Component Area</flux:label>
                        <flux:input
                            type="text"
                            name="component_area"
                            placeholder="e.g., header, footer, main-nav"
                            value="{{ old('component_area', $issue->component_area) }}"
                        />
                        <flux:error name="component_area" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label>Severity</flux:label>
                        <flux:select name="severity" required>
                            <option value="critical" @selected(old('severity', $issue->severity) === 'critical')>Critical</option>
                            <option value="major" @selected(old('severity', $issue->severity) === 'major')>Major</option>
                            <option value="moderate" @selected(old('severity', $issue->severity) === 'moderate')>Moderate</option>
                            <option value="minor" @selected(old('severity', $issue->severity) === 'minor')>Minor</option>
                        </flux:select>
                        <flux:error name="severity" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Difficulty to Fix</flux:label>
                        <flux:select name="difficulty" required>
                            <option value="easy" @selected(old('difficulty', $issue->difficulty) === 'easy')>Easy</option>
                            <option value="medium" @selected(old('difficulty', $issue->difficulty) === 'medium')>Medium</option>
                            <option value="hard" @selected(old('difficulty', $issue->difficulty) === 'hard')>Hard</option>
                        </flux:select>
                        <flux:error name="difficulty" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select name="status" required>
                            <option value="open" @selected(old('status', $issue->status) === 'open')>Open</option>
                            <option value="resolved" @selected(old('status', $issue->status) === 'resolved')>Resolved</option>
                            <option value="wont_fix" @selected(old('status', $issue->status) === 'wont_fix')>Won't Fix</option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>WCAG Success Criteria</flux:label>
                    @livewire('wcag-criteria-selector')
                    <flux:error name="wcag_criteria" />
                </flux:field>

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">Update Issue</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">Cancel</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
