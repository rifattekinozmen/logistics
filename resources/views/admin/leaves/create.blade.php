@extends('layouts.app')

@section('title', 'Yeni İzin Talebi - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni İzin Talebi</h2>
        <p class="text-secondary mb-0">Yeni izin talebi oluşturun</p>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.leaves.store') }}" method="POST">
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
                <label for="leave_type" class="form-label fw-semibold text-dark">İzin Türü</label>
                <select name="leave_type" id="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                    <option value="">Seçiniz</option>
                    <option value="annual" {{ old('leave_type') === 'annual' ? 'selected' : '' }}>Yıllık İzin</option>
                    <option value="sick" {{ old('leave_type') === 'sick' ? 'selected' : '' }}>Hastalık İzni</option>
                    <option value="unpaid" {{ old('leave_type') === 'unpaid' ? 'selected' : '' }}>Ücretsiz İzin</option>
                    <option value="other" {{ old('leave_type') === 'other' ? 'selected' : '' }}>Diğer</option>
                </select>
                @error('leave_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="start_date" class="form-label fw-semibold text-dark">Başlangıç Tarihi</label>
                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="end_date" class="form-label fw-semibold text-dark">Bitiş Tarihi</label>
                <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                @error('end_date')
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
            <a href="{{ route('admin.leaves.index') }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
    </form>
</div>
@endsection
