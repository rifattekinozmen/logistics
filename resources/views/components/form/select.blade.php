@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Seçiniz...',
    'required' => false,
    'disabled' => false,
    'help' => null,
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

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'form-select border-info-200 focus:border-info focus:ring-info' . ($error || $errors->has($name) ? ' is-invalid border-danger' : '')]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option
                value="{{ $optionValue }}"
                {{ old($name, $value) == $optionValue ? 'selected' : '' }}
            >
                {{ $optionLabel }}
            </option>
        @endforeach

        {{ $slot }}
    </select>

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
