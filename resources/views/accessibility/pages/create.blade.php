<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <flux:heading level="1" class="mb-2">{{ __('Add Page') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">{{ __('Add a page or service to audit in this project.') }}</flux:text>

        <form action="{{ route('accessibility-pages.store', $project) }}" method="POST">
            @csrf

            <div class="space-y-6">
                <flux:field>
                    <flux:label>{{ __('Page Name') }}</flux:label>
                    <flux:input 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}"
                        placeholder="{{ __('e.g., Homepage, Product Listing') }}"
                        required
                    />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Page URL') }}</flux:label>
                    <flux:input 
                        type="url" 
                        name="url" 
                        value="{{ old('url') }}"
                        placeholder="{{ __('https://example.com/page') }}"
                    />
                    <flux:error name="url" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:textarea 
                        name="description" 
                        placeholder="{{ __('Add notes about this page...') }}"
                        rows="4"
                    >{{ old('description') }}</flux:textarea>
                    <flux:error name="description" />
                </flux:field>

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Add Page') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
