<div>
    <flux:modal name="export-issue-modal" class="md:w-96">
        <div class="space-y-4">
            <div>
                <flux:heading level="2">{{ __('Export Issue') }}</flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Copy issue data for external systems') }}
                </flux:text>
            </div>

            <flux:separator />

            <!-- Format Selector -->
            <div>
                <flux:heading level="3" class="text-sm mb-3">{{ __('Select Format') }}</flux:heading>
                <flux:select wire:model.live="selectedFormat" class="w-full">
                    <option value="json">{{ __('JSON') }} - {{ __('API & Integrations') }}</option>
                    <option value="markdown">{{ __('Markdown') }} - {{ __('Manual Creation') }}</option>
                    <option value="csv">{{ __('CSV') }} - {{ __('Spreadsheets') }}</option>
                </flux:select>
            </div>

            <!-- Format Info -->
            <flux:card class="p-3 bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800">
                <flux:text class="text-sm text-blue-900 dark:text-blue-100">
                    @switch($selectedFormat)
                        @case('json')
                            {{ __('Use JSON format to import issues into Jira, Azure DevOps, or other API-enabled systems.') }}
                            @break
                        @case('markdown')
                            {{ __('Use Markdown format to manually create tickets or paste into issue descriptions.') }}
                            @break
                        @case('csv')
                            {{ __('Use CSV format for spreadsheet tools and bulk import workflows.') }}
                            @break
                    @endswitch
                </flux:text>
            </flux:card>

            <!-- Preview -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <flux:heading level="3" class="text-sm">{{ __('Preview') }}</flux:heading>
                    <flux:button
                        wire:click="$toggle('showPreview')"
                        variant="ghost"
                        size="sm"
                        icon="{{ $showPreview ? 'chevron-up' : 'chevron-down' }}"
                    >
                        {{ $showPreview ? __('Hide') : __('Show') }}
                    </flux:button>
                </div>

                @if ($showPreview)
                    <div class="bg-zinc-900 dark:bg-zinc-800 text-zinc-100 p-3 rounded-lg font-mono text-xs overflow-auto max-h-48 border border-zinc-700">
                        <pre>{{ $exportContent }}</pre>
                    </div>
                @endif
            </div>

            <flux:separator />

            <!-- Actions -->
            <div class="flex gap-2 justify-end">
                <flux:button
                    type="button"
                    variant="ghost"
                    x-on:click="$dispatch('close-modal', { name: 'export-issue-modal' })"
                >
                    {{ __('Close') }}
                </flux:button>

                <flux:button
                    type="button"
                    variant="outline"
                    icon="arrow-down-tray"
                    wire:click="download"
                >
                    {{ __('Download') }}
                </flux:button>

                <flux:button
                    type="button"
                    variant="primary"
                    icon="square-2-stack"
                    wire:click="copyToClipboard"
                >
                    {{ __('Copy') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    @script
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', (event) => {
                const content = event.content;
                navigator.clipboard.writeText(content).then(() => {
                    // Success - notification will be handled by the notify event
                }).catch(() => {
                    alert('{{ __("Failed to copy to clipboard") }}');
                });
            });

            Livewire.on('notify', (event) => {
                // Show notification - you can use a toast/notification system here
                console.log(event.message);
            });
        });
    </script>
    @endscript
</div>
