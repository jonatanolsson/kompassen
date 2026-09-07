<div>
    <flux:heading size="xl">{{ __('WCAG Knowledge Base') }}</flux:heading>
    <flux:subheading>{{ __('Browse success criteria and manage global examples with code snippets and references.') }}</flux:subheading>

    <flux:separator variant="subtle" class="my-6" />

    <div class="mb-6 max-w-sm">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by number or name…') }}" icon="magnifying-glass" />
    </div>

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

                    {{-- Examples --}}
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <flux:heading size="sm">{{ __('Examples') }}</flux:heading>
                            @if ($criterion->examples->isNotEmpty())
                                <flux:badge size="sm" color="zinc">{{ $criterion->examples->count() }}</flux:badge>
                            @endif
                        </div>

                        @forelse ($criterion->examples as $example)
                            <flux:card class="!p-0 overflow-hidden">
                                <div class="flex items-center justify-between gap-2 px-4 py-3">
                                    <flux:heading size="sm">{{ $example->title }}</flux:heading>
                                    <div class="flex gap-1 shrink-0">
                                        <flux:button size="xs" variant="ghost" icon="pencil" wire:click="editExample('{{ $example->id }}')" />
                                        <flux:button size="xs" variant="ghost" icon="trash" wire:click="deleteExample('{{ $example->id }}')" wire:confirm="Delete this example?" />
                                    </div>
                                </div>

                                @if ($example->description)
                                    <flux:separator />
                                    <div class="px-4 py-3">
                                        <x-user-content :content="$example->description" />
                                    </div>
                                @endif

                                @if ($example->code_snippet)
                                    <flux:separator />
                                    <div class="flex items-center px-4 py-1.5 bg-zinc-100 dark:bg-zinc-800">
                                        <flux:badge size="sm" color="zinc">{{ $example->code_language ?? 'html' }}</flux:badge>
                                    </div>
                                    <pre class="px-4 py-3 overflow-x-auto text-sm bg-zinc-950 text-zinc-100"><code>{{ $example->code_snippet }}</code></pre>
                                @endif

                                @if ($example->url)
                                    <flux:separator />
                                    <div class="px-4 py-2.5">
                                        <flux:link href="{{ $example->url }}" target="_blank" class="text-sm">
                                            {{ $example->url_label ?: $example->url }}
                                        </flux:link>
                                    </div>
                                @endif
                            </flux:card>
                        @empty
                            <flux:callout icon="light-bulb" color="zinc">
                                <flux:callout.text>{{ __('No examples yet. Add the first one below.') }}</flux:callout.text>
                            </flux:callout>
                        @endforelse
                    </div>

                    <flux:separator />

                    {{-- Add / Edit form --}}
                    <div class="px-6 py-5">
                        <flux:heading size="sm" class="mb-4">
                            {{ $editingExampleId ? __('Edit Example') : __('Add Example') }}
                        </flux:heading>

                        <form wire:submit="saveExample" class="space-y-4">
                            <flux:field>
                                <flux:label>{{ __('Title') }}</flux:label>
                                <flux:input wire:model="exampleTitle" placeholder="{{ __('e.g. Button with visible label') }}" />
                                <flux:error name="exampleTitle" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Description') }}</flux:label>
                                <flux:textarea wire:model="exampleDescription" rows="3" placeholder="{{ __('Explain the example…') }}" />
                                <flux:error name="exampleDescription" />
                            </flux:field>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <flux:field>
                                        <flux:label>{{ __('Code Snippet') }}</flux:label>
                                        <flux:textarea wire:model="exampleCode" rows="5" class="font-mono text-sm" placeholder="{{ __('<button>Click me</button>') }}" />
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

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>{{ __('Reference URL') }}</flux:label>
                                    <flux:input wire:model="exampleUrl" type="url" placeholder="https://…" />
                                    <flux:error name="exampleUrl" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Link Label') }}</flux:label>
                                    <flux:input wire:model="exampleUrlLabel" placeholder="{{ __('MDN – button element') }}" />
                                </flux:field>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <flux:button type="submit" variant="primary">
                                    {{ $editingExampleId ? __('Update Example') : __('Add Example') }}
                                </flux:button>
                                @if ($editingExampleId)
                                    <flux:button type="button" variant="ghost" wire:click="cancelEdit">{{ __('Cancel') }}</flux:button>
                                @endif
                            </div>
                        </form>
                    </div>

                </flux:card>

            @else
                <flux:callout icon="book-open" color="zinc">
                    <flux:callout.heading>{{ __('Select a criterion') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Choose a success criterion from the list to view details and manage examples.') }}</flux:callout.text>
                </flux:callout>
            @endif
        </div>
    </div>
</div>
