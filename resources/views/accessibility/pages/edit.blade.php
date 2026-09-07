<x-app-layout> {{ __('') }} <flux:heading level="1" class="mb-2">{{ __('Update Page') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">{{ __('Update page or service details.') }}</flux:text>

        <form action="{{ route('accessibility-pages.update', [$project, $page]) }}" method="POST"> {{ __('') }} <flux:label>{{ __('Page Name') }}</flux:label>
                    <flux:input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $page->name) }}"
                        required
                    /> {{ __('') }} <flux:field>
                    <flux:label>{{ __('Page URL') }}</flux:label>
                    <flux:input 
                        type="url" 
                        name="url" 
                        value="{{ old('url', $page->url) }}"
                    /> {{ __('') }} <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:textarea 
                        name="description" 
                        rows="4"
                    >{{ old('description', $page-> {{ __('') }} </flux:field>

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Update Page') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button> {{ __('') }} </div> {{ __('') }} </div>
</x-app-layout>
