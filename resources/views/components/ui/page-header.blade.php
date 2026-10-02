@props(['title', 'description' => null])

<header {{ $attributes->merge(['class' => 'flex flex-col gap-4 border-b border-zinc-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-800']) }}>
    <div class="min-w-0">
        <flux:heading level="1">{{ $title }}</flux:heading>
        @if ($description)
            <flux:text class="mt-2 max-w-3xl text-zinc-600 dark:text-zinc-400">{{ $description }}</flux:text>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</header>
