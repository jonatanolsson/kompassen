
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Edit Issue') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                    {{ __('Update the issue details and metadata.') }}
                </flux:text>
            </div>

            <form wire:submit="update" enctype="multipart/form-data" class="space-y-8" wire:key="issue-edit-{{ $this->issueId }}">
                <!-- Title Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Issue Details') }}</flux:heading>
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
                            <flux:editor
                                wire:model="description"
                                placeholder="{{ __('Describe the issue in detail...') }}"
                            />
                            <flux:text size="sm" class="text-zinc-500 mt-2">{{ __('Supports Markdown formatting') }}</flux:text>
                            <flux:error name="description" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Images Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Images') }}</flux:heading>

                    <flux:field class="mb-4">
                        <flux:label>{{ __('Upload new images') }}</flux:label>
                        <flux:file-upload wire:model="attachments" multiple accept="image/*" />
                        <flux:error name="attachments" />
                    </flux:field>

                    @if (count($databaseAttachments) > 0 || count($this->attachments) > 0)
                        <div class="space-y-3">
                            <flux:heading level="3" class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Current Images') }}</flux:heading>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                <!-- Existing database attachments -->
                                @foreach ($databaseAttachments as $attachment)
                                    <div class="flex min-w-0 flex-col gap-2">
                                        <flux:modal.trigger name="issue-attachment-lightbox-{{ $loop->index }}">
                                            <button
                                                type="button"
                                                class="relative aspect-square w-full overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800"
                                            >
                                                <img
                                                    src="{{ asset('storage/' . $attachment['path']) }}"
                                                    alt="{{ $attachment['original_filename'] }}"
                                                    class="h-full w-full object-cover transition-transform duration-200 hover:scale-105"
                                                />
                                            </button>
                                        </flux:modal.trigger>
                                        <flux:button
                                            type="button"
                                            variant="danger"
                                            size="xs"
                                            icon="trash"
                                            class="w-full"
                                            wire:click="deleteAttachment('{{ $attachment['id'] }}')"
                                            wire:confirm="{{ __('Delete this image?') }}"
                                        >
                                            {{ __('Delete') }}
                                        </flux:button>

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
                                                        src="{{ asset('storage/' . $attachment['path']) }}"
                                                        alt="{{ $attachment['original_filename'] }}"
                                                        class="max-h-[70vh] object-contain"
                                                    />
                                                </div>
                                            </div>
                                        </flux:modal>
                                    </div>
                                @endforeach

                                <!-- Newly uploaded temporary files (preview) -->
                                @foreach ($this->attachments as $uploadedFile)
                                    <div class="flex min-w-0 flex-col gap-2">
                                        <div class="relative aspect-square overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                            <img
                                                src="{{ $uploadedFile->temporaryUrl() }}"
                                                alt="{{ $uploadedFile->getClientOriginalName() }}"
                                                class="w-full h-full object-cover"
                                            />
                                            <div class="absolute inset-0 flex items-center justify-center bg-blue-950/35">
                                                <span class="rounded bg-blue-600/85 px-2 py-1 text-xs font-semibold text-white">{{ __('New') }}</span>
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
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Location') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Page/Service') }}</flux:label>
                            <flux:select wire:model.defer="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page['id'] }}">
                                        {{ $page['name'] }}{{ ($page['resource_type'] ?? 'page') === 'service' ? ' ('.__('Service').')' : '' }}
                                    </option>
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
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Classification') }}</flux:heading>
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
