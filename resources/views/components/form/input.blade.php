@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'prepend' => null,
    'append' => null,
    'error' => null,
])

<div class="mb-3">
    @if ($label)
        <label for="{{ $name }}" class="form-label fw-semibold text-dark">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="{{ $prepend || $append ? 'input-group' : '' }}">
        @if ($prepend)
            <span class="input-group-text">{{ $prepend }}</span>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->merge(['class' => 'form-control border-info-200 focus:border-info focus:ring-info' . ($error || $errors->has($name) ? ' is-invalid border-danger' : '')]) }}
        >

        @if ($append)
            <span class="input-group-text">{{ $append }}</span>
        @endif

        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if ($error)
            <div class="invalid-feedback">{{ $error }}</div>
        @endif
    </div>

    @if ($help)
        <small class="form-text text-muted">{{ $help }}</small>
    @endif
</div>
