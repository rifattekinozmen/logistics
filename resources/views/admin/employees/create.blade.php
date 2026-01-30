@extends('layouts.app')

@section('title', 'Yeni Personel - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Personel Ekle</h2>
        <p class="text-secondary mb-0">Yeni bir personel kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('admin.employees.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Ad <span class="text-danger">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('first_name') is-invalid border-danger @enderror" required>
                @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Soyad <span class="text-danger">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('last_name') is-invalid border-danger @enderror" required>
                @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Personel No</label>
                <input type="text" name="employee_number" value="{{ old('employee_number') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('employee_number') is-invalid border-danger @enderror">
                <small class="text-secondary">Boş bırakılırsa otomatik oluşturulur</small>
                @error('employee_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Şube <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('branch_id') is-invalid border-danger @enderror" required>
                    <option value="">Şube Seçin</option>
                    @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
                @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Pozisyon</label>
                <select name="position_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary">
                    <option value="">Pozisyon Seçin</option>
                    @foreach($positions ?? [] as $position)
                    <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                        {{ $position->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">İşe Başlama Tarihi <span class="text-danger">*</span></label>
                <input type="date" name="hire_date" value="{{ old('hire_date') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('hire_date') is-invalid border-danger @enderror" required>
                @error('hire_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">E-posta</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('email') is-invalid border-danger @enderror">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Maaş</label>
                <input type="number" step="0.01" name="salary" value="{{ old('salary') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pasif</option>
                    <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>İzinli</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
            <a href="{{ route('admin.employees.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Personel Ekle</button>
        </div>
    </form>
</div>
@endsection
