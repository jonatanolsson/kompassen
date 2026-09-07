<x-app-layout> {{ __('') }} <flux:heading level="1" class="mb-2">{{ __('Add Page') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">{{ __('Add a page or service to audit in this project.') }}</flux:text>

        <form action="{{ route('accessibility-pages.store', $project) }}" method="POST"> {{ __('') }} <flux:label>{{ __('Page Name') }}</flux:label>
                    <flux:input 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}"
                        placeholder="{{ __('e.g., Homepage, Product Listing') }}"
                        required
                    /> {{ __('') }} <flux:field>
                    <flux:label>{{ __('Page URL') }}</flux:label>
                    <flux:input 
                        type="url" 
                        name="url" 
                        value="{{ old('url') }}"
                        placeholder="{{ __('https://example.com/page') }}"
                    /> {{ __('') }} <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:textarea 
                        name="description" 
                        placeholder="{{ __('Add notes about this page...') }}"
                        rows="4"
                    > {{ __('') }} </flux:field>

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Add Page') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button> {{ __('') }} </div> {{ __('') }} </div>
</x-app-layout>
