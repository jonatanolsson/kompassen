<div class="max-w-2xl mx-auto px-4 py-8 space-y-8">
    <div>
        <flux:heading level="1" class="mb-1">{{ __('App Settings') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Manage branding and application settings.') }}</flux:text>
    </div>

    <flux:separator />

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
            <flux:heading level="2" size="sm">{{ __('Brand Logo') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Displayed in the sidebar. PNG or SVG recommended.') }}</flux:text>
            </div>

            <div class="sm:col-span-2 space-y-4">
                @if ($currentLogo)
                    <div class="flex items-center gap-4">
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-3 flex items-center justify-center w-32 h-16">
                            <img src="{{ Storage::url($currentLogo) }}" alt="{{ __('Current logo') }}" class="max-h-10 max-w-full object-contain" />
                        </div>
                        <flux:button type="button" variant="subtle" size="sm" icon="trash" wire:click="removeLogo" wire:confirm="{{ __('Remove the current logo?') }}">
                            {{ __('Remove') }}
                        </flux:button>
                    </div>
                @endif

                <flux:field>
                <flux:label>{{ $currentLogo ? __('Replace logo') : __('Upload logo') }}</flux:label>
                    <flux:input type="file" wire:model="logo" accept="image/*" />
                    <flux:error name="logo" />
                    @if ($logo)
                        <div class="mt-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-3 inline-flex">
                        <img src="{{ $logo->temporaryUrl() }}" alt="{{ __('Preview') }}" class="max-h-12 object-contain" />
                        </div>
                    @endif
                </flux:field>
            </div>
        </div>

        <flux:separator />

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">{{ __('Save Settings') }}</flux:button>
        </div>
    </form>
</div>
