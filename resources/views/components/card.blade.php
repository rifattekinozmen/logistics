@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'footer' => null,
    'headerActions' => null,
    'noPadding' => false,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm rounded-3xl ' . $class]) }}>
    @if ($title || $icon || $subtitle || $headerActions)
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                @if ($icon)
                    <span class="material-symbols-outlined me-2 text-primary" style="font-size: 1.25rem;">{{ $icon }}</span>
                @endif
                <div>
                    @if ($title)
                        <h5 class="card-title mb-0 fw-semibold text-dark">{{ $title }}</h5>
                    @endif
                    @if ($subtitle)
                        <small class="text-muted">{{ $subtitle }}</small>
                    @endif
                </div>
            </div>
            @if ($headerActions)
                <div>{{ $headerActions }}</div>
            @endif
        </div>
    @endif

    <div class="card-body {{ $noPadding ? 'p-0' : '' }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="card-footer bg-white border-0">
            {{ $footer }}
        </div>
    @endif
</div>
