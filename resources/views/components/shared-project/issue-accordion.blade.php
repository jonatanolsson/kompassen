@props(['issue', 'targetWcagVersion', 'issueHeadingLevel' => 3, 'sectionHeadingLevel' => 4, 'editUrl' => null])

@php
    $issueFacts = collect([
        __('Component Area') => $issue->component_area,
        __('Estimated remediation effort') => $issue->difficulty ? __(Str::title(str_replace(['_', '-'], ' ', $issue->difficulty))) : null,
        __('Status') => $issue->status ? __($issue->status) : null,
    ])->filter(fn (?string $value): bool => filled($value));
@endphp

<flux:accordion.item
    wire:key="shared-issue-{{ $issue->id }}"
    id="issue-{{ $issue->id }}"
    class="overflow-hidden rounded-lg border border-zinc-200 bg-white p-0 dark:border-zinc-700 dark:bg-zinc-900 print:border-zinc-300 print:page-break-inside-avoid"
>
    <flux:accordion.heading class="items-start p-4 text-start print:p-3">
        <span class="flex w-full min-w-0 flex-col items-start gap-3">
            <span class="flex w-full items-start justify-between gap-3">
                <span role="heading" aria-level="{{ $issueHeadingLevel }}" class="text-base font-semibold text-zinc-900 dark:text-white print:text-black">{{ $issue->title }}</span>
                <span class="shrink-0">
                    <x-shared-project.severity-badge :severity="$issue->severity" />
                </span>
            </span>

            <span class="w-full space-y-3">
                @if ($issueFacts->isNotEmpty())
                    <dl class="grid w-full grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-4">
                        @foreach ($issueFacts as $label => $value)
                            <div class="min-w-0">
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400 print:text-zinc-700">{{ $label }}</dt>
                                <dd class="truncate text-sm font-medium text-zinc-700 dark:text-zinc-200 print:text-black">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($issue->wcagCriteria->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach ($issue->wcagCriteria->take(2) as $criterion)
                            <flux:badge color="zinc" size="sm" class="print:border print:border-zinc-400 print:bg-transparent print:text-black">{{ $criterion->standard }} · {{ $criterion->number }}</flux:badge>
                            @if ($criterion->isOutsideProjectTargetVersion($targetWcagVersion))
                                <flux:badge color="amber" size="sm" class="print:border print:border-zinc-400 print:bg-transparent print:text-black">{{ __('Nice to have') }}</flux:badge>
                            @endif
                        @endforeach
                        @if ($issue->wcagCriteria->count() > 2)
                            <flux:badge color="zinc" size="sm">+{{ $issue->wcagCriteria->count() - 2 }}</flux:badge>
                        @endif
                    </div>
                @endif

                @if (filled($issue->summary))
                    <span class="block max-w-5xl space-y-0.5">
                        <span class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 print:text-zinc-700">{{ __('Summary') }}</span>
                        <span class="block text-sm leading-6 text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ $issue->summary }}</span>
                    </span>
                @endif
            </span>
        </span>
    </flux:accordion.heading>

    <flux:accordion.content class="space-y-4 border-t border-zinc-200 p-4 text-zinc-900 dark:border-zinc-700 dark:text-zinc-100 print:p-3">
        @if ($editUrl)
            <div class="flex justify-end print:hidden">
                <flux:button href="{{ $editUrl }}" variant="ghost" size="sm" icon="pencil">
                    {{ __('Edit finding') }}
                </flux:button>
            </div>
        @endif

        @if ($issue->description)
            <section>
                <div role="heading" aria-level="{{ $sectionHeadingLevel }}" class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white print:text-black">{{ __('Description') }}</div>
                <x-user-content :content="$issue->description" class="text-sm leading-6 text-zinc-700 dark:text-zinc-300 print:text-zinc-700" />
            </section>
        @endif

        @if ($issue->solution_suggestions)
            <section>
                <div role="heading" aria-level="{{ $sectionHeadingLevel }}" class="mb-3 text-sm font-semibold text-emerald-950 dark:text-emerald-100 print:text-black">{{ __('Recommended action') }}</div>
                <x-user-content :content="$issue->solution_suggestions" class="text-sm leading-6 text-zinc-800 dark:text-zinc-200 print:text-zinc-700" />
            </section>
        @endif

        @if ($issue->problem_code_snippet || $issue->corrected_code_snippet)
            <section>
                <div role="heading" aria-level="{{ $sectionHeadingLevel }}" class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white print:text-black">{{ __('Issue-specific code') }}</div>
                <div class="space-y-4">
                    @if ($issue->problem_code_snippet)
                        <div class="space-y-2">
                            <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ __('Code causing the issue') }}</p>
                            <pre class="overflow-auto rounded bg-zinc-950 p-3 text-xs text-zinc-100 print:border print:border-zinc-300 print:bg-white print:text-zinc-900"><code>{{ $issue->problem_code_snippet }}</code></pre>
                        </div>
                    @endif
                    @if ($issue->corrected_code_snippet)
                        <div class="space-y-2">
                            <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ __('Suggested corrected code') }}</p>
                            <pre class="overflow-auto rounded bg-zinc-950 p-3 text-xs text-zinc-100 print:border print:border-zinc-300 print:bg-white print:text-zinc-900"><code>{{ $issue->corrected_code_snippet }}</code></pre>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($issue->mermaid_diagram)
            <section>
                <div role="heading" aria-level="{{ $sectionHeadingLevel }}" class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white print:text-black">{{ __('Issue diagram') }}</div>
                <div
                    id="issue-diagram-{{ $issue->id }}"
                    data-mermaid-diagram
                    data-mermaid-source="{{ $issue->mermaid_diagram }}"
                    role="img"
                    aria-label="{{ __('Issue diagram') }}"
                    class="mermaid min-h-24 overflow-auto rounded bg-white px-4 py-3 text-sm text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100 print:border print:border-zinc-300 print:bg-white print:text-zinc-900"
                >{{ $issue->mermaid_diagram }}</div>
            </section>
        @endif

        @if ($issue->wcagCriteria->isNotEmpty())
            <section>
                <div role="heading" aria-level="{{ $sectionHeadingLevel }}" class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white print:text-black">{{ __('WCAG Criteria') }}</div>
                <div class="space-y-3">
                    @foreach ($issue->wcagCriteria as $criterion)
                        <article class="border-t border-zinc-200 pt-3 first:border-t-0 first:pt-0 dark:border-zinc-700">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-semibold tracking-wide text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ $criterion->standard }} · {{ $criterion->number }}</span>
                                        @if ($criterion->isOutsideProjectTargetVersion($targetWcagVersion))
                                            <flux:badge color="amber" size="sm" class="print:border print:border-zinc-400 print:bg-transparent print:text-black">{{ __('Nice to have') }}</flux:badge>
                                        @endif
                                    </div>
                                    <p class="font-medium text-zinc-900 dark:text-white print:text-black">{{ $criterion->name_sv ?? $criterion->name_en }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <flux:badge color="zinc" size="sm" class="print:border print:border-zinc-400 print:bg-transparent print:text-black">{{ __('Level') }} {{ Str::upper($criterion->level) }}</flux:badge>
                                    @if ($criterion->url)
                                        <a href="{{ $criterion->url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-accent-content underline print:hidden">W3C</a>
                                    @endif
                                </div>
                            </div>

                            @if ($criterion->description_sv || $criterion->description_en)
                                <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400 print:text-zinc-700">{{ $criterion->description_sv ?? $criterion->description_en }}</p>
                            @endif

                            @if ($criterion->pivot->failure_type || $criterion->pivot->comment)
                                <dl class="mt-3 grid gap-3 border-t border-zinc-200 pt-3 dark:border-zinc-700 sm:grid-cols-2">
                                    @if ($criterion->pivot->failure_type)
                                        <div>
                                            @php $isManualAssessment = strtolower(trim($criterion->pivot->failure_type)) === 'manual'; @endphp
                                            <dt class="text-xs text-zinc-500 dark:text-zinc-400 print:text-zinc-700">{{ $isManualAssessment ? __('Assessment method') : __('Failure type') }}</dt>
                                            <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200 print:text-black">{{ $isManualAssessment ? __('Manual assessment') : __($criterion->pivot->failure_type) }}</dd>
                                        </div>
                                    @endif
                                    @if ($criterion->pivot->comment)
                                        <div>
                                            <dt class="text-xs text-zinc-500 dark:text-zinc-400 print:text-zinc-700">{{ __('Evidence') }}</dt>
                                            <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200 print:text-black">{{ $criterion->pivot->comment }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            @endif

                            @if ($criterion->pivot->code_snippet)
                                <pre class="mt-3 overflow-auto rounded bg-zinc-950 p-3 text-xs text-zinc-100 print:border print:border-zinc-300 print:bg-white print:text-zinc-900"><code>{{ $criterion->pivot->code_snippet }}</code></pre>
                            @endif
                            @if ($criterion->pivot->screenshot_path)
                                <img
                                    src="{{ asset('storage/'.$criterion->pivot->screenshot_path) }}"
                                    alt="{{ __('WCAG failure evidence for :criterion', ['criterion' => $criterion->standard.' '.$criterion->number]) }}"
                                    class="mt-3 max-h-96 max-w-full rounded border border-zinc-200 object-contain dark:border-zinc-700 print:border-zinc-300"
                                />
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($issue->attachments->isNotEmpty())
            <section>
                <div role="heading" aria-level="{{ $sectionHeadingLevel }}" class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white print:text-black">{{ __('Screenshots') }}</div>
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
            <section>
                <div role="heading" aria-level="{{ $sectionHeadingLevel }}" class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">
                    {{ __('Knowledge Base Examples') }} ({{ $issue->examples->count() }})
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($issue->examples as $example)
                        <article class="space-y-3 py-4 first:pt-0 last:pb-0 print:page-break-inside-avoid">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $example->title }}</p>
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
                                <flux:accordion transition data-print-accordion class="px-3">
                                    <flux:accordion.item wire:key="shared-example-source-{{ $example->id }}" class="border-0">
                                        <flux:accordion.heading class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ __('Show source code') }}{{ $example->code_language ? ' · '.strtoupper($example->code_language) : '' }}
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <pre class="overflow-auto bg-zinc-950 px-3 py-2 text-xs text-zinc-100"><code>{{ $example->code_snippet }}</code></pre>
                                        </flux:accordion.content>
                                    </flux:accordion.item>
                                </flux:accordion>
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
    </flux:accordion.content>
</flux:accordion.item>
