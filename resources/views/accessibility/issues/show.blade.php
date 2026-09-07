<x-app-layout> {{ __('') }} <div class="flex items-start justify-between mb-8"> {{ __('') }} </flux:heading> {{ __('') }} </flux:badge>
                    <flux:badge color="zinc">{{ Str::title($issue->status) }}</flux:badge>
                    <flux:badge color="blue">{{ __('Difficulty') }}: {{ Str::title($issue-> {{ __('') }} </div> {{ __('') }} <flux:button variant="outline" icon="pencil">{{ __('Edit') }}</flux:button> {{ __('') }} <form id="resolve-fixed-form" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="inline"> {{ __('') }} <flux:button type="button" variant="primary" icon="check" size="sm" onclick="submitResolution('fixed', 'resolve-fixed-form')">{{ __('Mark as fixed') }}</flux:button> {{ __('') }} <form id="resolve-wontfix-form" action="{{ route('accessibility-issues.resolve', [$project, $issue]) }}" method="POST" class="inline"> {{ __('') }} <flux:button type="button" variant="danger" icon="x-mark" size="sm" onclick="submitResolution('wontfix', 'resolve-wontfix-form')">{{ __('Mark as wontfix') }}</flux:button> {{ __('') }} <flux:button variant="ghost">{{ __('Back') }}</flux:button> {{ __('') }} <script>
                function submitResolution(status, formId) {
                    if (!confirm('{{ __('Are you sure you want to change resolution status?') }}')) return false;
                    const notes = prompt('{{ __('Add resolution notes (optional)') }}');
                    const form = document.getElementById(formId);
                    if (!form) return false;
                    form.querySelector('input[name="resolution_status"]').value = status;
                    form.querySelector('input[name="resolution_notes"]').value = notes ? notes : '';
                    form.submit();
                }
            </script> {{ __('') }} <!-- Description -->
        @if ($issue->description)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Description') }}</flux:heading> {{ __('') }} </flux:card>
            </div>
        @endif

        <!-- Images -->
        @if ($issue->attachments->count() > 0)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">{{ __('Images') }}</flux:heading>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($issue-> {{ __('') }} <img 
                                    src="{{ asset('storage/' . $attachment->path) }}" 
                                    alt="{{ $attachment->original_filename }}"
                                    class="w-full h-48 object-cover group-hover:scale-105 transition"
                                /> {{ __('') }} </p>
                        </div> {{ __('') }} <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @if ($issue->page)
                <flux:card class="p-4">
                    <flux:heading level="3" class="text-sm mb-2">{{ __('Page/Service') }}</flux:heading>
                    <flux:text>{{ $issue->page-> {{ __('') }} <flux:card class="p-4">
                    <flux:heading level="3" class="text-sm mb-2">{{ __('Component Area') }}</flux:heading>
                    <flux:text>{{ $issue-> {{ __('') }} </div>

        <!-- WCAG Criteria -->
        @if ($wcagCriteria->count() > 0)
            <div class="mb-8">
                <flux:heading level="2" class="mb-4">{{ __('WCAG Success Criteria') }}</flux:heading>
                <div class="space-y-2"> {{ __('') }} <span class="font-bold text-blue-600 dark:text-blue-400 min-w-fit">{{ $criterion-> {{ __('') }} <flux:heading level="4" class="text-sm">{{ $criterion->name_sv ?? $criterion->name_en }}</flux:heading>
                                    <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Level') }} {{ Str::upper($criterion-> {{ __('') }} </div>
                        </flux:card> {{ __('') }} <flux:separator class="my-6" /> {{ __('') }} <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Created') }}</flux:text>
                <flux:text>{{ $issue->created_at-> {{ __('') }} <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Last Updated') }}</flux:text>
                <flux:text>{{ $issue->updated_at-> {{ __('') }} <div>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Issue ID') }}</flux:text>
                <flux:text class="font-mono text-xs">{{ $issue-> {{ __('') }} </div>
    </div>
</div>
</x-app-layout>
