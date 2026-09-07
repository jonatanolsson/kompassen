<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <flux:heading level="1" class="mb-2">{{ __('Update Project') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">{{ __('Update project details and audit settings.') }}</flux:text>

            <form action="{{ route('accessibility-projects.update', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <flux:field>
                        <flux:label>{{ __('Project Name') }}</flux:label>
                        <flux:input 
                            type="text" 
                            name="name" 
                            value="{{ old('name', $project->name) }}"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Description') }}</flux:label>
                        <flux:textarea 
                            name="description" 
                            rows="4"
                        >{{ old('description', $project->description) }}</flux:textarea>
                        <flux:error name="description" />
                    </flux:field>

                    <div>
                        <flux:heading size="sm" level="3" class="mb-3">{{ __('Client Logo') }}</flux:heading>
                        @if ($project->client_logo)
                            <div class="flex items-center gap-4 mb-3">
                                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-3 flex items-center justify-center w-32 h-16">
                                    <img src="{{ Storage::url($project->client_logo) }}" alt="{{ __('Client logo') }}" class="max-h-10 max-w-full object-contain" />
                                </div>
                                <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 cursor-pointer">
                                    <input type="checkbox" name="remove_client_logo" value="1" class="rounded" />
                                    {{ __('Remove current logo') }}
                                </label>
                            </div>
                        @endif
                        <flux:field>
                            <flux:label>{{ $project->client_logo ? __('Replace logo') : __('Upload client logo') }}</flux:label>
                            <flux:input type="file" name="client_logo" accept="image/*" />
                            <flux:description>{{ __('Optional. Shown on reports. PNG, SVG or JPG, max 2 MB.') }}</flux:description>
                            <flux:error name="client_logo" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>{{ __('Target WCAG Level') }}</flux:label>
                        <flux:select 
                            name="target_wcag_level" 
                            required
                        >
                            <option value="A" {{ old('target_wcag_level', $project->target_wcag_level) === 'A' ? 'selected' : '' }}>{{ __('WCAG 2.1 Level A') }}</option>
                            <option value="AA" {{ old('target_wcag_level', $project->target_wcag_level) === 'AA' ? 'selected' : '' }}>{{ __('WCAG 2.1 Level AA') }}</option>
                            <option value="AAA" {{ old('target_wcag_level', $project->target_wcag_level) === 'AAA' ? 'selected' : '' }}>{{ __('WCAG 2.1 Level AAA') }}</option>
                        </flux:select>
                        <flux:error name="target_wcag_level" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select 
                            name="status" 
                            required
                        >
                            <option value="planning" {{ old('status', $project->status) === 'planning' ? 'selected' : '' }}>{{ __('Planning') }}</option>
                            <option value="in_progress" {{ old('status', $project->status) === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                            <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Audit Date') }}</flux:label>
                        <flux:input 
                            type="date" 
                            name="audit_date" 
                            value="{{ old('audit_date', $project->audit_date?->format('Y-m-d')) }}"
                        />
                        <flux:error name="audit_date" />
                    </flux:field>

                    <div class="flex gap-4 pt-4">
                        <flux:button type="submit" variant="primary">{{ __('Update Project') }}</flux:button>
                        <a href="{{ route('accessibility-projects.show', $project) }}">
                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
