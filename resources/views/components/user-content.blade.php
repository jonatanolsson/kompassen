@props(['content' => ''])

@if ($content)
    <div {{ $attributes->merge(['class' => 'user-content']) }}>
        {!! \App\Helpers\MarkdownHelper::toHtml($content) !!}
    </div>
@endif
