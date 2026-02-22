@props([
    'type' => 'info',
    'dismissible' => true,
    'icon' => null,
])

@php
    $iconMap = [
        'success' => 'check_circle',
        'danger' => 'error',
        'warning' => 'warning',
        'info' => 'info',
        'primary' => 'star',
        'secondary' => 'help',
    ];
    $iconName = $icon ?? ($iconMap[$type] ?? 'info');
@endphp

<div {{ $attributes->merge([
    'class' => 'alert alert-' . $type . ' rounded-3xl ' . ($dismissible ? 'alert-dismissible fade show' : ''),
    'role' => 'alert',
]) }}>
    <div class="d-flex align-items-start">
        <span class="material-symbols-outlined me-2 mt-1 flex-shrink-0" style="font-size: 1.25rem;">{{ $iconName }}</span>
        <div class="flex-grow-1">
            {{ $slot }}
        </div>
    </div>
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
    @endif
</div>
