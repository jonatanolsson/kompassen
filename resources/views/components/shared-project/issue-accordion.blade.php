@props(['issue', 'pageName' => null])

@php
    $descriptionSummary = Str::of(strip_tags($issue->description ?? ''))->squish()->limit(220);
@endphp

<details
    id="issue-{{ $issue->id }}"
    data-print-expand
    class="group rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 print:page-break-inside-avoid"
>
    <summary class="cursor-pointer list-none p-4 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 print:p-3">
        <div class="flex items-start justify-between gap-3">
            <h3 class="font-semibold text-zinc-900 dark:text-white print:text-black">{{ $issue->title }}</h3>
            <x-shared-project.severity-badge :severity="$issue->severity" />
        </div>

        <div class="mt-2 flex flex-wrap gap-2 text-xs text-zinc-600 dark:text-zinc-300 print:text-zinc-700">
            @if ($pageName)
                <span>{{ __('Affected page or service') }}: {{ $pageName }}</span>
            @endif
            @if ($issue->component_area)
                <span>{{ $issue->component_area }}</span>
            @endif
            @if ($issue->difficulty)
                <span>{{ __('Difficulty') }}: {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) }}</span>
            @endif
            @if ($issue->status)
                <span>{{ __('Status') }}: {{ __($issue->status) }}</span>
            @endif
            @foreach ($issue->wcagCriteria as $criterion)
                <flux:badge color="emerald" size="sm">
                    WCAG {{ $criterion->number }}
                </flux:badge>
            @endforeach
        </div>

        @if ($descriptionSummary->isNotEmpty())
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ $descriptionSummary }}</p>
        @endif

        <span class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-zinc-600 dark:text-zinc-400 print:hidden">
            {{ __('Show or hide details') }}
            <span aria-hidden="true" class="transition-transform group-open:rotate-180">⌄</span>
        </span>
    </summary>

    <div data-print-details class="space-y-5 border-t border-zinc-200 p-4 dark:border-zinc-700 print:p-3">
        @if ($issue->description)
            <section>
                <h4 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Problem') }}</h4>
                <x-user-content :content="$issue->description" class="text-sm text-zinc-700 dark:text-zinc-300" />
            </section>
        @endif

        @if ($issue->mermaid_diagram)
            <section>
                <h4 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Issue diagram') }}</h4>
                <div
                    id="issue-diagram-{{ $issue->id }}"
                    data-mermaid-diagram
                    data-mermaid-source="{{ $issue->mermaid_diagram }}"
                    role="img"
                    aria-label="{{ __('Issue diagram') }}"
                    class="mermaid min-h-24 overflow-auto rounded bg-white px-4 py-3 text-sm text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100 print:border print:border-zinc-300"
                >{{ $issue->mermaid_diagram }}</div>
            </section>
        @endif

        @if ($issue->solution_suggestions)
            <section>
                <h4 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Recommended action') }}</h4>
                <x-user-content :content="$issue->solution_suggestions" class="text-sm text-zinc-700 dark:text-zinc-300" />
            </section>
        @endif

        @if ($issue->wcagCriteria->isNotEmpty())
            <section>
                <h4 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('WCAG Criteria') }}</h4>
                <div class="space-y-3">
                    @foreach ($issue->wcagCriteria as $criterion)
                        <article class="border-t border-zinc-200 pt-3 dark:border-zinc-700 print:border-zinc-300">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $criterion->number }}</span>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $criterion->name_sv ?? $criterion->name_en }}</span>
                                <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Level') }} {{ Str::upper($criterion->level) }}</span>
                                @if ($criterion->url)
                                    <a href="{{ $criterion->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-zinc-700 underline dark:text-zinc-300 print:hidden">
                                        W3C
                                    </a>
                                @endif
                            </div>
                            @if ($criterion->description_sv || $criterion->description_en)
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $criterion->description_sv ?? $criterion->description_en }}</p>
                            @endif
                            @if ($criterion->pivot->failure_type)
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    <span class="font-medium">{{ __('Failure type') }}:</span> {{ __($criterion->pivot->failure_type) }}
                                </p>
                            @endif
                            @if ($criterion->pivot->comment)
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    <span class="font-medium">{{ __('Evidence') }}:</span> {{ $criterion->pivot->comment }}
                                </p>
                            @endif
                            @if ($criterion->pivot->code_snippet)
                                <pre class="mt-2 overflow-auto rounded bg-zinc-950 p-3 text-xs text-zinc-100"><code>{{ $criterion->pivot->code_snippet }}</code></pre>
                            @endif
                            @if ($criterion->pivot->screenshot_path)
                                <img
                                    src="{{ asset('storage/'.$criterion->pivot->screenshot_path) }}"
                                    alt="{{ __('WCAG failure evidence for :criterion', ['criterion' => $criterion->number]) }}"
                                    class="mt-2 max-h-96 max-w-full rounded border border-zinc-200 object-contain dark:border-zinc-700 print:border-zinc-300"
                                />
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($issue->attachments->isNotEmpty())
            <section>
                <h4 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Screenshots') }}</h4>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($issue->attachments as $attachment)
                        <div>
                            <button
                                type="button"
                                @click="openModal('selectedImage', '{{ asset('storage/'.$attachment->path) }}', $event); showModal = true"
                                aria-label="{{ __('View image: :filename', ['filename' => $attachment->original_filename]) }}"
                                class="overflow-hidden rounded border border-zinc-200 dark:border-zinc-700 print:hidden"
                            >
                                <img
                                    src="{{ asset('storage/'.$attachment->path) }}"
                                    alt="{{ $attachment->original_filename }}"
                                    class="aspect-video w-full object-cover"
                                />
                            </button>
                            <img
                                src="{{ asset('storage/'.$attachment->path) }}"
                                alt="{{ $attachment->original_filename }}"
                                class="hidden w-full rounded border border-zinc-300 print:block"
                            />
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ $attachment->original_filename }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($issue->examples->isNotEmpty())
            <section class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <h4 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">
                    {{ __('Knowledge Base Examples') }} ({{ $issue->examples->count() }})
                </h4>
                <div class="space-y-4">
                    @foreach ($issue->examples as $example)
                        <article class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700 print:page-break-inside-avoid">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <h5 class="font-medium text-zinc-900 dark:text-white">{{ $example->title }}</h5>
                                @if ($example->criterion)
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $example->criterion->standard }} · {{ $example->criterion->number }}
                                    </span>
                                @endif
                            </div>

                            @if ($example->description)
                                <x-user-content :content="$example->description" class="text-sm text-zinc-700 dark:text-zinc-300" />
                            @endif

                            @if ($example->attachments->isNotEmpty())
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    @foreach ($example->attachments as $attachment)
                                        <a
                                            href="{{ asset('storage/'.$attachment->path) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="overflow-hidden rounded"
                                        >
                                            <img
                                                src="{{ asset('storage/'.$attachment->path) }}"
                                                alt="{{ $attachment->alt_text ?: $attachment->original_filename }}"
                                                class="aspect-video w-full object-cover"
                                            />
                                            <span class="block px-2 py-1 text-xs text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ $attachment->original_filename }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if ($example->code_snippet)
                                @if ($example->code_language === 'mermaid')
                                    <div
                                        id="example-diagram-{{ $example->id }}"
                                        data-mermaid-diagram
                                        data-mermaid-source="{{ $example->code_snippet }}"
                                        role="img"
                                        aria-label="{{ $example->title }}"
                                        class="mermaid min-h-24 overflow-auto rounded bg-white px-4 py-3 text-sm text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100 print:border print:border-zinc-300"
                                    >{{ $example->code_snippet }}</div>
                                @endif
                                <details data-print-expand class="group rounded bg-zinc-100 dark:bg-zinc-800">
                                    <summary class="cursor-pointer px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ __('Show source code') }}{{ $example->code_language ? ' · '.strtoupper($example->code_language) : '' }}
                                    </summary>
                                    <pre data-print-details class="overflow-auto bg-zinc-950 px-3 py-2 text-xs text-zinc-100"><code>{{ $example->code_snippet }}</code></pre>
                                </details>
                            @endif

                            @if ($example->url)
                                <a href="{{ $example->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-zinc-700 underline dark:text-zinc-300 print:hidden">
                                    {{ $example->url_label ?: $example->url }}
                                </a>
                            @endif
                            @if ($example->referenceLinks->isNotEmpty())
                                <div class="flex flex-wrap gap-x-4 gap-y-2">
                                    @foreach ($example->referenceLinks as $link)
                                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-zinc-700 underline dark:text-zinc-300 print:hidden">
                                            {{ $link->label ?: $link->url }}
                                        </a>
                                        <span class="hidden text-sm print:inline">{{ $link->label ?: $link->url }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</details>
