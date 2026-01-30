@extends('layouts.app')

@section('title', 'Yeni Motorin Fiyatı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Motorin Fiyatı</h2>
        <p class="text-secondary mb-0">Günlük motorin fiyat kaydı oluşturun</p>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.fuel-prices.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="price_date" class="form-label fw-semibold text-dark">Tarih</label>
                <input type="date" 
                       name="price_date" 
                       id="price_date" 
                       class="form-control @error('price_date') is-invalid @enderror"
                       value="{{ old('price_date', date('Y-m-d')) }}"
                       required>
                @error('price_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="price_type" class="form-label fw-semibold text-dark">Fiyat Türü</label>
                <select name="price_type" 
                        id="price_type" 
                        class="form-select @error('price_type') is-invalid @enderror"
                        required>
                    <option value="">Seçiniz</option>
                    <option value="purchase" {{ old('price_type') === 'purchase' ? 'selected' : '' }}>Satın Alma</option>
                    <option value="station" {{ old('price_type') === 'station' ? 'selected' : '' }}>İstasyon</option>
                </select>
                @error('price_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="price" class="form-label fw-semibold text-dark">Fiyat (TL/Litre)</label>
                <input type="number" 
                       step="0.0001" 
                       min="0"
                       name="price" 
                       id="price" 
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price') }}"
                       required>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="supplier_name" class="form-label fw-semibold text-dark">Tedarikçi Adı</label>
                <input type="text" 
                       name="supplier_name" 
                       id="supplier_name" 
                       class="form-control @error('supplier_name') is-invalid @enderror"
                       value="{{ old('supplier_name') }}">
                @error('supplier_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="region" class="form-label fw-semibold text-dark">Bölge</label>
                <input type="text" 
                       name="region" 
                       id="region" 
                       class="form-control @error('region') is-invalid @enderror"
                       value="{{ old('region') }}">
                @error('region')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                <textarea name="notes" 
                          id="notes" 
                          class="form-control @error('notes') is-invalid @enderror"
                          rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.fuel-prices.index') }}" class="btn btn-outline-secondary">
                İptal
            </a>
            <button type="submit" class="btn btn-primary">
                Kaydet
            </button>
        </div>
    </form>
</div>
@endsection
