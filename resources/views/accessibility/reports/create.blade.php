@php use App\Models\AccessibilityProject; @endphp

<x-app-layout>
    <x-slot:title>{{ __('Generate Report for :project', ['project' => $project->name]) }}</x-slot:title>

    <flux:header>
        <flux:heading>{{ __('Generate Report for :project', ['project' => $project->name]) }}</flux:heading>
        <flux:spacer/>
        <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
    </flux:header>

    <div>
        <flux:card class="max-w-2xl">
            <form method="POST" action="{{ route('accessibility-reports.store', $project) }}" class="space-y-6">
                @csrf

                <flux:field>
                    <flux:label>{{ __('Report Title') }}</flux:label>
                    <flux:input name="title" type="text" placeholder="{{ __('e.g., Initial Accessibility Audit') }}" value="{{ old('title') }}" required/>
                    <flux:error name="title"/>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Scope') }}</flux:label>
                    <flux:textarea name="scope" placeholder="{{ __('Describe what was tested and what was excluded from this report...') }}" rows="4">{{ old('scope') }}</flux:textarea>
                    <flux:error name="scope"/>
                    <flux:description>{{ __('Optional: Describe the scope of this audit (e.g., pages tested, WCAG level targeted)') }}</flux:description>
                </flux:field>

                <div class="flex justify-end gap-3">
                    <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Generate Report') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-app-layout>
