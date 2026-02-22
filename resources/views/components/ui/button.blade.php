@props([
    'variant' => 'primary',
    'size' => 'sm',
    'as' => 'button',
    'href' => null,
    'icon' => null,
    'block' => false,
    'active' => false,
    'disabled' => false,
    'class' => '',
])

@php
    $base = 'btn d-flex align-items-center justify-content-center gap-2';
    $sizes = ['sm' => 'btn-sm', 'md' => '', 'lg' => 'btn-lg'];
    $variantClass = 'btn-' . $variant;
    $classes = trim(collect([
        $base,
        $variantClass,
        $sizes[$size] ?? '',
        $block ? 'w-100' : '',
        $active ? 'active' : '',
        $disabled ? 'disabled' : '',
        $class,
    ])->filter()->implode(' '));
@endphp

@if ($as === 'a' && $href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if ($disabled) aria-disabled="true" tabindex="-1" @endif>
        @if ($icon)
            <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $icon }}</span>
        @endif
        @if ($slot->isNotEmpty())
            <span>{{ $slot }}</span>
        @endif
    </a>
@elseif ($as === 'submit')
    <button type="submit" {{ $attributes->merge(['class' => $classes]) }} @disabled($disabled)>
        @if ($icon)
            <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $icon }}</span>
        @endif
        @if ($slot->isNotEmpty())
            <span>{{ $slot }}</span>
        @endif
    </button>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }} @disabled($disabled)>
        @if ($icon)
            <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $icon }}</span>
        @endif
        @if ($slot->isNotEmpty())
            <span>{{ $slot }}</span>
        @endif
    </button>
@endif
