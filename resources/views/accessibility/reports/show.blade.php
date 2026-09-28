<x-app-layout>
    <x-slot:title>{{ $report->title }}</x-slot:title>

    <div class="product-page space-y-6">
        <div class="product-page-header">
            <div>
                <flux:heading level="1">{{ $report->title }}</flux:heading>
                <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __('Reports for :name', ['name' => $project->name]) }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
            <flux:button href="{{ route('accessibility-reports.download', ['project' => $project, 'report' => $report]) }}" variant="primary" icon="arrow-down-tray">
                {{ __('Download PDF') }}
            </flux:button>
            <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back to Reports') }}</flux:button>
            </div>
        </div>

    <div class="flex justify-end">
            <form action="{{ route('accessibility-reports.destroy', ['project' => $project, 'report' => $report]) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');" style="display: inline;">
                @csrf
                @method('DELETE')
                <flux:button type="submit" variant="danger" icon="trash">
                    {{ __('Delete Report') }}
                </flux:button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
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
