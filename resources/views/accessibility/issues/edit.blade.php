<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1">{{ __('Edit Issue') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Update the issue details and metadata.') }}
                </flux:text>
            </div>

            <form action="{{ route('accessibility-issues.update', [$project, $issue]) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Title Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Issue Details') }}</flux:heading>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Issue Title') }}</flux:label>
                            <flux:input
                                type="text"
                                name="title"
                                placeholder="{{ __('e.g., Missing alt text on product images') }}"
                                value="{{ old('title', $issue->title) }}"
                                required
                            />
                            <flux:error name="title" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea
                                name="description"
                                placeholder="{{ __('Describe the issue in detail...') }}"
                                rows="4"
                            >{{ old('description', $issue->description) }}</flux:textarea>
                            <flux:error name="description" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Images Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Images') }}</flux:heading>
                    
                    <flux:field class="mb-4">
                        <flux:label>{{ __('Upload new images') }}</flux:label>
                        <flux:input type="file" name="attachments[]" multiple accept="image/*" />
                        <flux:error name="attachments" />
                    </flux:field>

                    @if ($issue->attachments->count() > 0)
                        <div>
                            <flux:heading level="3" class="text-sm mb-3">{{ __('Current Images') }}</flux:heading>
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
                                            onsubmit="return confirm('{{ __('Delete this image?') }}')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger" size="xs" icon="trash">{{ __('Delete') }}</flux:button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <flux:separator />

                <!-- Location Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Location') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Page/Service') }}</flux:label>
                            <flux:select name="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($project->pages as $page)
                                    <option value="{{ $page->id }}" @selected(old('page_id', $issue->page_id) === $page->id)>{{ $page->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="page_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Component Area') }}</flux:label>
                            <flux:input
                                type="text"
                                name="component_area"
                                placeholder="{{ __('e.g., header, footer, main-nav') }}"
                                value="{{ old('component_area', $issue->component_area) }}"
                            />
                            <flux:error name="component_area" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Classification Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Classification') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Severity') }}</flux:label>
                            <flux:select name="severity" required>
                                <option value="critical" @selected(old('severity', $issue->severity) === 'critical')>{{ __('Critical') }}</option>
                                <option value="major" @selected(old('severity', $issue->severity) === 'major')>{{ __('Major') }}</option>
                                <option value="moderate" @selected(old('severity', $issue->severity) === 'moderate')>{{ __('Moderate') }}</option>
                                <option value="minor" @selected(old('severity', $issue->severity) === 'minor')>{{ __('Minor') }}</option>
                            </flux:select>
                            <flux:error name="severity" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Difficulty to Fix') }}</flux:label>
                            <flux:select name="difficulty" required>
                                <option value="easy" @selected(old('difficulty', $issue->difficulty) === 'easy')>{{ __('Easy') }}</option>
                                <option value="medium" @selected(old('difficulty', $issue->difficulty) === 'medium')>{{ __('Medium') }}</option>
                                <option value="hard" @selected(old('difficulty', $issue->difficulty) === 'hard')>{{ __('Hard') }}</option>
                            </flux:select>
                            <flux:error name="difficulty" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <flux:select name="status" required>
                                <option value="open" @selected(old('status', $issue->status) === 'open')>{{ __('Open') }}</option>
                                <option value="resolved" @selected(old('status', $issue->status) === 'resolved')>{{ __('Resolved') }}</option>
                                <option value="wont_fix" @selected(old('status', $issue->status) === 'wont_fix')>{{ __("Won't Fix") }}</option>
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- WCAG Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('WCAG Success Criteria') }}</flux:heading>
                    <flux:card class="p-6">
                        <!-- Debug: Show selectedCriteria -->
                        <div class="mb-4 p-2 bg-blue-100 text-sm" style="display: none;">
                            Selected: {{ json_encode($selectedCriteria ?? []) }}
                        </div>
                        @livewire('wcag-criteria-selector', ['initialSelectedCriteria' => $selectedCriteria ?? []])
                        <flux:error name="wcag_criteria" />
                    </flux:card>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-6">
                    <flux:button type="submit" variant="primary">{{ __('Update Issue') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
