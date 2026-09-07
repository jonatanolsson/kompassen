@php use App\Models\AccessibilityProject; @endphp

<flux:header>
    <flux:heading>{{ __('Generate Report for :project', ['project' => $project-> {{ __('') }} <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button> {{ __('') }} <flux:card class="max-w-2xl">
        <form method="POST" action="{{ route('accessibility-reports.store', $project) }}" class="space-y-6">
            @csrf

            <flux:field>
            <flux:label>{{ __('Report Title') }}</flux:label>
            <flux:input name="title" type="text" placeholder="{{ __('e.g., Initial Accessibility Audit') }}" value="{{ old('title') }}" required/> {{ __('') }} <flux:field>
            <flux:label>{{ __('Scope') }}</flux:label>
            <flux:textarea name="scope" placeholder="{{ __('Describe what was tested and what was excluded from this report...') }}" rows="4"> {{ __('') }} <flux:description>{{ __('Optional: Describe the scope of this audit (e.g., pages tested, WCAG level targeted)') }}</flux:description> {{ __('') }} <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Generate Report') }}</flux:button> {{ __('') }} </flux:card>
</flux:main>
