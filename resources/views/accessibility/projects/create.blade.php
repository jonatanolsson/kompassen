<x-app-layout> {{ __('') }} <flux:heading level="1" class="mb-2">{{ __('Create Project') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">{{ __('Start a new accessibility audit by creating a project.') }}</flux:text>

            <form action="{{ route('accessibility-projects.store') }}" method="POST" enctype="multipart/form-data"> {{ __('') }} <flux:label>{{ __('Project Name') }}</flux:label>
                        <flux:input 
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}"
                            placeholder="{{ __('e.g., Company Website Audit') }}"
                            required
                        /> {{ __('') }} <flux:field>
                        <flux:label>{{ __('Description') }}</flux:label>
                        <flux:textarea 
                            name="description" 
                            placeholder="Add details about the audit scope..."
                            rows="4"
                        > {{ __('') }} </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Client Logo') }}</flux:label>
                        <flux:input type="file" name="client_logo" accept="image/*" />
                        <flux:description>{{ __('Optional. Shown on reports for this project. PNG, SVG or JPG, max 2 MB.') }}</flux:description> {{ __('') }} <flux:field>
                        <flux:label>{{ __('Target WCAG Level') }}</flux:label>
                        <flux:select 
                            name="target_wcag_level" 
                            required
                        >
                            <option value="">{{ __('Select a level') }}</option>
                            <option value="A" {{ old('target_wcag_level') === 'A' ? 'selected' : '' }}>{{ __('WCAG 2.1 Level A') }}</option>
                            <option value="AA" {{ old('target_wcag_level') === 'AA' ? 'selected' : '' }}>{{ __('WCAG 2.1 Level AA') }}</option>
                            <option value="AAA" {{ old('target_wcag_level') === 'AAA' ? 'selected' : '' }}>{{ __('WCAG 2.1 Level AAA') }}</option> {{ __('') }} </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Audit Date') }}</flux:label> {{ __('') }} </flux:field>

                    <div class="flex gap-4 pt-4">
                        <flux:button type="submit" variant="primary">{{ __('Create Project') }}</flux:button>
                        <a href="{{ route('accessibility-projects.index') }}">
                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button> {{ __('') }} </div> {{ __('') }} </div>
</x-app-layout>
