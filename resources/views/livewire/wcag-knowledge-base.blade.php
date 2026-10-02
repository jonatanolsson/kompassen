<div class="product-page space-y-6">
    <div class="product-page-header">
        <div>
            <flux:heading size="xl">{{ __('Knowledge Base') }}</flux:heading>
            <flux:subheading>{{ __('WCAG criteria and resources are read-only. Team editors may manage their own examples.') }}</flux:subheading>
        </div>
        @if (auth()->user()->isTeamOwner())
            <flux:button href="{{ route('team.knowledge-editors.index') }}" variant="outline">{{ __('Knowledge editors') }}</flux:button>
        @endif
    </div>

    <div class="product-section grid max-w-3xl grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_14rem]">
        <flux:input
            id="knowledge-search"
            wire:model.live.debounce.300ms="search"
            :loading="false"
            placeholder="{{ __('Search by number, standard, or name…') }}"
            icon="magnifying-glass"
        />
        <flux:select wire:model.live="standardFilter">
            <option value="">{{ __('All standards') }}</option>
            @foreach ($this->standards as $standard)
                <option value="{{ $standard }}">{{ $standard }}</option>
            @endforeach
        </flux:select>
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
            <flux:link href="https://www.etsi.org/deliver/etsi_en/301500_301599/301549/" target="_blank" icon-trailing="arrow-top-right-on-square">
                {{ __('EN 301 549') }}
            </flux:link>
        </div>
    </flux:card>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Criteria list --}}
        <div class="lg:col-span-2 space-y-5">
            @forelse ($this->criteria as $standard => $standardGroup)
                <div class="space-y-3">
                    <flux:text size="xs" class="px-1 font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                        {{ $standard }}
                    </flux:text>
                    @foreach ($standardGroup->groupBy(fn ($criterion) => explode('.', $criterion->number)[0]) as $principle => $group)
                        <div>
                            <flux:text size="xs" class="mb-2 px-1 font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                {{ __('Principle') }} {{ $principle }}
                            </flux:text>
                            <div class="space-y-0.5">
                                @foreach ($group as $criterion)
                                    <flux:button
                                        type="button"
                                        wire:click="selectCriterion('{{ $criterion->id }}')"
                                        variant="{{ $selectedCriterionId === $criterion->id ? 'primary' : 'ghost' }}"
                                        class="w-full justify-start text-left text-sm"
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="flex min-w-0 items-center gap-2">
                                                <span class="shrink-0 font-mono font-semibold">{{ $criterion->number }}</span>
                                                <span class="truncate">{{ $criterion->name_sv ?? $criterion->name_en }}</span>
                                            </span>
                                            <div class="flex shrink-0 items-center gap-1">
                                                @if ($criterion->examples_count > 0)
                                                    <flux:badge size="sm" color="zinc">{{ $criterion->examples_count }}</flux:badge>
                                                @endif
                                                <span @class([
                                                    'rounded-full px-2 py-0.5 text-xs font-semibold',
                                                    'bg-white/20 text-white' => $selectedCriterionId === $criterion->id,
                                                    'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-100' => $selectedCriterionId !== $criterion->id && $criterion->level === 'A',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-100' => $selectedCriterionId !== $criterion->id && $criterion->level === 'AA',
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-100' => $selectedCriterionId !== $criterion->id && $criterion->level !== 'A' && $criterion->level !== 'AA',
                                                ])>
                                                    {{ $criterion->level }}
                                                </span>
                                            </div>
                                        </div>
                                    </flux:button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
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
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="font-mono text-sm text-zinc-500 dark:text-zinc-400">{{ $criterion->number }}</span>
                                    <flux:badge color="blue">
                                        {{ $criterion->standard }} · {{ __('Level') }} {{ $criterion->level }}
                                    </flux:badge>
                                </div>
                                <flux:heading>{{ $criterion->name_sv ?? $criterion->name_en }}</flux:heading>
                                @if ($criterion->name_sv !== $criterion->name_en)
                                    <flux:subheading>{{ $criterion->name_sv }}</flux:subheading>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                @if ($criterion->url)
                                    <flux:button href="{{ $criterion->url }}" target="_blank" size="sm" variant="ghost" icon-trailing="arrow-top-right-on-square">
                                        {{ __('Open source link') }}
                                    </flux:button>
                                @endif
                                @can('update', $criterion)
                                <flux:modal.trigger name="criterion-form-modal">
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editCriterion('{{ $criterion->id }}')">
                                        {{ __('Edit') }}
                                    </flux:button>
                                </flux:modal.trigger>
                                <flux:modal.trigger name="delete-criterion-{{ $criterion->id }}">
                                    <flux:button type="button" size="sm" variant="ghost" icon="trash">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>
                                <flux:modal name="delete-criterion-{{ $criterion->id }}" class="md:w-[28rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading level="2">{{ __('Delete this knowledge base entry?') }}</flux:heading>
                                            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:button type="button" variant="danger" wire:click="deleteCriterion('{{ $criterion->id }}')">
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                                @endcan
                            </div>
                        </div>
                        <flux:text size="sm" class="mt-4 block w-full text-zinc-600 dark:text-zinc-400">
                            {{ $criterion->description_sv ?? $criterion->description_en }}
                        </flux:text>
                    </div>

                    <flux:separator />

                    {{-- Related resources --}}
                    <div class="space-y-3 px-6 py-5">
                        <div class="flex items-center justify-between gap-4">
                            <flux:heading size="sm">{{ __('Related Resources') }}</flux:heading>
                            @can('update', $criterion)
                            <flux:modal.trigger name="resource-form-modal">
                                <flux:button size="sm" variant="primary" icon="plus" wire:click="startCreatingResource">
                                    {{ __('Add Resource') }}
                                </flux:button>
                            </flux:modal.trigger>
                            @endcan
                        </div>

                        @if ($criterion->relatedResources->isNotEmpty())
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($criterion->relatedResources as $resource)
                                    <div class="flex items-center justify-between gap-3 py-2 first:pt-0 last:pb-0">
                                        <div class="min-w-0 flex-1">
                                            @if ($resource->url)
                                                <flux:link href="{{ $resource->url }}" target="_blank" icon-trailing="arrow-top-right-on-square" class="block min-w-0 max-w-full whitespace-normal break-words text-sm">
                                                    {{ $resource->title_sv ?: $resource->title_en }}
                                                </flux:link>
                                            @else
                                                <flux:text class="whitespace-normal break-words text-sm font-medium">{{ $resource->title_sv ?: $resource->title_en }}</flux:text>
                                            @endif
                                            @if ($resource->description_sv || $resource->description_en)
                                                <flux:text size="sm" class="mt-0.5 truncate text-zinc-600 dark:text-zinc-400">
                                                    {{ $resource->description_sv ?: $resource->description_en }}
                                                </flux:text>
                                            @endif
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            <flux:badge size="sm" color="zinc">
                                                {{ $this->resourceTypeOptions()[$resource->type] ?? $resource->type }}
                                            </flux:badge>
                                            <flux:dropdown align="end">
                                                <flux:button
                                                    variant="subtle"
                                                    size="sm"
                                                    icon="ellipsis-vertical"
                                                    aria-label="{{ __('Actions') }}"
                                                />
                                                <flux:menu>
                                                    @if ($resource->url)
                                                        <flux:menu.item href="{{ $resource->url }}" target="_blank" icon="arrow-top-right-on-square">
                                                            {{ __('Open resource') }}
                                                        </flux:menu.item>
                                                        <flux:menu.separator />
                                                    @endif
                                                    @can('update', $criterion)
                                                    <flux:modal.trigger name="resource-form-modal">
                                                        <flux:menu.item as="button" icon="pencil" wire:click="editResource('{{ $resource->id }}')">
                                                            {{ __('Edit') }}
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                    @endcan
                                                    @can('update', $criterion)
                                                    <flux:modal.trigger name="delete-resource-{{ $resource->id }}">
                                                        <flux:menu.item as="button" icon="trash" variant="danger">
                                                            {{ __('Delete') }}
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                    @endcan
                                                </flux:menu>
                                            </flux:dropdown>
                                            @can('update', $criterion)
                                            <flux:modal name="delete-resource-{{ $resource->id }}" class="md:w-[28rem]">
                                                <div class="space-y-6">
                                                    <div>
                                                        <flux:heading level="2">{{ __('Delete this related resource?') }}</flux:heading>
                                                        <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <flux:modal.close>
                                                            <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                                                        </flux:modal.close>
                                                        <flux:button type="button" variant="danger" wire:click="deleteResource('{{ $resource->id }}')">
                                                            {{ __('Delete') }}
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            </flux:modal>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <flux:callout icon="link" color="zinc">
                                <flux:callout.text>{{ __('No related resources yet. Add W3C, DIGG, or project guidance.') }}</flux:callout.text>
                            </flux:callout>
                        @endif
                    </div>

                    <flux:separator />

                    {{-- Examples --}}
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm">{{ __('Examples') }}</flux:heading>
                                @if ($criterion->examples->isNotEmpty())
                                    <flux:badge size="sm" color="zinc">{{ $criterion->examples->count() }}</flux:badge>
                                @endif
                            </div>
                            @can('create', \App\Models\WcagCriterionExample::class)
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
                            @endcan
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
                                                @can('update', $example)
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
                                                <flux:modal.trigger name="delete-example-{{ $example->id }}">
                                                    <flux:button type="button" size="xs" variant="ghost" icon="trash">
                                                        {{ __('Delete') }}
                                                    </flux:button>
                                                </flux:modal.trigger>
                                                <flux:modal name="delete-example-{{ $example->id }}" class="md:w-[28rem]">
                                                    <div class="space-y-6">
                                                        <div>
                                                            <flux:heading level="2">{{ __('Delete this example?') }}</flux:heading>
                                                            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                                                        </div>
                                                        <div class="flex justify-end gap-2">
                                                            <flux:modal.close>
                                                                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                                                            </flux:modal.close>
                                                            <flux:button type="button" variant="danger" wire:click="deleteExample('{{ $example->id }}')">
                                                                {{ __('Delete') }}
                                                            </flux:button>
                                                        </div>
                                                    </div>
                                                </flux:modal>
                                                @endcan
                                            </div>
                                        </div>

                                        @if ($example->description)
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                <x-user-content :content="$example->description" />
                                            </div>
                                        @endif

                                        @if ($example->attachments->isNotEmpty())
                                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                                @foreach ($example->attachments as $attachment)
                                                    <a
                                                        href="{{ asset('storage/'.$attachment->path) }}"
                                                        target="_blank"
                                                        class="group overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
                                                    >
                                                        <img
                                                            src="{{ asset('storage/'.$attachment->path) }}"
                                                            alt="{{ $attachment->alt_text ?: $attachment->original_filename }}"
                                                            class="aspect-video w-full object-cover transition duration-200 group-hover:scale-105"
                                                        />
                                                        <span class="block truncate px-2 py-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                                                            {{ $attachment->original_filename }}
                                                        </span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($example->code_snippet)
                                            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                                <div class="flex items-center justify-between bg-zinc-100 px-3 py-1.5 dark:bg-zinc-800">
                                                    <flux:text size="xs" class="font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                        {{ $example->code_language === 'mermaid' ? __('Rendered diagram') : __('Code Snippet') }}
                                                    </flux:text>
                                                    <flux:badge size="sm" color="zinc">{{ $example->code_language ?? 'html' }}                                                    </flux:badge>
                                                </div>
                                                @if ($example->code_language === 'mermaid')
                                                    <div
                                                        id="mermaid-{{ $example->id }}"
                                                        data-mermaid-diagram
                                                        data-mermaid-source="{{ $example->code_snippet }}"
                                                        role="img"
                                                        aria-label="{{ $example->title }}"
                                                        class="mermaid min-h-24 overflow-auto bg-white px-4 py-3 text-sm text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100"
                                                    ></div>
                                                @else
                                                    <pre class="max-h-56 overflow-auto bg-zinc-950 px-4 py-3 text-sm text-zinc-100"><code>{{ $example->code_snippet }}</code></pre>
                                                @endif
                                                @if ($example->code_language === 'mermaid')
                                                    <details class="border-t border-zinc-200 dark:border-zinc-700">
                                                        <summary class="cursor-pointer px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                            {{ __('View source code') }}
                                                        </summary>
                                                        <pre class="max-h-56 overflow-auto border-t border-zinc-200 bg-zinc-950 px-4 py-3 text-sm text-zinc-100 dark:border-zinc-700"><code>{{ $example->code_snippet }}</code></pre>
                                                    </details>
                                                @endif
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

                @can('create', \App\Models\WcagCriterionExample::class)
                <flux:modal name="example-form-modal" class="w-full max-w-2xl">
                    <form
                        x-data="issueDiagramForm('saveExample', {
                            renderFailed: @js(__('The diagram could not be rendered. Check the Mermaid code and try again.')),
                            unsupported: @js(__('The diagram contains unsupported content. Remove external links, images, or HTML and try again.')),
                            cryptoUnavailable: @js(__('Secure diagram verification is unavailable. Use HTTPS or localhost and try again.')),
                            sourceChanged: @js(__('The diagram changed during rendering. Save again.'))
                        }, { source: 'exampleCode', svg: 'exampleSvg', hash: 'exampleSourceHash', language: 'exampleCodeLanguage' })"
                        x-on:submit.prevent="save()"
                        enctype="multipart/form-data"
                        class="space-y-6"
                    >
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
                                        <option value="mermaid">{{ __('Mermaid diagram') }}</option>
                                    </flux:select>
                                    <flux:error name="exampleCodeLanguage" />
                                </flux:field>
                            </div>
                            <p x-show="diagramError" x-text="diagramError" x-ref="diagramError" tabindex="-1" role="alert" class="text-sm text-red-600 dark:text-red-400"></p>

                            <div class="space-y-3">
                                <div>
                                    <flux:label>{{ __('Image attachments') }}</flux:label>
                                    <flux:text size="sm" class="mt-1 text-zinc-500 dark:text-zinc-400">
                                        {{ __('Add screenshots or diagrams to illustrate the example. Max 5 MB per image.') }}
                                    </flux:text>
                                </div>
                                <flux:file-upload wire:model="exampleAttachments" multiple accept="image/*">
                                    <flux:file-upload.dropzone
                                        heading="{{ __('Choose images or drop them here') }}"
                                        text="{{ __('JPG, PNG, GIF or WebP. Max 5 MB per image.') }}"
                                    />
                                </flux:file-upload>
                                <flux:error name="exampleAttachments" />
                                <flux:error name="exampleAttachments.*" />

                                @if (count($existingExampleAttachments) > 0)
                                    <div class="space-y-2">
                                        <flux:text size="sm" class="font-medium">{{ __('Current image attachments') }}</flux:text>
                                        @foreach ($existingExampleAttachments as $attachment)
                                            <div wire:key="existing-example-attachment-{{ $attachment['id'] }}" class="flex items-center gap-3 rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                                                <img
                                                    src="{{ asset('storage/'.$attachment['path']) }}"
                                                    alt="{{ $attachment['alt_text'] ?: $attachment['original_filename'] }}"
                                                    class="size-14 rounded object-cover"
                                                />
                                                <div class="min-w-0 flex-1">
                                                    <flux:text size="sm" class="truncate">{{ $attachment['original_filename'] }}</flux:text>
                                                    @if ($attachment['alt_text'])
                                                        <flux:text size="xs" class="truncate text-zinc-500 dark:text-zinc-400">{{ $attachment['alt_text'] }}</flux:text>
                                                    @endif
                                                </div>
                                                <flux:modal.trigger name="delete-example-attachment-{{ $attachment['id'] }}">
                                                    <flux:button
                                                        type="button"
                                                        size="xs"
                                                        variant="ghost"
                                                        icon="trash"
                                                        aria-label="{{ __('Delete image attachment') }}"
                                                    />
                                                </flux:modal.trigger>
                                                <flux:modal name="delete-example-attachment-{{ $attachment['id'] }}" class="md:w-[28rem]">
                                                    <div class="space-y-6">
                                                        <div>
                                                            <flux:heading level="2">{{ __('Delete this image attachment?') }}</flux:heading>
                                                            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                                                        </div>
                                                        <div class="flex justify-end gap-2">
                                                            <flux:modal.close>
                                                                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                                                            </flux:modal.close>
                                                            <flux:button type="button" variant="danger" wire:click="deleteExampleAttachment('{{ $attachment['id'] }}')">
                                                                {{ __('Delete') }}
                                                            </flux:button>
                                                        </div>
                                                    </div>
                                                </flux:modal>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @foreach ($this->exampleAttachments as $index => $uploadedFile)
                                    <div wire:key="new-example-attachment-{{ $index }}" class="grid grid-cols-1 items-start gap-3 rounded-lg border border-blue-200 bg-blue-50/50 p-3 sm:grid-cols-[5rem_minmax(0,1fr)] dark:border-blue-900 dark:bg-blue-950/20">
                                        @if (str_starts_with($uploadedFile->getMimeType(), 'image/'))
                                            <img
                                                src="{{ $uploadedFile->temporaryUrl() }}"
                                                alt="{{ $uploadedFile->getClientOriginalName() }}"
                                                class="aspect-square w-20 rounded object-cover"
                                            />
                                        @else
                                            <div class="flex aspect-square w-20 items-center justify-center rounded bg-zinc-100 px-1 text-center text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $uploadedFile->getClientOriginalName() }}
                                            </div>
                                        @endif
                                        <flux:field>
                                            <flux:label>{{ __('Alt text') }}</flux:label>
                                            <flux:input wire:model="exampleAttachmentAltTexts.{{ $index }}" placeholder="{{ __('Describe what the image shows') }}" />
                                            <flux:error name="exampleAttachmentAltTexts.{{ $index }}" />
                                        </flux:field>
                                    </div>
                                @endforeach
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
                            <flux:button type="submit" variant="primary" x-bind:disabled="rendering">
                                {{ $editingExampleId ? __('Update Example') : __('Add Example') }}
                            </flux:button>
                        </div>
                    </form>
                </flux:modal>
                @endcan

            @else
                <flux:callout icon="book-open" color="zinc">
                    <flux:callout.heading>{{ __('Select a criterion') }}</flux:callout.heading>
                    <flux:callout.text>{{ __('Choose a success criterion from the list to view details and manage examples.') }}</flux:callout.text>
                </flux:callout>
            @endif
        </div>
    </div>

    @can('create', \App\Models\WcagSuccessCriterion::class)
    <flux:modal name="criterion-form-modal" class="w-full max-w-3xl">
        <form wire:submit="saveCriterion" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingCriterionId ? __('Edit Knowledge Base Entry') : __('Add Knowledge Base Entry') }}
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ __('Add WCAG, EN 301 549, or another accessibility standard.') }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <flux:field>
                    <flux:label>{{ __('Standard') }}</flux:label>
                    <flux:input wire:model="criterionStandard" placeholder="{{ __('e.g. EN 301 549') }}" />
                    <flux:error name="criterionStandard" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Reference') }}</flux:label>
                    <flux:input wire:model="criterionNumber" placeholder="{{ __('e.g. 9.1.1') }}" />
                    <flux:error name="criterionNumber" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Level or clause') }}</flux:label>
                    <flux:input wire:model="criterionLevel" placeholder="{{ __('e.g. AA or applicable') }}" />
                    <flux:error name="criterionLevel" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('English name') }}</flux:label>
                    <flux:input wire:model="criterionNameEn" />
                    <flux:error name="criterionNameEn" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Swedish name') }}</flux:label>
                    <flux:input wire:model="criterionNameSv" />
                    <flux:error name="criterionNameSv" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('English description') }}</flux:label>
                    <flux:textarea wire:model="criterionDescriptionEn" rows="5" />
                    <flux:error name="criterionDescriptionEn" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Swedish description') }}</flux:label>
                    <flux:textarea wire:model="criterionDescriptionSv" rows="5" />
                    <flux:error name="criterionDescriptionSv" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Official source URL') }}</flux:label>
                <flux:input wire:model="criterionUrl" type="url" placeholder="{{ __('https://…') }}" />
                <flux:error name="criterionUrl" />
            </flux:field>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" wire:click="resetCriterionForm">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingCriterionId ? __('Update Entry') : __('Add Entry') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
    @endcan

    @can('create', \App\Models\WcagSuccessCriterion::class)
    <flux:modal name="resource-form-modal" class="w-full max-w-3xl">
        <form wire:submit="saveResource" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingResourceId ? __('Edit Related Resource') : __('Add Related Resource') }}
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ __('Add W3C, DIGG, WAI failure, or project-specific guidance.') }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <flux:field>
                    <flux:label>{{ __('Type') }}</flux:label>
                    <flux:select wire:model="resourceType">
                        @foreach ($this->resourceTypeOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="resourceType" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Code') }}</flux:label>
                    <flux:input wire:model="resourceCode" placeholder="{{ __('e.g. F16') }}" />
                    <flux:error name="resourceCode" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Sort order') }}</flux:label>
                    <flux:input wire:model="resourceOrder" type="number" min="0" />
                    <flux:error name="resourceOrder" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('English title') }}</flux:label>
                    <flux:input wire:model="resourceTitleEn" />
                    <flux:error name="resourceTitleEn" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Swedish title') }}</flux:label>
                    <flux:input wire:model="resourceTitleSv" />
                    <flux:error name="resourceTitleSv" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('English description') }}</flux:label>
                    <flux:textarea wire:model="resourceDescriptionEn" rows="4" />
                    <flux:error name="resourceDescriptionEn" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Swedish description') }}</flux:label>
                    <flux:textarea wire:model="resourceDescriptionSv" rows="4" />
                    <flux:error name="resourceDescriptionSv" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Resource URL') }}</flux:label>
                <flux:input wire:model="resourceUrl" type="url" placeholder="{{ __('https://…') }}" />
                <flux:error name="resourceUrl" />
            </flux:field>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" wire:click="resetResourceForm">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingResourceId ? __('Update Resource') : __('Add Resource') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
    @endcan
</div>
