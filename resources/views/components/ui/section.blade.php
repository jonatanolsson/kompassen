@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'product-section']) }}>
    @if ($title || $description || isset($actions))
        <div class="product-section-heading mb-4">
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
