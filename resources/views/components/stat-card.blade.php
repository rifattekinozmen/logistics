@props([
    'title',
    'value',
    'icon' => 'show_chart',
    'color' => 'primary',
    'change' => null,
    'changeType' => 'increase',
    'link' => null,
])

<div {{ $attributes->merge(['class' => 'col-md-3']) }}>
    <div class="card stat-card border-0 shadow-sm h-100 rounded-3xl">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1 small">{{ $title }}</p>
                    <h3 class="mb-0 fw-bold text-dark">{{ $value }}</h3>
                    @if ($change !== null)
                        <small class="
                            @if ($changeType === 'increase') text-success
                            @elseif ($changeType === 'decrease') text-danger
                            @else text-muted
                            @endif
                        ">
                            <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">
                                {{ $changeType === 'increase' ? 'trending_up' : ($changeType === 'decrease' ? 'trending_down' : 'trending_flat') }}
                            </span>
                            {{ $change }}
                        </small>
                    @endif
                </div>
                <div class="icon-box bg-{{ $color }} bg-opacity-10 rounded-3 p-3">
                    <span class="material-symbols-outlined text-{{ $color }}" style="font-size: 1.75rem;">{{ $icon }}</span>
                </div>
            </div>
        </div>
        @if ($link)
            <div class="card-footer bg-transparent border-top-0 pt-0">
                <a href="{{ $link }}" class="text-decoration-none small text-primary">
                    Detayları Gör <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">arrow_forward</span>
                </a>
            </div>
        @endif
    </div>
</div>
