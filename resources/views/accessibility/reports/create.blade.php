<x-app-layout>
    <x-slot:title>{{ __('Generate Report for :project', ['project' => $project->name]) }}</x-slot:title>

    <div class="product-page space-y-6">
        <div class="product-page-header">
            <div>
                <flux:heading level="1">{{ __('Generate Report for :project', ['project' => $project->name]) }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Generated reports') }}</flux:text>
            </div>
            <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
        </div>

        <div class="product-section max-w-3xl">
            <form method="POST" action="{{ route('accessibility-reports.store', $project) }}" class="space-y-6">
                @csrf

                <flux:field>
                    <flux:label>{{ __('Report Title') }}</flux:label>
                    <flux:input name="title" type="text" placeholder="{{ __('e.g., Initial Accessibility Audit') }}" value="{{ old('title') }}" required/>
                    <flux:error name="title"/>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Scope') }}</flux:label>
                    <flux:textarea name="scope" placeholder="{{ __('Describe what was tested and what was excluded from this report...') }}" rows="4">{{ old('scope') }}</flux:textarea>
                    <flux:error name="scope"/>
                    <flux:description>{{ __('Optional: Describe the scope of this audit (e.g., pages tested, WCAG level targeted)') }}</flux:description>
                </flux:field>

                <div class="flex justify-end gap-3">
                    <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Generate Report') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
