@props(['markdown' => ''])

@if ($markdown)
    <div {{ $attributes->merge(['class' => 'prose dark:prose-invert max-w-none']) }}>
        {!! \App\Helpers\MarkdownHelper::toHtml($markdown) !!}
    </div>
@endif
