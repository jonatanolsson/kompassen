@php use App\Models\AccessibilityProject; @endphp

<flux:header>
    <flux:heading>{{ $report-> {{ __('') }} <div class="flex gap-2">
        <flux:button href="{{ route('accessibility-reports.download', ['project' => $project, 'report' => $report]) }}" variant="primary" icon="arrow-down-tray">
            {{ __('Download PDF') }}
        </flux:button>
        <flux:button href="{{ route('accessibility-reports.index', $project) }}" variant="ghost" icon="arrow-left">{{ __('Back to Reports') }}</flux:button>
    </div> {{ __('') }} <div class="flex justify-end">
        <form action="{{ route('accessibility-reports.destroy', ['project' => $project, 'report' => $report]) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');" style="display: inline;">
            @csrf
            @method('DELETE')
            <flux:button type="submit" variant="danger" icon="trash">
                {{ __('Delete Report') }}
            </flux:button> {{ __('') }} <div class="bg-white dark:bg-zinc-900 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
        {!! $report->html_content !!}
    </div> {{ __('') }} </style>
