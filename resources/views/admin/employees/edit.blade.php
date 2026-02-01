@extends('layouts.app')

@section('title', 'Personel Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel Düzenle</h2>
        <p class="text-secondary mb-0">Personel bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Ad <span class="text-danger">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="form-control @error('first_name') is-invalid @enderror" required>
                @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Soyad <span class="text-danger">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="form-control @error('last_name') is-invalid @enderror" required>
                @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Personel No</label>
                <input type="text" name="employee_number" value="{{ old('employee_number', $employee->employee_number) }}" class="form-control @error('employee_number') is-invalid @enderror">
                @error('employee_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Şube <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                    <option value="">Şube Seçin</option>
                    @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
                @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Pozisyon</label>
                <select name="position_id" class="form-select">
                    <option value="">Pozisyon Seçin</option>
                    @foreach($positions ?? [] as $position)
                    <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>
                        {{ $position->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">İşe Başlama Tarihi <span class="text-danger">*</span></label>
                <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" class="form-control @error('hire_date') is-invalid @enderror" required>
                @error('hire_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">E-posta</label>
                <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="form-control @error('email') is-invalid @enderror">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Maaş</label>
                <input type="number" step="0.01" name="salary" value="{{ old('salary', $employee->salary) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="1" {{ old('status', $employee->status) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status', $employee->status) == 0 ? 'selected' : '' }}>Pasif</option>
                    <option value="2" {{ old('status', $employee->status) == 2 ? 'selected' : '' }}>İzinli</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top">
            <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-light">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
