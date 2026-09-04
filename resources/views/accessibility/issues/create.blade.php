<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <flux:heading level="1" class="mb-2">Report Issue</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                Document an accessibility issue found during the audit.
            </flux:text>

            <form action="{{ route('accessibility-issues.store', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <flux:field>
                    <flux:label>Issue Title</flux:label>
                    <flux:input
                        type="text"
                        name="title"
                        placeholder="e.g., Missing alt text on product images"
                        value="{{ old('title') }}"
                        required
                    />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea
                        name="description"
                        placeholder="Describe the issue in detail..."
                        value="{{ old('description') }}"
                    />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Images</flux:label>
                    <flux:input type="file" name="attachments[]" multiple accept="image/*" />
                    <flux:error name="attachments" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Page/Service</flux:label>
                        <flux:select name="page_id">
                            <option value="">Not specific to a page</option>
                            @foreach ($project->pages as $page)
                                <option value="{{ $page->id }}" @selected(old('page_id') === $page->id)>{{ $page->name }}</option>
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
                            value="{{ old('component_area') }}"
                        />
                        <flux:error name="component_area" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label>Severity</flux:label>
                        <flux:select name="severity" required>
                            <option value="critical" @selected(old('severity') === 'critical')>Critical</option>
                            <option value="major" @selected(old('severity') === 'major' || !old('severity'))>Major</option>
                            <option value="moderate" @selected(old('severity') === 'moderate')>Moderate</option>
                            <option value="minor" @selected(old('severity') === 'minor')>Minor</option>
                        </flux:select>
                        <flux:error name="severity" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Difficulty to Fix</flux:label>
                        <flux:select name="difficulty" required>
                            <option value="easy" @selected(old('difficulty') === 'easy')>Easy</option>
                            <option value="medium" @selected(old('difficulty') === 'medium' || !old('difficulty'))>Medium</option>
                            <option value="hard" @selected(old('difficulty') === 'hard')>Hard</option>
                        </flux:select>
                        <flux:error name="difficulty" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select name="status" required>
                            <option value="open" @selected(old('status') === 'open' || !old('status'))>Open</option>
                            <option value="resolved" @selected(old('status') === 'resolved')>Resolved</option>
                            <option value="wont_fix" @selected(old('status') === 'wont_fix')>Won't Fix</option>
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
                    <flux:button type="submit" variant="primary">Report Issue</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">Cancel</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
