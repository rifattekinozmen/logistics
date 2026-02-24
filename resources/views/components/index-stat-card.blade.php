@props([
    'title',
    'value',
    'icon' => 'show_chart',
    'color' => 'primary',
    'col' => 'col-md-3',
])

@php
    $colorRgb = match($color) {
        'primary' => '61, 105, 206',
        'success' => '45, 139, 111',
        'warning' => '255, 193, 7',
        'danger' => '196, 30, 90',
        'info' => '55, 117, 168',
        'secondary' => '107, 122, 153',
        default => '61, 105, 206',
    };
@endphp

<div class="{{ $col }}">
    <div class="bg-white rounded-3xl shadow-sm border p-3 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba({{ $colorRgb }}, 0.12);">
        <div class="position-absolute top-0 end-0 opacity-5" style="width: 80px; height: 80px; background: radial-gradient(circle, var(--bs-{{ $color }}) 0%, transparent 70%); transform: translate(20px, -20px);"></div>
        <div class="d-flex align-items-center justify-content-between position-relative">
            <div>
                <div class="small text-secondary mb-1">{{ $title }}</div>
                <div class="fw-bold fs-4 text-{{ $color }}">{{ $value }}</div>
            </div>
            <div class="rounded-2xl d-flex align-items-center justify-content-center bg-{{ $color }}-200" style="width: 44px; height: 44px;">
                <span class="material-symbols-outlined text-{{ $color }}" style="font-size: 1.25rem;">{{ $icon }}</span>
            </div>
        </div>
    </div>
</div>
