
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1">{{ __('Add Page') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Add a page or service to audit in this project.') }}</flux:text>
            </div>

            <form wire:submit="submit" class="space-y-8">
                <!-- Page Details -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Page Information') }}</flux:heading>
                    
                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>{{ __('Page Name') }}</flux:label>
                            <flux:input 
                                type="text" 
                                wire:model="name"
                                placeholder="{{ __('e.g., Homepage, Product Listing') }}"
                                required
                            />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Page URL') }}</flux:label>
                            <flux:input 
                                type="url" 
                                wire:model="url"
                                placeholder="{{ __('https://example.com/page') }}"
                            />
                            <flux:error name="url" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea 
                                wire:model="description"
                                placeholder="{{ __('Add notes about this page...') }}"
                                rows="4"
                            />
                            <flux:error name="description" />
                        </flux:field>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Add Page') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>

