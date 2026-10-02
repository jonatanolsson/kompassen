@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10']) }}>
    @if ($title || $description || isset($actions))
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                @if ($title)
                    <flux:heading level="2">{{ $title }}</flux:heading>
                @endif

                @if ($description)
                    <flux:text class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $description }}</flux:text>
                @endif
            </div>

            @if (isset($actions))
                <div class="flex shrink-0 items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
