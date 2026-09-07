<x-app-layout> {{ __('') }} <div class="mb-8">
                <flux:heading level="1">{{ __('Edit Issue') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Update the issue details and metadata.') }}
                </flux:text> {{ __('') }} <!-- Title Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Issue Details') }}</flux:heading> {{ __('') }} <flux:label>{{ __('Issue Title') }}</flux:label>
                            <flux:input
                                type="text"
                                name="title"
                                placeholder="{{ __('e.g., Missing alt text on product images') }}"
                                value="{{ old('title', $issue->title) }}"
                                required
                            /> {{ __('') }} <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea
                                name="description"
                                placeholder="{{ __('Describe the issue in detail...') }}"
                                rows="4"
                            >{{ old('description', $issue-> {{ __('') }} </flux:field> {{ __('') }} <flux:separator /> {{ __('') }} <flux:heading level="2" class="mb-4">{{ __('Images') }}</flux:heading>
                    
                    <flux:field class="mb-4">
                        <flux:label>{{ __('Upload new images') }}</flux:label> {{ __('') }} </flux:field>

                    @if ($issue->attachments->count() > 0)
                        <div>
                            <flux:heading level="3" class="text-sm mb-3">{{ __('Current Images') }}</flux:heading>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach ($issue-> {{ __('') }} <form 
                                            action="{{ route('accessibility-issue-attachments.destroy', [$project, $issue, $attachment]) }}" 
                                            method="POST" 
                                            class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition rounded"
                                            onsubmit="return confirm('{{ __('Delete this image?') }}')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger" size="xs" icon="trash">{{ __('Delete') }}</flux:button> {{ __('') }} </div>
                        </div> {{ __('') }} <!-- Location Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Location') }}</flux:heading> {{ __('') }} <flux:label>{{ __('Page/Service') }}</flux:label>
                            <flux:select name="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($project->pages as $page)
                                    <option value="{{ $page->id }}" @selected(old('page_id', $issue->page_id) === $page->id)>{{ $page->name }}</option> {{ __('') }} </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Component Area') }}</flux:label>
                            <flux:input
                                type="text"
                                name="component_area"
                                placeholder="{{ __('e.g., header, footer, main-nav') }}"
                                value="{{ old('component_area', $issue->component_area) }}"
                            /> {{ __('') }} </div> {{ __('') }} <!-- Classification Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Classification') }}</flux:heading> {{ __('') }} <flux:label>{{ __('Severity') }}</flux:label>
                            <flux:select name="severity" required>
                                <option value="critical" @selected(old('severity', $issue->severity) === 'critical')>{{ __('Critical') }}</option>
                                <option value="major" @selected(old('severity', $issue->severity) === 'major')>{{ __('Major') }}</option>
                                <option value="moderate" @selected(old('severity', $issue->severity) === 'moderate')>{{ __('Moderate') }}</option>
                                <option value="minor" @selected(old('severity', $issue->severity) === 'minor')>{{ __('Minor') }}</option> {{ __('') }} </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Difficulty to Fix') }}</flux:label>
                            <flux:select name="difficulty" required>
                                <option value="easy" @selected(old('difficulty', $issue->difficulty) === 'easy')>{{ __('Easy') }}</option>
                                <option value="medium" @selected(old('difficulty', $issue->difficulty) === 'medium')>{{ __('Medium') }}</option>
                                <option value="hard" @selected(old('difficulty', $issue->difficulty) === 'hard')>{{ __('Hard') }}</option> {{ __('') }} </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <flux:select name="status" required>
                                <option value="open" @selected(old('status', $issue->status) === 'open')>{{ __('Open') }}</option>
                                <option value="resolved" @selected(old('status', $issue->status) === 'resolved')>{{ __('Resolved') }}</option>
                                <option value="wont_fix" @selected(old('status', $issue->status) === 'wont_fix')> {{ __('') }} <flux:error name="status" /> {{ __('') }} </div> {{ __('') }} <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('WCAG Success Criteria') }}</flux:heading>
                    <flux:card class="p-6">
                        @livewire('wcag-criteria-selector', ['initialSelectedCriteria' => $selectedCriteria ?? []], key('wcag-selector-'.$issue-> {{ __('') }} </div> {{ __('') }} <flux:button type="submit" variant="primary">{{ __('Update Issue') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button> {{ __('') }} </form> {{ __('') }} </x-app-layout>
