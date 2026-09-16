    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Edit Project') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                    {{ __('Update project details and configuration.') }}
                </flux:text>
            </div>

            <form wire:submit="update" enctype="multipart/form-data" class="space-y-8">
                <!-- Project Details -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Project Details') }}</flux:heading>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Project Name') }}</flux:label>
                            <flux:input
                                type="text"
                                wire:model="name"
                                placeholder="{{ __('e.g., Company Website Accessibility Audit') }}"
                                required
                            />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:editor
                                wire:model="description"
                                placeholder="{{ __('Add details about the audit scope...') }}"
                            />
                            <flux:text size="sm" class="text-zinc-500 mt-2">{{ __('Supports Markdown formatting') }}</flux:text>
                            <flux:error name="description" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Configuration -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Configuration') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Target WCAG Level') }}</flux:label>
                            <flux:select wire:model="target_wcag_level" required>
                                <option value="A">{{ __('WCAG :version Level :level', ['version' => $target_wcag_version, 'level' => 'A']) }}</option>
                                <option value="AA">{{ __('WCAG :version Level :level', ['version' => $target_wcag_version, 'level' => 'AA']) }}</option>
                                <option value="AAA">{{ __('WCAG :version Level :level', ['version' => $target_wcag_version, 'level' => 'AAA']) }}</option>
                            </flux:select>
                            <flux:error name="target_wcag_level" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <flux:select wire:model="status" required>
                                <option value="planning">{{ __('Planning') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Logo Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Client Logo') }}</flux:heading>
                    @if ($clientLogo)
                        <div class="flex items-center gap-4 mb-4">
                            <div class="h-16 w-16 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                                <img
                                    src="{{ Storage::url($clientLogo) }}"
                                    alt="{{ $this->name }}"
                                    class="h-full w-full object-contain"
                                />
                            </div>
                            <flux:text size="sm" class="text-zinc-500">
                                {{ __('Current logo') }}
                            </flux:text>
                        </div>
                    @endif

                    <flux:field>
                        <flux:label>{{ __('Upload new logo') }}</flux:label>
                        <flux:description>{{ __('Optional. Shown on reports. PNG, SVG or JPG, max 2 MB.') }}</flux:description>
                        <flux:file-upload wire:model="client_logo" accept="image/*" />
                        @if ($client_logo)
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-16 w-16 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                                    <img
                                        src="{{ $client_logo->temporaryUrl() }}"
                                        alt="{{ __('New client logo preview') }}"
                                        class="h-full w-full object-contain"
                                    />
                                </div>
                                <flux:text size="sm" class="text-zinc-500">{{ __('New preview') }}</flux:text>
                            </div>
                        @endif
                        <flux:error name="client_logo" />
                    </flux:field>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-6">
                    <flux:button type="submit" variant="primary">{{ __('Update Project') }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancel">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
