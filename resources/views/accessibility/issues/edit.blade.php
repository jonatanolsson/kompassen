<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1" class="text-xl">{{ __('Edit Issue') }}</flux:heading>
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
                                rows="6"
                            >{{ old('description', $issue->description) }}</flux:textarea>
                            <flux:text size="sm" class="text-zinc-500 mt-2">
                                {{ __('Supports Markdown formatting') }}
                            </flux:text>
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
                        <div class="space-y-3">
                            <flux:heading level="3" class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Current Images') }}</flux:heading>
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
                                @foreach ($issue->attachments as $attachment)
                                    <div class="flex flex-col">
                                        <flux:modal.trigger name="attachment-lightbox-{{ $loop->index }}">
                                            <button
                                                type="button"
                                                class="relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow duration-200 bg-zinc-50 dark:bg-zinc-800 cursor-pointer hover:opacity-75"
                                            >
                                                <img
                                                    src="{{ asset('storage/' . $attachment->path) }}"
                                                    alt="{{ $attachment->original_filename }}"
                                                    class="w-full h-28 object-cover"
                                                />
                                            </button>
                                        </flux:modal.trigger>
                                        <form
                                            action="{{ route('accessibility-issue-attachments.destroy', [$project, $issue, $attachment]) }}"
                                            method="POST"
                                            onsubmit="return confirm('{{ __('Delete this image?') }}')"
                                            class="mt-3"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger" size="xs" icon="trash" class="w-full">{{ __('Delete') }}</flux:button>
                                        </form>
                                    </div>

                                    <!-- Image Lightbox Modal for each attachment -->
                                    <flux:modal name="attachment-lightbox-{{ $loop->index }}" class="w-auto max-w-4xl">
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between">
                                                <flux:heading level="2">{{ $attachment->original_filename }}</flux:heading>
                                                <flux:modal.close />
                                            </div>

                                            <flux:separator />

                                            <div class="flex justify-center bg-zinc-900 rounded-lg p-4">
                                                <img
                                                    src="{{ asset('storage/' . $attachment->path) }}"
                                                    alt="{{ $attachment->original_filename }}"
                                                    class="max-h-[70vh] object-contain"
                                                />
                                            </div>
                                        </div>
                                    </flux:modal>
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
