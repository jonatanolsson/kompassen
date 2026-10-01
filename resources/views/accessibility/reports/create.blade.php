<x-app-layout>
    <x-slot:title>{{ __('Generate Report for :project', ['project' => $project->name]) }}</x-slot:title>

    <div class="product-page space-y-6">
        <div class="product-page-header">
            <div>
                <flux:heading level="1">{{ __('Generate Report for :project', ['project' => $project->name]) }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Generated reports') }}</flux:text>
            </div>
            <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
        </div>

        <div class="product-section max-w-3xl">
            <form method="POST" action="{{ route('accessibility-reports.store', $project) }}" class="space-y-6">
                @csrf
                <flux:error name="report"/>

                <flux:field>
                    <flux:label>{{ __('Report Title') }}</flux:label>
                    <flux:input name="title" type="text" placeholder="{{ __('e.g., Initial Accessibility Audit') }}" value="{{ old('title') }}" required/>
                    <flux:error name="title"/>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Report scope') }}</flux:label>
                    <flux:textarea name="scope" placeholder="{{ __('Describe what was tested and what was excluded from this report...') }}" rows="4">{{ old('scope') }}</flux:textarea>
                    <flux:error name="scope"/>
                    <flux:description>{{ __('Optional: Describe the scope of this audit (e.g., pages tested, WCAG level targeted)') }}</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Primary website for report icon') }}</flux:label>
                    <flux:select name="site_url">
                        <option value="">{{ __('No website icon') }}</option>
                        @foreach ($reportSites as $siteUrl => $label)
                            <option value="{{ $siteUrl }}" @selected(old('site_url', $project->report_site_url) === $siteUrl)>{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:description>{{ __('Select an HTTPS website from this project. Its icon is saved in each new report version.') }}</flux:description>
                    <flux:error name="site_url" />
                </flux:field>

                <details class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <summary class="cursor-pointer font-medium">{{ __('Advanced report content') }}</summary>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Core issue details are always included. These selections become the project default for future reports.') }}</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ([
                            'evidence' => __('Evidence and failure type'),
                            'images' => __('Screenshots and attachments'),
                            'code' => __('Code snippets'),
                            'diagrams' => __('Diagrams and source'),
                            'examples' => __('Examples and references'),
                            'resolution' => __('Resolution details'),
                            'scope' => __('Issue sample scope'),
                            'methodologies' => __('Testing methodologies'),
                            'clean_pages' => __('Tested pages without issues'),
                            'wcag_index' => __('WCAG index'),
                        ] as $key => $label)
                            <input type="hidden" name="options[{{ $key }}]" value="0">
                            <flux:checkbox name="options[{{ $key }}]" value="1" :checked="(bool) old('options.'.$key, $reportOptions[$key])" label="{{ $label }}" />
                        @endforeach
                    </div>
                    <flux:error name="options"/>
                </details>

                <div class="flex justify-end gap-3">
                    <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Generate Report') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
