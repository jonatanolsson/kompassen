
    <div class="product-page">
        <div class="product-section w-full">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Report Issue') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Document an accessibility issue found during the audit.') }}</flux:text>
            </div>

            <form wire:submit="submit" enctype="multipart/form-data" class="space-y-8">
                <!-- Issue Details -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Issue Information') }}</flux:heading>
                    
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
                            <flux:editor
                                wire:model="description"
                                placeholder="{{ __('Describe the issue in detail...') }}"
                            />
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Mermaid diagram') }}</flux:label>
                            <flux:textarea
                                wire:model="mermaid_diagram"
                                rows="8"
                                placeholder="flowchart LR&#10;    A[Observed issue] --> B[Impact]"
                                class="font-mono text-sm"
                            />
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                {{ __('Optional Mermaid source. It will be rendered on the issue detail page.') }}
                            </flux:text>
                            <flux:error name="mermaid_diagram" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Images/Evidence') }}</flux:label>
                            <flux:file-upload
                                wire:model="attachments"
                                multiple
                                accept="image/*"
                            />
                            <flux:error name="attachments" />

                            @if (count($this->attachments) > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 pt-2">
                                    @foreach ($this->attachments as $uploadedFile)
                                        <div class="relative aspect-square overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                            <img
                                                src="{{ $uploadedFile->temporaryUrl() }}"
                                                alt="{{ $uploadedFile->getClientOriginalName() }}"
                                                class="h-full w-full object-cover"
                                            />
                                            <span class="absolute bottom-2 left-2 rounded bg-blue-600/85 px-2 py-1 text-xs font-semibold text-white">
                                                {{ __('New') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Location & Classification -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Classification') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Page/Service') }}</flux:label>
                            <flux:select wire:model="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page->id }}">
                                        {{ $page->name }}{{ ($page->resource_type ?? 'page') === 'service' ? ' ('.__('Service').')' : '' }}
                                    </option>
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
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('WCAG Success Criteria') }}</flux:heading>
                    
                    <div class="space-y-4">
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ __('Select which WCAG success criteria this issue relates to.') }}
                        </flux:text>

                        <div class="space-y-2 max-h-96 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            @forelse ($wcagCriteria as $criterion)
                                <flux:checkbox
                                    wire:model="wcag_criteria"
                                    value="{{ $criterion->id }}"
                                    :label="$criterion->code.' — '.Str::limit($criterion->name, 80)"
                                />
                            @empty
                                <flux:text class="text-zinc-500">{{ __('No WCAG criteria available') }}</flux:text>
                            @endforelse
                        </div>

                        <flux:error name="wcag_criteria" />
                    </div>
                </div>

                <flux:separator />

                <!-- Knowledge Base Examples -->
                <div class="py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading level="2" class="mb-2 text-lg font-semibold">{{ __('Knowledge Base Examples') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                {{ __('Link one or more reusable examples to show what was found and how it can be addressed.') }}
                            </flux:text>
                        </div>
                        @if (count($example_ids) > 0)
                            <flux:badge color="blue">{{ count($example_ids) }}</flux:badge>
                        @endif
                    </div>

                    @if (count($wcag_criteria) === 0)
                        <flux:callout icon="information-circle" color="zinc" class="mt-4">
                            <flux:callout.text>{{ __('Select one or more WCAG criteria first to see related examples.') }}</flux:callout.text>
                        </flux:callout>
                    @elseif ($availableExamples->isEmpty())
                        <flux:callout icon="light-bulb" color="zinc" class="mt-4">
                            <flux:callout.text>{{ __('No examples are available for the selected criteria yet.') }}</flux:callout.text>
                        </flux:callout>
                    @else
                        <div class="mt-4 space-y-4">
                            <flux:input
                                wire:model.live.debounce.300ms="exampleSearch"
                                :loading="false"
                                icon="magnifying-glass"
                                placeholder="{{ __('Search examples…') }}"
                            />
                            <div class="max-h-80 space-y-2 overflow-y-auto rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                                @foreach ($availableExamples as $example)
                                    <div wire:key="create-issue-example-{{ $example->id }}" class="flex items-start gap-3 rounded-lg px-3 py-2 transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                        <flux:checkbox
                                            wire:model="example_ids"
                                            value="{{ $example->id }}"
                                            :label="$example->title"
                                        />
                                        <div class="min-w-0 pt-0.5">
                                            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">
                                                {{ $example->criterion->number }} · {{ $example->criterion->name_sv ?? $example->criterion->name_en }}
                                            </flux:text>
                                            @if ($example->description)
                                                <flux:text size="sm" class="mt-1 line-clamp-2 text-zinc-600 dark:text-zinc-400">
                                                    {{ Str::limit(strip_tags($example->description), 160) }}
                                                </flux:text>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <flux:error name="example_ids" />
                            <flux:error name="example_ids.*" />
                        </div>
                    @endif
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
