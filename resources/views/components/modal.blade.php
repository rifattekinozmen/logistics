@props([
    'id',
    'title' => null,
    'size' => 'md',
    'centered' => false,
    'scrollable' => false,
    'footer' => null,
    'closeButton' => true,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        default => '',
    };
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $sizeClass }} {{ $centered ? 'modal-dialog-centered' : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }}">
        <div class="modal-content border-0 shadow rounded-3xl">
            @if ($title)
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold" id="{{ $id }}Label">{{ $title }}</h5>
                    @if ($closeButton)
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    @endif
                </div>
            @endif
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if ($footer)
                <div class="modal-footer border-0">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
