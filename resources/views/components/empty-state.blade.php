@props([
    'icon' => 'inbox',
    'title' => 'Veri Bulunamadı',
    'message' => 'Henüz kayıtlı veri bulunmamaktadır.',
    'action' => null,
    'actionText' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-5']) }}>
    <div class="empty-state">
        <span class="material-symbols-outlined text-secondary mb-3 d-block" style="font-size: 4rem; opacity: 0.5;">{{ $icon }}</span>
        <h4 class="text-muted">{{ $title }}</h4>
        <p class="text-muted mb-4">{{ $message }}</p>
        @if ($action || ($actionText && $actionUrl))
            {{ $action ?? '' }}
            @if ($actionText && $actionUrl && ! $action)
                <a href="{{ $actionUrl }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
                    {{ $actionText }}
                </a>
            @endif
        @endif
        {{ $slot }}
    </div>
</div>
