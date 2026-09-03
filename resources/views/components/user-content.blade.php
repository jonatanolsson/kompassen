@props(['content' => ''])

@if ($content)
    <div {{ $attributes->merge(['class' => 'user-content']) }}>
        {!! $content !!}
    </div>
@endif
