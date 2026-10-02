<x-app-layout>
    <x-slot:title>{{ $report->title }}</x-slot:title>

    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8 space-y-8">
        <flux:error name="report"/>
        <div class="flex flex-col gap-4 border-b border-zinc-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-800">
            <div>
                <flux:heading level="1">{{ $report->title }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Reports for :name', ['name' => $project->name]) }}</flux:text>
                <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Saved snapshot: :date', ['date' => ($report->generated_at ?? $report->created_at)->translatedFormat('j M Y, H:i')]) }}
                    · {{ __('Version :version', ['version' => $report->version]) }}
                </flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:button href="{{ route('accessibility-reports.download', ['project' => $project, 'report' => $report]) }}" variant="primary" icon="arrow-down-tray">
                    {{ __('Download PDF') }}
                </flux:button>
                <flux:dropdown align="end">
                    <flux:button variant="outline" icon="ellipsis-horizontal">{{ __('Actions') }}</flux:button>
                    <flux:menu>
                        @can('update', $project)
                            <flux:menu.item as="button" type="submit" form="regenerate-report" icon="arrow-path">
                                {{ __('Create new version') }}
                            </flux:menu.item>
                            <flux:menu.separator />
                        @endcan
                        @can('delete', $project)
                            <flux:modal.trigger name="delete-report-modal">
                                <flux:menu.item icon="trash" variant="danger">
                                    {{ __('Delete Report') }}
                                </flux:menu.item>
                            </flux:modal.trigger>
                        @endcan
                    </flux:menu>
                </flux:dropdown>
                <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">
                    {{ __('Back to Reports') }}
                </flux:button>
            </div>
        </div>

        @can('update', $project)
            <form id="regenerate-report" method="POST" action="{{ route('accessibility-reports.regenerate', ['project' => $project, 'report' => $report]) }}" class="hidden">
                @csrf
            </form>
        @endcan

        @can('delete', $project)
        <flux:modal name="delete-report-modal" class="md:w-[28rem]">
            <form action="{{ route('accessibility-reports.destroy', ['project' => $project, 'report' => $report]) }}" method="POST" class="space-y-6">
                @csrf
                @method('DELETE')

                <div>
                    <flux:heading level="2">{{ __('Delete this report?') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                </div>
            </form>
        </flux:modal>
        @endcan

        @if ($versions->count() > 1)
            <nav aria-label="{{ __('Report versions') }}">
                <flux:dropdown>
                    <flux:button variant="outline" icon="clock" icon:trailing="chevron-down">
                        {{ __('Report versions') }} · {{ __('Version :version', ['version' => $report->version]) }}
                    </flux:button>
                    <flux:menu>
                        @foreach ($versions as $version)
                            <flux:menu.item
                                href="{{ route('accessibility-reports.show', ['project' => $project, 'report' => $version]) }}"
                                :icon="$version->is($report) ? 'check' : 'document-text'"
                                :aria-current="$version->is($report) ? 'page' : null"
                            >
                                {{ __('Version :version', ['version' => $version->version]) }} · {{ ($version->generated_at ?? $version->created_at)->translatedFormat('j M Y, H:i') }}
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </nav>
        @endif

        <section class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 space-y-4" aria-label="{{ __('Customer delivery') }}">
            <div>
                <flux:heading level="2">{{ __('Customer delivery') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Customer links show only this saved report version, never current project changes. The preview on the project page shows current work and is available only to members.') }}</flux:text>
            </div>
            @can('update', $project)
                <form method="POST" action="{{ route('project-share-links.store', [$project, $report]) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <flux:field>
                        <flux:label>{{ __('Customer link expiration') }}</flux:label>
                        <flux:input type="datetime-local" name="expires_at" required min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" value="{{ old('expires_at') }}" />
                        <flux:error name="expires_at" />
                    </flux:field>
                    <flux:button type="submit" variant="primary" icon="link">{{ __('Create customer link') }}</flux:button>
                </form>
                @if ($errors->has('report'))
                    <flux:text color="red">{{ $errors->first('report') }}</flux:text>
                @endif
                <div class="space-y-3">
                    <flux:heading level="3">{{ __('Customer links for this version') }}</flux:heading>
                    @forelse ($shareLinks as $link)
                        <div class="flex flex-wrap items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <flux:badge :color="$link->isExpired() ? 'zinc' : 'green'">{{ $link->isExpired() ? __('Expired') : __('Active') }}</flux:badge>
                            <flux:text class="break-all">{{ route('projects.shared', $link->token) }}</flux:text>
                            <flux:text>{{ __('Expires: :date', ['date' => $link->expires_at?->translatedFormat('j M Y, H:i') ?? __('Unknown')]) }}</flux:text>
                            @unless ($link->isExpired())
                                <flux:button href="{{ route('projects.shared', $link->token) }}" target="_blank" size="sm" variant="outline">{{ __('Open customer report') }}</flux:button>
                                <flux:button x-on:click="navigator.clipboard.writeText(@js(route('projects.shared', $link->token)))" type="button" size="sm" variant="outline">{{ __('Copy link') }}</flux:button>
                            @endunless
                            <form method="POST" action="{{ route('project-share-links.destroy', [$project, $link]) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" size="sm" variant="danger">{{ __('Revoke link') }}</flux:button>
                            </form>
                        </div>
                    @empty
                        <flux:text>{{ __('No customer links for this version.') }}</flux:text>
                    @endforelse
                </div>
            @endcan
        </section>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="{{ __('Report Summary') }}">
            <div class="rounded-xl border border-zinc-200/80 bg-white p-4 transition-colors hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Issues') }}</flux:text>
                <flux:heading level="2" size="xl" class="mt-1">{{ $report->total_issues }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200/80 bg-white p-4 transition-colors hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Target Level') }}</flux:text>
                <flux:heading level="2" size="xl" class="mt-1">WCAG {{ $report->target_wcag_level }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200/80 bg-white p-4 transition-colors hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Generated By') }}</flux:text>
                <flux:heading level="2" size="lg" class="mt-2">{{ $report->document_metadata['creator'] ?? $report->creator?->name ?? __('Unknown') }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200/80 bg-white p-4 transition-colors hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Severity') }}</flux:text>
                <div class="mt-2 flex flex-wrap gap-1">
                    @if ($report->critical_count > 0)
                        <flux:badge color="red" size="sm">{{ $report->critical_count }} {{ __('Critical report issues') }}</flux:badge>
                    @endif
                    @if ($report->major_count > 0)
                        <flux:badge color="amber" size="sm">{{ $report->major_count }} {{ __('Major report issues') }}</flux:badge>
                    @endif
                    @if ($report->moderate_count > 0)
                        <flux:badge color="yellow" size="sm">{{ $report->moderate_count }} {{ __('Moderate report issues') }}</flux:badge>
                    @endif
                    @if ($report->minor_count > 0)
                        <flux:badge color="emerald" size="sm">{{ $report->minor_count }} {{ __('Minor report issues') }}</flux:badge>
                    @endif
                    @if ($report->total_issues === 0)
                        <flux:badge color="zinc" size="sm">{{ __('No documented issues') }}</flux:badge>
                    @endif
                </div>
            </div>
        </section>

        @if (! $usesCurrentTemplate)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('This report uses an older template') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Create a new version to use the current design and project evidence. This saved version will remain unchanged.') }}</flux:callout.text>
                @can('update', $project)
                    <div class="mt-4">
                        <flux:button type="submit" form="regenerate-report" variant="primary" icon="arrow-path">
                            {{ __('Create new version') }}
                        </flux:button>
                    </div>
                @endcan
            </flux:callout>
        @endif

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-white">
            <iframe
                title="{{ __('Saved report preview') }}"
                srcdoc="{{ $report->html_content }}"
                class="block min-h-[75vh] w-full bg-white"
                style="color-scheme: light"
                sandbox
            ></iframe>
        </div>
    </div>
</x-app-layout>
