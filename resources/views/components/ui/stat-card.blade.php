@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-zinc-200/80 bg-white p-4 transition-colors hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700']) }}>
    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ $label }}</div>
    <div class="mt-2 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $value }}</div>

    @if ($slot->isNotEmpty())
        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $slot }}</div>
    @endif
</div>
