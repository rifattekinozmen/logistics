@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'error' => null,
    'rows' => 3,
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

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $attributes->merge(['class' => 'form-control border-info-200 focus:border-info focus:ring-info' . ($error || $errors->has($name) ? ' is-invalid border-danger' : '')]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if ($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif

    @if ($help)
        <small class="form-text text-muted">{{ $help }}</small>
    @endif
</div>
