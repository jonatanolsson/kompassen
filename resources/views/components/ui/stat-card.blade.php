@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'product-stat']) }}>
    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ $label }}</div>
    <div class="mt-2 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $value }}</div>

    @if ($slot->isNotEmpty())
        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $slot }}</div>
    @endif
</div>
