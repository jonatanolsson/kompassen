<div>
    <flux:heading size="xl">{{ __('WCAG Knowledge Base') }}</flux:heading>
    <flux:subheading>{{ __('Browse success criteria and manage global examples with code snippets and references.') }}</flux:subheading>

    <flux:separator variant="subtle" class="my-6" />

    <div class="mb-6 max-w-sm">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by number or name…') }}" icon="magnifying-glass" />
    </div>

    <flux:card class="mb-6 space-y-4">
        <div>
            <flux:heading size="sm">{{ __('Guidance and standards') }}</flux:heading>
            <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('Official guidance and standards for digital accessibility.') }}
            </flux:text>
        </div>
        <div class="flex flex-wrap gap-x-6 gap-y-2">
            <flux:link href="https://www.digg.se/webbriktlinjer/alla-webbriktlinjer" target="_blank" icon-trailing="arrow-top-right-on-square">
                {{ __('DIGG Web Guidelines') }}
            </flux:link>
            <flux:link href="https://www.w3.org/TR/WCAG21/" target="_blank" icon-trailing="arrow-top-right-on-square">
                {{ __('W3C WCAG 2.1 standard') }}
            </flux:link>
            <flux:link href="https://www.w3.org/WAI/WCAG21/Understanding/" target="_blank" icon-trailing="arrow-top-right-on-square">
                {{ __('W3C Understanding WCAG 2.1') }}
            </flux:link>
        </div>
    </flux:card>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Criteria list --}}
        <div class="lg:col-span-2 space-y-5">
            @forelse ($this->criteria as $principle => $group)
                <div>
                    <flux:text size="xs" class="font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 px-1 mb-2">
                    {{ __('Principle') }} {{ $principle }}
                    </flux:text>
                    <div class="space-y-0.5">
                        @foreach ($group as $criterion)
                            <button
                                wire:click="selectCriterion('{{ $criterion->id }}')"
                                @class([
                                    'w-full text-left px-3 py-2.5 rounded-lg text-sm transition-colors',
                                    'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900' => $selectedCriterionId === $criterion->id,
                                    'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800' => $selectedCriterionId !== $criterion->id,
                                ])
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <span class="font-mono font-semibold shrink-0">{{ $criterion->number }}</span>
                                        <span class="truncate">{{ $criterion->name_sv ?? $criterion->name_en }}</span>
                                    </span>
                                    <div class="flex items-center gap-1 shrink-0">
                                        @if ($criterion->examples_count > 0)
                                            <flux:badge size="sm" color="zinc">{{ $criterion->examples_count }}</flux:badge>
                                        @endif
                                        <flux:badge size="sm" color="{{ $criterion->level === 'A' ? 'zinc' : ($criterion->level === 'AA' ? 'blue' : 'purple') }}">
                                            {{ $criterion->level }}
                                        </flux:badge>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <flux:callout icon="magnifying-glass" color="zinc">
                    <flux:callout.text>{{ __('No criteria match your search.') }}</flux:callout.text>
                </flux:callout>
            @endforelse
        </div>

        {{-- Detail panel --}}
        <div class="lg:col-span-3">
            @if ($this->selectedCriterion)
                @php $criterion = $this->selectedCriterion; @endphp

                <flux:card class="space-y-0 !p-0 overflow-hidden">

                    {{-- Criterion header --}}
                    <div class="px-6 py-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-mono text-sm text-zinc-500 dark:text-zinc-400">{{ $criterion->number }}</span>
                                    <flux:badge color="{{ $criterion->level === 'A' ? 'zinc' : ($criterion->level === 'AA' ? 'blue' : 'purple') }}">
                                        {{ __('Level') }} {{ $criterion->level }}
                                    </flux:badge>
                                </div>
                                <flux:heading>{{ $criterion->name_sv ?? $criterion->name_en }}</flux:heading>
                                @if ($criterion->name_sv !== $criterion->name_en)
                                    <flux:subheading>{{ $criterion->name_sv }}</flux:subheading>
                                @endif
                                <flux:text size="sm" class="mt-3 text-zinc-600 dark:text-zinc-400">{{ $criterion->description_sv ?? $criterion->description_en }}</flux:text>
                            </div>
                            @if ($criterion->url)
                                <flux:button href="{{ $criterion->url }}" target="_blank" size="sm" variant="ghost" icon-trailing="arrow-top-right-on-square">
                                    {{ __('W3C') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>

                    <flux:separator />

                    {{-- Related resources --}}
                    @if ($criterion->relatedResources->isNotEmpty())
                        <div class="px-6 py-5 space-y-3">
                            <flux:heading size="sm">{{ __('Related Resources') }}</flux:heading>

                            <div class="space-y-2">
                                @foreach ($criterion->relatedResources as $resource)
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <flux:link href="{{ $resource->url }}" target="_blank" icon-trailing="arrow-top-right-on-square">
                                                {{ $resource->title_sv ?: $resource->title_en }}
                                            </flux:link>
                                            @if ($resource->description_sv || $resource->description_en)
                                                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                                                    {{ $resource->description_sv ?: $resource->description_en }}
                                                </flux:text>
                                            @endif
                                        </div>
                                        <flux:badge size="sm" color="zinc" class="shrink-0">
                                            {{ __($resource->type === 'wai_failure' ? 'WAI Failure' : ($resource->type === 'project_issue' ? 'Project Issue' : 'Web Guidelines')) }}
                                        </flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <flux:separator />
                    @endif

                    {{-- Examples --}}
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm">{{ __('Examples') }}</flux:heading>
                                @if ($criterion->examples->isNotEmpty())
                                    <flux:badge size="sm" color="zinc">{{ $criterion->examples->count() }}</flux:badge>
                                @endif
                            </div>
                            <flux:modal.trigger name="example-form-modal">
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    icon="plus"
                                    wire:click="startCreatingExample"
                                >
                                    {{ __('Add Example') }}
                                </flux:button>
                            </flux:modal.trigger>
                        </div>

                        @if ($criterion->examples->isNotEmpty())
                            <div class="divide-y divide-zinc-200 overflow-hidden rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                                @foreach ($criterion->examples as $example)
                                    <article wire:key="wcag-example-{{ $example->id }}" class="space-y-4 p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <flux:heading size="sm">{{ $example->title }}</flux:heading>
                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    @if ($example->code_snippet)
                                                        <flux:badge size="sm" color="zinc">{{ strtoupper($example->code_language ?? 'html') }}</flux:badge>
                                                    @endif
                                                    @if ($example->referenceLinks->isNotEmpty() || $example->url)
                                                        <flux:badge size="sm" color="blue">
                                                            {{ __('Reference links') }}
                                                        </flux:badge>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-1">
                                                <flux:modal.trigger name="example-form-modal">
                                                    <flux:button
                                                        size="xs"
                                                        variant="ghost"
                                                        icon="pencil"
                                                        wire:click="editExample('{{ $example->id }}')"
                                                    >
                                                        {{ __('Edit') }}
                                                    </flux:button>
                                                </flux:modal.trigger>
                                                <flux:button
                                                    size="xs"
                                                    variant="ghost"
                                                    icon="trash"
                                                    wire:click="deleteExample('{{ $example->id }}')"
                                                    wire:confirm="{{ __('Delete this example?') }}"
                                                >
                                                    {{ __('Delete') }}
                                                </flux:button>
                                            </div>
                                        </div>

                                        @if ($example->description)
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                <x-user-content :content="$example->description" />
                                            </div>
                                        @endif

                                        @if ($example->code_snippet)
                                            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                                <div class="flex items-center justify-between bg-zinc-100 px-3 py-1.5 dark:bg-zinc-800">
                                                    <flux:text size="xs" class="font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                        {{ __('Code Snippet') }}
                                                    </flux:text>
                                                    <flux:badge size="sm" color="zinc">{{ $example->code_language ?? 'html' }}</flux:badge>
                                                </div>
                                                <pre class="max-h-56 overflow-auto bg-zinc-950 px-4 py-3 text-sm text-zinc-100"><code>{{ $example->code_snippet }}</code></pre>
                                            </div>
                                        @endif

                                        @if ($example->referenceLinks->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach ($example->referenceLinks as $link)
                                                    <flux:link href="{{ $link->url }}" target="_blank" class="flex text-sm" icon-trailing="arrow-top-right-on-square">
                                                        {{ $link->label ?: $link->url }}
                                                    </flux:link>
                                                @endforeach
                                            </div>
                                        @elseif ($example->url)
                                            <flux:link href="{{ $example->url }}" target="_blank" class="flex text-sm" icon-trailing="arrow-top-right-on-square">
                                                {{ $example->url_label ?: $example->url }}
                                            </flux:link>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <flux:callout icon="light-bulb" color="zinc">
                                <flux:callout.text>{{ __('No examples yet. Add the first one below.') }}</flux:callout.text>
                            </flux:callout>
                        @endif
                    </div>

                </flux:card>

                <flux:modal name="example-form-modal" class="w-full max-w-2xl">
                    <form wire:submit="saveExample" class="space-y-6">
                        <div>
                            <flux:heading size="lg">
                                {{ $editingExampleId ? __('Edit Example') : __('Add Example') }}
                            </flux:heading>
                            <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                                {{ $criterion->number }} · {{ $criterion->name_sv ?? $criterion->name_en }}
                            </flux:text>
                        </div>

                        <flux:separator />

                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>{{ __('Title') }}</flux:label>
                                <flux:input wire:model="exampleTitle" placeholder="{{ __('e.g. Button with visible label') }}" autofocus />
                                <flux:error name="exampleTitle" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Description') }}</flux:label>
                                <flux:textarea wire:model="exampleDescription" rows="4" placeholder="{{ __('Explain the example…') }}" />
                                <flux:error name="exampleDescription" />
                            </flux:field>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <flux:field>
                                        <flux:label>{{ __('Code Snippet') }}</flux:label>
                                        <flux:textarea wire:model="exampleCode" rows="7" class="font-mono text-sm" placeholder="{{ __('<button>Click me</button>') }}" />
                                        <flux:error name="exampleCode" />
                                    </flux:field>
                                </div>
                                <flux:field>
                                    <flux:label>{{ __('Language') }}</flux:label>
                                    <flux:select wire:model="exampleCodeLanguage">
                                        <option value="html">{{ __('HTML') }}</option>
                                        <option value="css">{{ __('CSS') }}</option>
                                        <option value="javascript">{{ __('JavaScript') }}</option>
                                        <option value="jsx">{{ __('JSX / React') }}</option>
                                        <option value="vue">{{ __('Vue') }}</option>
                                        <option value="php">{{ __('PHP') }}</option>
                                        <option value="blade">{{ __('Blade') }}</option>
                                        <option value="aria">{{ __('ARIA') }}</option>
                                    </flux:select>
                                </flux:field>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-4">
                                    <flux:label>{{ __('Reference links') }}</flux:label>
                                    <flux:button type="button" size="sm" variant="ghost" icon="plus" wire:click="addExampleLink">
                                        {{ __('Add reference link') }}
                                    </flux:button>
                                </div>

                                @forelse ($exampleLinks as $index => $link)
                                    <div wire:key="example-link-{{ $index }}" class="grid grid-cols-1 items-start gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                                        <flux:field>
                                            <flux:label>{{ __('Reference URL') }}</flux:label>
                                            <flux:input wire:model="exampleLinks.{{ $index }}.url" type="url" placeholder="{{ __('https://…') }}" />
                                            <flux:error name="exampleLinks.{{ $index }}.url" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label>{{ __('Link label') }}</flux:label>
                                            <flux:input wire:model="exampleLinks.{{ $index }}.label" placeholder="{{ __('MDN – button element') }}" />
                                            <flux:error name="exampleLinks.{{ $index }}.label" />
                                        </flux:field>
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            class="mt-6"
                                            wire:click="removeExampleLink({{ $index }})"
                                        >
                                            {{ __('Remove') }}
                                        </flux:button>
                                    </div>
                                @empty
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                        {{ __('No reference links added yet.') }}
                                    </flux:text>
                                @endforelse
                            </div>
                        </div>

                        <flux:separator />

                        <div class="flex justify-end gap-3">
                            <flux:modal.close>
                                <flux:button type="button" variant="ghost" wire:click="cancelEdit">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button type="submit" variant="primary">
                                {{ $editingExampleId ? __('Update Example') : __('Add Example') }}
                            </flux:button>
                        </div>
                    </form>
                </flux:modal>

            @else
                <flux:callout icon="book-open" color="zinc">
                    <flux:callout.heading>{{ __('Select a criterion') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Choose a success criterion from the list to view details and manage examples.') }}</flux:callout.text>
                </flux:callout>
            @endif
        </div>
    </div>
</div>
