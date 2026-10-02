<div>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="w-full rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Update Page or Service') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                    {{ __('Update page or service details.') }}
                </flux:text>
            </div>

            <form wire:submit="submit" class="space-y-8">
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Page or Service Information') }}</flux:heading>

                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>{{ __('Name') }}</flux:label>
                            <flux:input type="text" wire:model="name" required />
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
                            <flux:input type="url" wire:model="url" />
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

                        <flux:radio.group
                            wire:model="scope"
                            label="{{ __('Scope') }}"
                            description="{{ __('Choose whether this page or service is included in the audit.') }}"
                        >
                            <flux:radio value="in_scope" label="{{ __('Yes') }}" />
                            <flux:radio value="out_of_scope" label="{{ __('No') }}" />
                        </flux:radio.group>
                        <flux:error name="scope" />
                    </div>
                </div>

                <flux:separator />

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Update Page or Service') }}</flux:button>
                    <flux:button href="{{ route('accessibility-projects.show', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
