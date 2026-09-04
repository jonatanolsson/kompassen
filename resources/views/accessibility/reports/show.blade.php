@php use App\Models\AccessibilityProject; @endphp

<flux:header>
    <flux:heading>{{ $report->title }}</flux:heading>
    <flux:spacer/>
    <div class="flex gap-2">
        <flux:button href="{{ route('accessibility-reports.download', ['project' => $project, 'report' => $report]) }}" variant="primary" icon="arrow-down-tray">
            Download PDF
        </flux:button>
        <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">Back to Reports</flux:button>
    </div>
</flux:header>

<flux:main class="space-y-6">
    <div class="flex justify-end">
        <form action="{{ route('accessibility-reports.destroy', ['project' => $project, 'report' => $report]) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
            @csrf
            @method('DELETE')
            <flux:button type="submit" variant="danger" icon="trash">
                Delete Report
            </flux:button>
        </form>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
        {!! $report->html_content !!}
    </div>
</flux:main>

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
