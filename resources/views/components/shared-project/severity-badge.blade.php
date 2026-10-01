@props(['severity'])

@php
    $color = match ($severity) {
        'critical' => 'red',
        'major' => 'amber',
        'moderate' => 'yellow',
        default => 'emerald',
    };
    $label = __(Str::title(str_replace(['_', '-'], ' ', $severity)));
@endphp

<flux:badge :color="$color" size="sm" class="print:border print:border-current print:bg-transparent">
    {{ $label }}
</flux:badge>
