<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1">{{ __('Edit Project') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Update project details and configuration.') }}
                </flux:text>
            </div>

            <form wire:submit="update" enctype="multipart/form-data" class="space-y-8">
                <!-- Project Details -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Project Details') }}</flux:heading>
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
                            <flux:error name="description" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Configuration -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Configuration') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Target WCAG Level') }}</flux:label>
                            <flux:select wire:model="target_wcag_level" required>
                                <option value="wcag2.0-a">WCAG 2.0 Level A</option>
                                <option value="wcag2.0-aa">WCAG 2.0 Level AA</option>
                                <option value="wcag2.0-aaa">WCAG 2.0 Level AAA</option>
                                <option value="wcag2.1-a">WCAG 2.1 Level A</option>
                                <option value="wcag2.1-aa">WCAG 2.1 Level AA</option>
                                <option value="wcag2.1-aaa">WCAG 2.1 Level AAA</option>
                            </flux:select>
                            <flux:error name="target_wcag_level" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <flux:select wire:model="status" required>
                                <option value="planning">{{ __('Planning') }}</option>
                                <option value="in-progress">{{ __('In Progress') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Logo Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Client Logo') }}</flux:heading>
                    @if ($clientLogo)
                        <div class="flex items-center gap-4 mb-4">
                            <img
                                src="{{ Storage::url($clientLogo) }}"
                                alt="{{ $this->name }}"
                                class="h-16 max-w-xs object-contain"
                            />
                            <flux:text size="sm" class="text-zinc-500">
                                {{ __('Current logo') }}
                            </flux:text>
                        </div>
                    @endif

                    <flux:field>
                        <flux:label>{{ __('Upload new logo') }}</flux:label>
                        <flux:description>{{ __('Optional. Shown on reports. PNG, SVG or JPG, max 2 MB.') }}</flux:description>
                        <flux:input type="file" name="client_logo" accept="image/*" />
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
</x-app-layout>
