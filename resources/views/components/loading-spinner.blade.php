@props([
    'size' => 'md',
    'type' => 'border',
    'color' => 'primary',
    'text' => null,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'spinner-' . $type . '-sm',
        'lg' => 'spinner-' . $type . ' spinner-lg',
        default => 'spinner-' . $type,
    };
@endphp

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <div class="{{ $sizeClass }} text-{{ $color }}" role="status">
        <span class="visually-hidden">Yükleniyor...</span>
    </div>
    @if ($text)
        <span class="ms-2 text-muted">{{ $text }}</span>
    @endif
    {{ $slot }}
</div>
