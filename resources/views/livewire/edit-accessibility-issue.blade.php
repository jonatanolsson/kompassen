
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1">{{ __('Edit Issue') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Update the issue details and metadata.') }}
                </flux:text>
            </div>

            <form wire:submit="update" enctype="multipart/form-data" class="space-y-8" wire:key="issue-edit-{{ $this->issueId }}">
                <!-- Title Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Issue Details') }}</flux:heading>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Issue Title') }}</flux:label>
                            <flux:input
                                type="text"
                                wire:model.defer="title"
                                placeholder="{{ __('e.g., Missing alt text on product images') }}"
                                required
                            />
                            <flux:error name="title" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea
                                wire:model.defer="description"
                                placeholder="{{ __('Describe the issue in detail...') }}"
                                rows="6"
                            />
                            <flux:text size="sm" class="text-zinc-500 mt-2">{{ __('Supports Markdown formatting') }}</flux:text>
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
                        <flux:input type="file" wire:model="attachments" multiple accept="image/*" />
                        <flux:error name="attachments" />
                    </flux:field>

                    @if (count($databaseAttachments) > 0 || count($this->attachments) > 0)
                        <div class="space-y-3">
                            <flux:heading level="3" class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Current Images') }}</flux:heading>
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
                                <!-- Existing database attachments -->
                                @foreach ($databaseAttachments as $attachment)
                                    <div class="flex flex-col">
                                        <flux:modal.trigger name="issue-attachment-lightbox-{{ $loop->index }}">
                                            <button
                                                type="button"
                                                class="relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow duration-200 bg-zinc-50 dark:bg-zinc-800 cursor-pointer hover:opacity-75"
                                            >
                                                <img
                                                    src="{{ asset('storage/' . $attachment['filename']) }}"
                                                    alt="{{ $attachment['original_filename'] }}"
                                                    class="w-full h-28 object-cover"
                                                />
                                            </button>
                                        </flux:modal.trigger>
                                        <form
                                            action="{{ route('accessibility-issue-attachments.destroy', [$this->projectId, $this->issueId, $attachment['id']]) }}"
                                            method="POST"
                                            onsubmit="return confirm(@js(__('Delete this image?')))"
                                            class="mt-3"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger" size="xs" icon="trash" class="w-full mt-3">{{ __('Delete') }}</flux:button>
                                        </form>
                                    </div>

                                    <!-- Image Lightbox Modal for each attachment -->
                                    <flux:modal name="issue-attachment-lightbox-{{ $loop->index }}" class="w-auto max-w-4xl">
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between">
                                                <flux:heading level="2">{{ $attachment['original_filename'] }}</flux:heading>
                                                <flux:modal.close />
                                            </div>

                                            <flux:separator />

                                            <div class="flex justify-center bg-zinc-900 rounded-lg p-4">
                                                <img
                                                    src="{{ asset('storage/' . $attachment['filename']) }}"
                                                    alt="{{ $attachment['original_filename'] }}"
                                                    class="max-h-[70vh] object-contain"
                                                />
                                            </div>
                                        </div>
                                    </flux:modal>
                                @endforeach

                                <!-- Newly uploaded temporary files (preview) -->
                                @foreach ($this->attachments as $uploadedFile)
                                    <div class="flex flex-col">
                                        <div class="relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm bg-zinc-50 dark:bg-zinc-800">
                                            <img
                                                src="{{ $uploadedFile->temporaryUrl() }}"
                                                alt="{{ $uploadedFile->getClientOriginalName() }}"
                                                class="w-full h-28 object-cover"
                                            />
                                            <div class="absolute inset-0 bg-blue-500 bg-opacity-20 flex items-center justify-center">
                                                <span class="text-white text-xs font-semibold bg-blue-600 bg-opacity-75 px-2 py-1 rounded">{{ __('New') }}</span>
                                            </div>
                                        </div>
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
                            <flux:select wire:model.defer="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page['id'] }}">{{ $page['name'] }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="page_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Component Area') }}</flux:label>
                            <flux:input
                                type="text"
                                wire:model.defer="component_area"
                                placeholder="{{ __('e.g., header, footer, main-nav') }}"
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
                            <flux:select wire:model.defer="severity" required>
                                <option value="critical">{{ __('Critical') }}</option>
                                <option value="major">{{ __('Major') }}</option>
                                <option value="moderate">{{ __('Moderate') }}</option>
                                <option value="minor">{{ __('Minor') }}</option>
                            </flux:select>
                            <flux:error name="severity" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Difficulty to Fix') }}</flux:label>
                            <flux:select wire:model.defer="difficulty" required>
                                <option value="easy">{{ __('Easy') }}</option>
                                <option value="medium">{{ __('Medium') }}</option>
                                <option value="hard">{{ __('Hard') }}</option>
                            </flux:select>
                            <flux:error name="difficulty" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <flux:select wire:model.defer="status" required>
                                <option value="open">{{ __('Open') }}</option>
                                <option value="resolved">{{ __('Resolved') }}</option>
                                <option value="wont_fix">{{ __("Won't Fix") }}</option>
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Actions -->
                <div class="flex gap-3 pt-6">
                    <flux:button type="submit" variant="primary">{{ __('Update Issue') }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancel">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </div>
    </div>

