@php use App\Models\AccessibilityProject; @endphp

<x-app-layout>
    <x-slot:title>{{ $report->title }}</x-slot:title>

    <flux:header>
        <flux:heading>{{ $report->title }}</flux:heading>
        <flux:spacer/>
        <div class="flex gap-2">
            <flux:button href="{{ route('accessibility-reports.download', ['project' => $project, 'report' => $report]) }}" variant="primary" icon="arrow-down-tray">
                {{ __('Download PDF') }}
            </flux:button>
            <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back to Reports') }}</flux:button>
        </div>
    </flux:header>

    <div class="space-y-6">
        <div class="flex justify-end">
            <form action="{{ route('accessibility-reports.destroy', ['project' => $project, 'report' => $report]) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');" style="display: inline;">
                @csrf
                @method('DELETE')
                <flux:button type="submit" variant="danger" icon="trash">
                    {{ __('Delete Report') }}
                </flux:button>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            {!! $report->html_content !!}
        </div>
    </div>
</x-app-layout>

<style>
    /* Override report print styles to fit in page layout */
    .container {
        max-width: 100%;
        border-radius: 0;
        box-shadow: none;
    }
    body {
        padding: 0;
        background-color: transparent;
    }
</style>
