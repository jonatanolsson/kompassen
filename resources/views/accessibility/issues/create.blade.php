<x-app-layout> {{ __('') }} <flux:heading level="1" class="mb-2">{{ __('Report Issue') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                {{ __('Document an accessibility issue found during the audit.') }}
            </flux:text>

            <form action="{{ route('accessibility-issues.store', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <flux:field>
                    <flux:label>{{ __('Issue Title') }}</flux:label>
                    <flux:input
                        type="text"
                        name="title"
                        placeholder="{{ __('e.g., Missing alt text on product images') }}"
                        value="{{ old('title') }}"
                        required
                    /> {{ __('') }} <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:textarea
                        name="description"
                        placeholder="{{ __('Describe the issue in detail...') }}"
                        value="{{ old('description') }}"
                    /> {{ __('') }} <flux:field>
                    <flux:label>{{ __('Images') }}</flux:label> {{ __('') }} </flux:field> {{ __('') }} <flux:label>{{ __('Page/Service') }}</flux:label>
                        <flux:select name="page_id">
                            <option value="">{{ __('Not specific to a page') }}</option>
                            @foreach ($project->pages as $page)
                                <option value="{{ $page->id }}" @selected(old('page_id') === $page->id)>{{ $page->name }}</option> {{ __('') }} </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Component Area') }}</flux:label> {{ __('') }} </flux:field> {{ __('') }} <flux:field>
                        <flux:label>{{ __('Severity') }}</flux:label>
                        <flux:select name="severity" required>
                            <option value="critical" @selected(old('severity') === 'critical')>{{ __('Critical') }}</option>
                            <option value="major" @selected(old('severity') === 'major' || !old('severity'))>{{ __('Major') }}</option>
                            <option value="moderate" @selected(old('severity') === 'moderate')>{{ __('Moderate') }}</option>
                            <option value="minor" @selected(old('severity') === 'minor')>{{ __('Minor') }}</option> {{ __('') }} </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Difficulty to Fix') }}</flux:label>
                        <flux:select name="difficulty" required>
                            <option value="easy" @selected(old('difficulty') === 'easy')>{{ __('Easy') }}</option>
                            <option value="medium" @selected(old('difficulty') === 'medium' || !old('difficulty'))>{{ __('Medium') }}</option>
                            <option value="hard" @selected(old('difficulty') === 'hard')>{{ __('Hard') }}</option> {{ __('') }} </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select name="status" required>
                            <option value="open" @selected(old('status') === 'open' || !old('status'))>{{ __('Open') }}</option>
                            <option value="resolved" @selected(old('status') === 'resolved')>{{ __('Resolved') }}</option>
                            <option value="wont_fix" @selected(old('status') === 'wont_fix')>{{ __('Won\'t Fix') }}</option> {{ __('') }} </flux:field> {{ __('') }} <flux:label>{{ __('WCAG Success Criteria') }}</flux:label> {{ __('') }} <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Report Issue') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button> {{ __('') }} </form> {{ __('') }} </x-app-layout>
