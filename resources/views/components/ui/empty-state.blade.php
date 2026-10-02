@props(['message', 'description' => null])

<div {{ $attributes->merge(['class' => 'py-4']) }}>
    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $message }}</flux:text>

    @if ($description)
        <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $description }}</flux:text>
    @endif

    @if (isset($actions))
        <div class="mt-3 flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
