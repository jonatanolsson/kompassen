
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Add Page or Service') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Add a page or service to audit in this project.') }}</flux:text>
            </div>

            <form wire:submit="submit" class="space-y-8">
                <!-- Page Details -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Page or Service Information') }}</flux:heading>
                    
                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>{{ __('Name') }}</flux:label>
                            <flux:input 
                                type="text" 
                                wire:model="name"
                                placeholder="{{ __('e.g., Homepage, Product Listing') }}"
                                required
                            />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Type') }}</flux:label>
                            <flux:select wire:model="resourceType">
                                <option value="page">{{ __('Page') }}</option>
                                <option value="service">{{ __('Service') }}</option>
                            </flux:select>
                            <flux:description>{{ __('Choose whether this item is a page or a service.') }}</flux:description>
                            <flux:error name="resourceType" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('URL') }}</flux:label>
                            <flux:input 
                                type="url" 
                                wire:model="url"
                                placeholder="{{ __('https://example.com/page') }}"
                            />
                            <flux:error name="url" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Access context') }}</flux:label>
                            <flux:select wire:model="accessContext">
                                <option value="not_applicable">{{ __('Not applicable') }}</option>
                                <option value="public">{{ __('Public') }}</option>
                                <option value="authenticated">{{ __('Authenticated') }}</option>
                                <option value="mixed">{{ __('Public and authenticated') }}</option>
                            </flux:select>
                            <flux:description>{{ __('Describe whether the page or service requires a login.') }}</flux:description>
                            <flux:error name="accessContext" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:editor
                                wire:model="description"
                                placeholder="{{ __('Add notes about this page...') }}"
                            />
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Scope') }}</flux:label>
                            <flux:select wire:model="scope">
                                <option value="in_scope">{{ __('In Scope') }}</option>
                                <option value="out_of_scope">{{ __('Out of Scope') }}</option>
                            </flux:select>
                            <flux:description>{{ __('Choose whether this page is included in the audit.') }}</flux:description>
                            <flux:error name="scope" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Actions -->
                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Add Page or Service') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>
