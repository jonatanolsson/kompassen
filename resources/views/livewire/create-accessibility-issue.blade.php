
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <flux:heading level="1">{{ __('Report Issue') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Document an accessibility issue found during the audit.') }}</flux:text>
            </div>

            <form wire:submit="submit" enctype="multipart/form-data" class="space-y-8">
                <!-- Issue Details -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Issue Information') }}</flux:heading>
                    
                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>{{ __('Issue Title') }}</flux:label>
                            <flux:input
                                type="text"
                                wire:model="title"
                                placeholder="{{ __('e.g., Missing alt text on product images') }}"
                                required
                            />
                            <flux:error name="title" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea
                                wire:model="description"
                                placeholder="{{ __('Describe the issue in detail...') }}"
                                rows="6"
                            />
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Images/Evidence') }}</flux:label>
                            <flux:file-upload
                                wire:model="attachments"
                                multiple
                                accept="image/*"
                            />
                            <flux:error name="attachments" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Location & Classification -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('Classification') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Page/Service') }}</flux:label>
                            <flux:select wire:model="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page->id }}">{{ $page->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="page_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Component Area') }}</flux:label>
                            <flux:input
                                type="text"
                                wire:model="component_area"
                                placeholder="{{ __('e.g., header, footer, main-nav') }}"
                            />
                            <flux:error name="component_area" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Severity') }}</flux:label>
                            <flux:select wire:model="severity" required>
                                <option value="critical">{{ __('Critical') }}</option>
                                <option value="major" selected>{{ __('Major') }}</option>
                                <option value="moderate">{{ __('Moderate') }}</option>
                                <option value="minor">{{ __('Minor') }}</option>
                            </flux:select>
                            <flux:error name="severity" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Difficulty to Fix') }}</flux:label>
                            <flux:select wire:model="difficulty" required>
                                <option value="easy">{{ __('Easy') }}</option>
                                <option value="medium" selected>{{ __('Medium') }}</option>
                                <option value="hard">{{ __('Hard') }}</option>
                            </flux:select>
                            <flux:error name="difficulty" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <flux:select wire:model="status" required>
                                <option value="open" selected>{{ __('Open') }}</option>
                                <option value="resolved">{{ __('Resolved') }}</option>
                                <option value="wont_fix">{{ __('Won\'t Fix') }}</option>
                            </flux:select>
                            <flux:error name="status" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- WCAG Criteria -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4">{{ __('WCAG Success Criteria') }}</flux:heading>
                    
                    <div class="space-y-4">
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ __('Select which WCAG success criteria this issue relates to.') }}
                        </flux:text>

                        <div class="space-y-2 max-h-96 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            @forelse ($wcagCriteria as $criterion)
                                <label class="flex items-start gap-3 p-2 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        wire:model="wcag_criteria" 
                                        value="{{ $criterion->id }}"
                                        class="mt-1"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-sm">{{ $criterion->code }}</div>
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400">{{ Str::limit($criterion->name, 80) }}</div>
                                    </div>
                                </label>
                            @empty
                                <flux:text class="text-zinc-500">{{ __('No WCAG criteria available') }}</flux:text>
                            @endforelse
                        </div>

                        <flux:error name="wcag_criteria" />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Report Issue') }}</flux:button>
                    <a href="{{ route('accessibility-projects.show', $project) }}">
                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </a>
                </div>
            </form>
        </div>
    </div>

