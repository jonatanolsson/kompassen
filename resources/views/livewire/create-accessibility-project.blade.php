
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Create Project') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Start a new accessibility audit by creating a project.') }}</flux:text>
            </div>

            <form wire:submit="submit" enctype="multipart/form-data" class="space-y-8">
                <!-- Project Details -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Project Details') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>{{ __('Project Name') }}</flux:label>
                                <flux:input 
                                    type="text" 
                                    wire:model="name"
                                    placeholder="{{ __('e.g., Company Website Audit') }}"
                                    required
                                />
                                <flux:error name="name" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>{{ __('Target WCAG Level') }}</flux:label>
                            <flux:select wire:model="target_wcag_level" required>
                                <option value="A">{{ __('WCAG 2.1 Level A') }}</option>
                                <option value="AA" selected>{{ __('WCAG 2.1 Level AA') }}</option>
                                <option value="AAA">{{ __('WCAG 2.1 Level AAA') }}</option>
                            </flux:select>
                            <flux:error name="target_wcag_level" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Audit Date') }}</flux:label>
                            <flux:input 
                                type="date" 
                                wire:model="audit_date"
                            />
                            <flux:error name="audit_date" />
                        </flux:field>

                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>{{ __('Description') }}</flux:label>
                                <flux:editor
                                    wire:model="description"
                                    placeholder="{{ __('Add details about the audit scope...') }}"
                                />
                                <flux:text size="sm" class="text-zinc-500 mt-2">
                                    {{ __('Supports Markdown formatting') }}
                                </flux:text>
                                <flux:error name="description" />
                            </flux:field>
                        </div>
                    </div>
                </div>

                <flux:separator />

                <!-- Client Logo -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Branding') }}</flux:heading>
                    
                    <flux:field>
                        <flux:label>{{ __('Client Logo') }}</flux:label>
                        <flux:file-upload
                            wire:model="client_logo"
                            accept="image/*"
                        />
                        @if ($client_logo)
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-16 w-16 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                                    <img
                                        src="{{ $client_logo->temporaryUrl() }}"
                                        alt="{{ __('New client logo preview') }}"
                                        class="h-full w-full object-contain"
                                    />
                                </div>
                                <flux:text size="sm" class="text-zinc-500">{{ __('Preview') }}</flux:text>
                            </div>
                        @endif
                        <flux:description>{{ __('Optional. Shown on reports for this project. PNG, SVG or JPG, max 2 MB.') }}</flux:description>
                        <flux:error name="client_logo" />
                    </flux:field>
                </div>

                <flux:separator />

                <!-- Actions -->
                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Create Project') }}</flux:button>
                    <a href="{{ route('accessibility-projects.index') }}">
                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>
