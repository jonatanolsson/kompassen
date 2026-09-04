@php use App\Models\AccessibilityProject; @endphp

<flux:header>
    <flux:heading>Generate Report for {{ $project->name }}</flux:heading>
    <flux:spacer/>
    <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">Back</flux:button>
</flux:header>

<flux:main>
    <flux:card class="max-w-2xl">
        <form method="POST" action="{{ route('accessibility-reports.store', $project) }}" class="space-y-6">
            @csrf

            <flux:field>
                <flux:label>Report Title</flux:label>
                <flux:input name="title" type="text" placeholder="e.g., Initial Accessibility Audit" value="{{ old('title') }}" required/>
                <flux:error name="title"/>
            </flux:field>

            <flux:field>
                <flux:label>Scope</flux:label>
                <flux:textarea name="scope" placeholder="Describe what was tested and what was excluded from this report..." rows="4">{{ old('scope') }}</flux:textarea>
                <flux:error name="scope"/>
                <flux:description>Optional: Describe the scope of this audit (e.g., pages tested, WCAG level targeted)</flux:description>
            </flux:field>

            <div class="flex gap-3 justify-end">
                <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">Generate Report</flux:button>
            </div>
        </form>
    </flux:card>
</flux:main>
