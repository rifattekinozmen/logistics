@extends('layouts.app')

@section('title', 'Yeni Avans Talebi - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Avans Talebi</h2>
        <p class="text-secondary mb-0">Yeni avans talebi oluşturun</p>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.advances.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="employee_id" class="form-label fw-semibold text-dark">Personel</label>
                <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                    <option value="">Seçiniz</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_number }})
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="amount" class="form-label fw-semibold text-dark">Tutar (₺)</label>
                <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                @error('amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="reason" class="form-label fw-semibold text-dark">Gerekçe</label>
                <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3">{{ old('reason') }}</textarea>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.advances.index') }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
    </form>
</div>
@endsection
