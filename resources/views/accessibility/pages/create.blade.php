<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <flux:heading level="1" class="mb-2">{{ __('Add Page or Service') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">{{ __('Add a page or service to audit in this project.') }}</flux:text>

        <form action="{{ route('accessibility-pages.store', $project) }}" method="POST">
            @csrf

            <div class="space-y-6">
                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
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
                    <flux:label>{{ __('Type') }}</flux:label>
                    <flux:select name="resource_type">
                        <option value="page" @selected(old('resource_type', 'page') === 'page')>{{ __('Page') }}</option>
                        <option value="service" @selected(old('resource_type') === 'service')>{{ __('Service') }}</option>
                    </flux:select>
                    <flux:description>{{ __('Choose whether this item is a page or a service.') }}</flux:description>
                    <flux:error name="resource_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('URL') }}</flux:label>
                    <flux:input 
                        type="url" 
                        name="url" 
                        value="{{ old('url') }}"
                        placeholder="{{ __('https://example.com/page') }}"
                    />
                    <flux:error name="url" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Access context') }}</flux:label>
                    <flux:select name="access_context">
                        <option value="not_applicable" @selected(old('access_context', 'not_applicable') === 'not_applicable')>{{ __('Not applicable') }}</option>
                        <option value="public" @selected(old('access_context') === 'public')>{{ __('Public') }}</option>
                        <option value="authenticated" @selected(old('access_context') === 'authenticated')>{{ __('Authenticated') }}</option>
                        <option value="mixed" @selected(old('access_context') === 'mixed')>{{ __('Public and authenticated') }}</option>
                    </flux:select>
                    <flux:description>{{ __('Describe whether the page or service requires a login.') }}</flux:description>
                    <flux:error name="access_context" />
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

                <flux:field>
                    <flux:label>{{ __('Scope') }}</flux:label>
                    <flux:select name="scope">
                        <option value="in_scope" @selected(old('scope', 'in_scope') === 'in_scope')>{{ __('In Scope') }}</option>
                        <option value="out_of_scope" @selected(old('scope') === 'out_of_scope')>{{ __('Out of Scope') }}</option>
                    </flux:select>
                    <flux:description>{{ __('Choose whether this page is included in the audit.') }}</flux:description>
                    <flux:error name="scope" />
                </flux:field>

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Add Page or Service') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
