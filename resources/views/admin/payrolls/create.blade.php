@extends('layouts.app')

@section('title', 'Yeni Bordro - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Bordro</h2>
        <p class="text-secondary mb-0">Yeni bordro oluşturun</p>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.payrolls.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="employee_id" class="form-label fw-semibold text-dark">Personel</label>
                <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                    <option value="">Seçiniz</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-salary="{{ $employee->salary ?? 0 }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_number }})
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="period_start" class="form-label fw-semibold text-dark">Dönem Başlangıç</label>
                <input type="date" name="period_start" id="period_start" class="form-control @error('period_start') is-invalid @enderror" value="{{ old('period_start') }}" required>
                @error('period_start')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="period_end" class="form-label fw-semibold text-dark">Dönem Bitiş</label>
                <input type="date" name="period_end" id="period_end" class="form-control @error('period_end') is-invalid @enderror" value="{{ old('period_end') }}" required>
                @error('period_end')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="base_salary" class="form-label fw-semibold text-dark">Brüt Maaş (₺)</label>
                <input type="number" step="0.01" min="0" name="base_salary" id="base_salary" class="form-control @error('base_salary') is-invalid @enderror" value="{{ old('base_salary') }}" required>
                @error('base_salary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="overtime_amount" class="form-label fw-semibold text-dark">Fazla Mesai (₺)</label>
                <input type="number" step="0.01" min="0" name="overtime_amount" id="overtime_amount" class="form-control @error('overtime_amount') is-invalid @enderror" value="{{ old('overtime_amount', 0) }}">
                @error('overtime_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="bonus" class="form-label fw-semibold text-dark">Prim (₺)</label>
                <input type="number" step="0.01" min="0" name="bonus" id="bonus" class="form-control @error('bonus') is-invalid @enderror" value="{{ old('bonus', 0) }}">
                @error('bonus')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="deduction" class="form-label fw-semibold text-dark">Kesinti (₺)</label>
                <input type="number" step="0.01" min="0" name="deduction" id="deduction" class="form-control @error('deduction') is-invalid @enderror" value="{{ old('deduction', 0) }}">
                @error('deduction')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="tax" class="form-label fw-semibold text-dark">Vergi (₺)</label>
                <input type="number" step="0.01" min="0" name="tax" id="tax" class="form-control @error('tax') is-invalid @enderror" value="{{ old('tax', 0) }}">
                @error('tax')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="social_security" class="form-label fw-semibold text-dark">SGK (₺)</label>
                <input type="number" step="0.01" min="0" name="social_security" id="social_security" class="form-control @error('social_security') is-invalid @enderror" value="{{ old('social_security', 0) }}">
                @error('social_security')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
    </form>
</div>
@endsection
