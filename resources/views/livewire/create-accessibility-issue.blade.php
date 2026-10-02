
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 w-full">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Report Issue') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('Document an accessibility issue found during the audit.') }}</flux:text>
            </div>

            <form
                x-data="issueDiagramForm('submit', @js([
                    'renderFailed' => __('The Mermaid diagram could not be rendered. Check the diagram syntax and try again.'),
                    'unsupported' => __('This Mermaid diagram uses features that cannot be saved safely for reports, such as HTML labels, images or links. Simplify the diagram and try again.'),
                    'cryptoUnavailable' => __('Your browser cannot verify the rendered diagram. Open the application over HTTPS and try again.'),
                    'sourceChanged' => __('The Mermaid diagram changed while it was being rendered. Save again.'),
                ]))"
                x-on:submit.prevent="save"  enctype="multipart/form-data" class="space-y-8">
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

                        <details class="space-y-6 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" @if ($errors->any()) open @endif>
                            <summary class="cursor-pointer font-medium">{{ __('Additional evidence and user impact') }}</summary>
                            <div class="mt-6 space-y-6">
                        <flux:field>
                            <flux:label>{{ __('Impact') }}</flux:label>
                            <flux:editor
                                wire:model="impact"
                                placeholder="{{ __('Describe how the issue affects users...') }}"
                            />
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                {{ __('Optional. Explain the consequences for users; shown in reports.') }}
                            </flux:text>
                            <flux:error name="impact" />
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
                                {{ __('Optional Mermaid source. It is rendered in your browser when you save and stored as a sanitized SVG for reports.') }}
                            </flux:text>
                            <flux:error name="mermaid_diagram" />
                            <div
                                x-ref="diagramError"
                                x-show="diagramError"
                                x-cloak
                                tabindex="-1"
                                role="alert"
                                class="text-sm font-medium text-red-600 dark:text-red-400"
                                x-text="diagramError"
                            ></div>
                            <flux:text size="sm" x-show="rendering" x-cloak class="text-zinc-500 dark:text-zinc-400" aria-live="polite">
                                {{ __('Rendering diagram…') }}
                            </flux:text>
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
                        </details>
                    </div>
                </div>

                <flux:separator />

                <!-- Location & Classification -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Classification') }}</flux:heading>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Page/Service') }}</flux:label>
                            <flux:select wire:model.live="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page->id }}">
                                        {{ $page->name }}{{ ($page->resource_type ?? 'page') === 'service' ? ' ('.__('Service').')' : '' }}
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="page_id" />
                            @if ($pages->firstWhere('id', $page_id)?->scope === 'out_of_scope')
                                <flux:callout color="amber" class="mt-3">
                                    <flux:callout.text>{{ __('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.') }}</flux:callout.text>
                                </flux:callout>
                            @endif
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

                        <flux:radio.group
                            variant="buttons"
                            wire:model="severity"
                            label="{{ __('Severity') }}"
                            required
                        >
                            <flux:radio value="critical" label="{{ __('Critical issue severity') }}" />
                            <flux:radio value="major" label="{{ __('Major issue severity') }}" />
                            <flux:radio value="moderate" label="{{ __('Moderate issue severity') }}" />
                            <flux:radio value="minor" label="{{ __('Minor issue severity') }}" />
                        </flux:radio.group>
                        <flux:error name="severity" />

                        <flux:radio.group
                            variant="buttons"
                            wire:model="difficulty"
                            label="{{ __('Difficulty to Fix') }}"
                            required
                        >
                            <flux:radio value="easy" label="{{ __('Easy') }}" />
                            <flux:radio value="medium" label="{{ __('Medium') }}" />
                            <flux:radio value="hard" label="{{ __('Hard') }}" />
                        </flux:radio.group>
                        <flux:error name="difficulty" />

                    </div>
                    <flux:text size="sm" class="mt-4 text-zinc-600 dark:text-zinc-400">{{ __('New issues start open. Change status with a resolution note after saving.') }}</flux:text>
                </div>

                <flux:separator />

                <details class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" @if ($errors->any()) open @endif>
                    <summary class="cursor-pointer font-medium">{{ __('WCAG criteria and examples') }}</summary>
                    <div class="mt-5 space-y-6">
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
                                    wire:model.live="wcag_criteria"
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

                    </div>
                </details>

                <!-- Actions -->
                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" x-bind:disabled="rendering" variant="primary">{{ __('Report Issue') }}</flux:button>
                    <flux:button href="{{ route('accessibility-projects.show', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
