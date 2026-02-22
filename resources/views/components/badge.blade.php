@props([
    'type' => 'primary',
    'size' => 'md',
    'rounded' => false,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'px-2 py-1 small',
        'lg' => 'px-4 py-2',
        default => 'px-3 py-2',
    };
    $typeClass = match ($type) {
        'success' => 'bg-success-200 text-success',
        'danger' => 'bg-danger-200 text-danger',
        'warning' => 'bg-warning-200 text-warning',
        'info' => 'bg-info-200 text-info',
        'primary' => 'bg-primary-200 text-primary',
        'secondary' => 'bg-secondary-200 text-secondary',
        default => "bg-{$type}",
    };
@endphp

<span {{ $attributes->merge([
    'class' => 'badge fw-semibold ' . $typeClass . ' ' . $sizeClass . ' ' . ($rounded ? 'rounded-pill' : 'rounded-2xl'),
]) }}>
    {{ $slot }}
</span>
