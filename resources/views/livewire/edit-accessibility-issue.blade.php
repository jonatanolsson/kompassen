
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 w-full">
            <div class="mb-8">
                <flux:heading level="1" class="text-2xl font-semibold tracking-tight">{{ __('Edit Issue') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                    {{ __('Update the issue details and metadata.') }}
                </flux:text>
            </div>

            <form
                x-data="issueDiagramForm('update', @js([
                    'renderFailed' => __('The Mermaid diagram could not be rendered. Check the diagram syntax and try again.'),
                    'unsupported' => __('This Mermaid diagram uses features that cannot be saved safely for reports, such as HTML labels, images or links. Simplify the diagram and try again.'),
                    'cryptoUnavailable' => __('Your browser cannot verify the rendered diagram. Open the application over HTTPS and try again.'),
                    'sourceChanged' => __('The Mermaid diagram changed while it was being rendered. Save again.'),
                ]))"
                x-on:submit.prevent="save"  enctype="multipart/form-data" class="space-y-8" wire:key="issue-edit-{{ $this->issueId }}">
                <!-- Title Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Issue Details') }}</flux:heading>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Issue Title') }}</flux:label>
                            <flux:input
                                type="text"
                                wire:model.defer="title"
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
                            <flux:text size="sm" class="text-zinc-500 mt-2">{{ __('Supports Markdown formatting') }}</flux:text>
                            <flux:error name="description" />
                        </flux:field>

                        <flux:accordion transition>
                            <flux:accordion.item
                                :heading="__('Additional evidence and user impact')"
                                :expanded="$impact !== '' || $mermaid_diagram !== '' || count($databaseAttachments) > 0 || $errors->any()"
                            >
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
                            </div>
                        </flux:accordion.item>
                    </flux:accordion>
                    </div>
                </div>

                <flux:separator />

                <!-- Images Section -->
                <flux:accordion transition>
                    <flux:accordion.item
                        :heading="__('Images')"
                        :expanded="true"
                    >
                <div class="py-4">

                    <flux:field class="mb-4">
                        <flux:label>{{ __('Upload new images') }}</flux:label>
                        <flux:file-upload wire:model="attachments" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                            <flux:file-upload.dropzone
                                heading="{{ __('Choose images or drop them here') }}"
                                text="{{ __('JPG, PNG, GIF or WebP. Max 5 MB per image.') }}"
                            />
                        </flux:file-upload>
                        <flux:error name="attachments" />
                    </flux:field>

                    @if (count($databaseAttachments) > 0 || count($this->attachments) > 0)
                        <div class="space-y-3">
                            <flux:heading level="3" class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Current Images') }}</flux:heading>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                <!-- Existing database attachments -->
                                @foreach ($databaseAttachments as $attachment)
                                    <div class="flex min-w-0 flex-col gap-2">
                                        <flux:modal.trigger name="issue-attachment-lightbox-{{ $loop->index }}">
                                            <button
                                                type="button"
                                                class="relative aspect-square w-full overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800"
                                            >
                                                <img
                                                    src="{{ asset('storage/' . $attachment['path']) }}"
                                                    alt="{{ $attachment['original_filename'] }}"
                                                    class="h-full w-full object-cover transition-transform duration-200 hover:scale-105"
                                                />
                                            </button>
                                        </flux:modal.trigger>
                                        <flux:modal.trigger name="delete-issue-attachment-{{ $attachment['id'] }}">
                                            <flux:button type="button" variant="danger" size="xs" icon="trash" class="w-full">
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </flux:modal.trigger>

                                        <flux:modal name="delete-issue-attachment-{{ $attachment['id'] }}" class="md:w-[28rem]">
                                            <div class="space-y-6">
                                                <div>
                                                    <flux:heading level="2">{{ __('Delete this image?') }}</flux:heading>
                                                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                                                </div>

                                                <div class="flex justify-end gap-2">
                                                    <flux:modal.close>
                                                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                                                    </flux:modal.close>
                                                    <flux:button type="button" variant="danger" wire:click="deleteAttachment('{{ $attachment['id'] }}')">
                                                        {{ __('Delete') }}
                                                    </flux:button>
                                                </div>
                                            </div>
                                        </flux:modal>

                                        <!-- Image Lightbox Modal for each attachment -->
                                        <flux:modal name="issue-attachment-lightbox-{{ $loop->index }}" class="w-auto max-w-4xl">
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between">
                                                    <flux:heading level="2">{{ $attachment['original_filename'] }}</flux:heading>
                                                    <flux:modal.close />
                                                </div>
                                                <flux:separator />

                                                <div class="flex justify-center bg-zinc-900 rounded-lg p-4">
                                                    <img
                                                        src="{{ asset('storage/' . $attachment['path']) }}"
                                                        alt="{{ $attachment['original_filename'] }}"
                                                        class="max-h-[70vh] object-contain"
                                                    />
                                                </div>
                                            </div>
                                        </flux:modal>
                                    </div>
                                @endforeach

                                <!-- Newly uploaded temporary files (preview) -->
                                @foreach ($this->attachments as $uploadedFile)
                                    <div class="flex min-w-0 flex-col gap-2">
                                        <div class="relative aspect-square overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                            <img
                                                src="{{ $uploadedFile->temporaryUrl() }}"
                                                alt="{{ $uploadedFile->getClientOriginalName() }}"
                                                class="w-full h-full object-cover"
                                            />
                                            <div class="absolute inset-0 flex items-center justify-center bg-blue-950/35">
                                                <span class="rounded bg-blue-600/85 px-2 py-1 text-xs font-semibold text-white">{{ __('New') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                    </flux:accordion.item>
                </flux:accordion>

                <flux:separator />

                <!-- Location Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Location') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Page/Service') }}</flux:label>
                            <flux:select wire:model.live="page_id">
                                <option value="">{{ __('Not specific to a page') }}</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page['id'] }}">
                                        {{ $page['name'] }}{{ ($page['resource_type'] ?? 'page') === 'service' ? ' ('.__('Service').')' : '' }}
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="page_id" />
                            @if ((collect($pages)->firstWhere('id', $page_id)['scope'] ?? null) === 'out_of_scope')
                                <flux:callout color="amber" class="mt-3">
                                    <flux:callout.text>{{ __('This page is outside the audit scope. Its issues remain in the project but are excluded from reports.') }}</flux:callout.text>
                                </flux:callout>
                            @endif
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Component Area') }}</flux:label>
                            <flux:input
                                type="text"
                                wire:model.defer="component_area"
                                placeholder="{{ __('e.g., header, footer, main-nav') }}"
                            />
                            <flux:error name="component_area" />
                        </flux:field>
                    </div>
                </div>

                <flux:separator />

                <!-- Classification Section -->
                <div class="py-4">
                    <flux:heading level="2" class="mb-4 text-lg font-semibold">{{ __('Classification') }}</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <flux:text size="sm" class="mt-4 text-zinc-600 dark:text-zinc-400">{{ __('Change issue status using the resolution action on the issue page.') }}</flux:text>
                </div>

                <flux:separator />

                <flux:accordion transition>
                    <flux:accordion.item
                        :heading="__('WCAG criteria and examples')"
                        :expanded="count($wcag_criteria) > 0 || count($example_ids) > 0 || $errors->any()"
                    >
                    <div class="mt-5 space-y-6">
                <!-- WCAG Criteria -->
                <div class="py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading level="2" class="mb-2 text-lg font-semibold">{{ __('WCAG Success Criteria') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                {{ __('Select the criteria this issue relates to.') }}
                            </flux:text>
                        </div>
                        @if (count($wcag_criteria) > 0)
                            <flux:badge color="blue">{{ count($wcag_criteria) }}</flux:badge>
                        @endif
                    </div>

                    <div class="mt-4 max-h-80 space-y-2 overflow-y-auto rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                        @forelse ($availableCriteria as $criterion)
                            <div wire:key="edit-issue-criterion-{{ $criterion['id'] }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <flux:checkbox
                                    wire:model.live="wcag_criteria"
                                    value="{{ $criterion['id'] }}"
                                    :label="$criterion['number'].' — '.($criterion['name_sv'] ?? $criterion['name_en'])"
                                />
                                <flux:text size="xs" class="ml-7 mt-0.5 text-zinc-500 dark:text-zinc-400">
                                    {{ __('Level') }} {{ $criterion['level'] }}
                                </flux:text>
                            </div>
                        @empty
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('No WCAG criteria available') }}</flux:text>
                        @endforelse
                    </div>
                    <flux:error name="wcag_criteria" />
                    <flux:error name="wcag_criteria.*" />
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
                                    <div wire:key="edit-issue-example-{{ $example->id }}" class="flex items-start gap-3 rounded-lg px-3 py-2 transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
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

                <flux:separator />

                    </div>
                    </flux:accordion.item>
                </flux:accordion>

                <!-- Actions -->
                <div class="flex gap-3 pt-6">
                    <flux:button type="submit" x-bind:disabled="rendering" variant="primary">{{ __('Update Issue') }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancel">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
