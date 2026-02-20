@extends('layouts.app')

@section('title', 'Fiyatlandırma Koşulu Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Fiyatlandırma Koşulu Düzenle</h2>
        <p class="text-secondary mb-0">{{ $condition->name }}</p>
    </div>
    <a href="{{ route('admin.pricing-conditions.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.pricing-conditions.update', $condition->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Koşul Türü <span class="text-danger">*</span></label>
                <select name="condition_type" class="form-select @error('condition_type') is-invalid @enderror" required>
                    <option value="weight_based"   {{ old('condition_type', $condition->condition_type) === 'weight_based'   ? 'selected' : '' }}>Ağırlık Bazlı</option>
                    <option value="distance_based" {{ old('condition_type', $condition->condition_type) === 'distance_based' ? 'selected' : '' }}>Mesafe Bazlı</option>
                    <option value="flat"           {{ old('condition_type', $condition->condition_type) === 'flat'           ? 'selected' : '' }}>Sabit Ücret</option>
                    <option value="zone_based"     {{ old('condition_type', $condition->condition_type) === 'zone_based'     ? 'selected' : '' }}>Bölge Bazlı</option>
                </select>
                @error('condition_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Koşul Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $condition->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Para Birimi <span class="text-danger">*</span></label>
                <select name="currency" class="form-select" required>
                    <option value="TRY" {{ old('currency', $condition->currency) === 'TRY' ? 'selected' : '' }}>TRY</option>
                    <option value="USD" {{ old('currency', $condition->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('currency', $condition->currency) === 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Araç Tipi</label>
                <select name="vehicle_type" class="form-select">
                    <option value="">Tümü</option>
                    <option value="truck"   {{ old('vehicle_type', $condition->vehicle_type) === 'truck'   ? 'selected' : '' }}>Kamyon</option>
                    <option value="van"     {{ old('vehicle_type', $condition->vehicle_type) === 'van'     ? 'selected' : '' }}>Minivan</option>
                    <option value="trailer" {{ old('vehicle_type', $condition->vehicle_type) === 'trailer' ? 'selected' : '' }}>TIR</option>
                </select>
            </div>

            <div class="col-12"><hr class="text-muted"></div>
            <div class="col-12"><h6 class="fw-semibold text-dark mb-0">Güzergah</h6></div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Çıkış Şehri</label>
                <input type="text" name="route_origin" value="{{ old('route_origin', $condition->route_origin) }}" class="form-control" placeholder="Boş = Tümü">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Varış Şehri</label>
                <input type="text" name="route_destination" value="{{ old('route_destination', $condition->route_destination) }}" class="form-control" placeholder="Boş = Tümü">
            </div>

            <div class="col-12"><hr class="text-muted"></div>
            <div class="col-12"><h6 class="fw-semibold text-dark mb-0">Fiyatlandırma Parametreleri</h6></div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Min. Ağırlık (kg)</label>
                <input type="number" step="0.01" name="weight_from" value="{{ old('weight_from', $condition->weight_from) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Maks. Ağırlık (kg)</label>
                <input type="number" step="0.01" name="weight_to" value="{{ old('weight_to', $condition->weight_to) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fiyat / kg</label>
                <input type="number" step="0.0001" name="price_per_kg" value="{{ old('price_per_kg', $condition->price_per_kg) }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Min. Mesafe (km)</label>
                <input type="number" step="0.01" name="distance_from" value="{{ old('distance_from', $condition->distance_from) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Maks. Mesafe (km)</label>
                <input type="number" step="0.01" name="distance_to" value="{{ old('distance_to', $condition->distance_to) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fiyat / km</label>
                <input type="number" step="0.0001" name="price_per_km" value="{{ old('price_per_km', $condition->price_per_km) }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Sabit Ücret</label>
                <input type="number" step="0.01" name="flat_rate" value="{{ old('flat_rate', $condition->flat_rate) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Min. Ücret</label>
                <input type="number" step="0.01" name="min_charge" value="{{ old('min_charge', $condition->min_charge) }}" class="form-control">
            </div>

            <div class="col-12"><hr class="text-muted"></div>
            <div class="col-12"><h6 class="fw-semibold text-dark mb-0">Geçerlilik ve Durum</h6></div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Başlangıç Tarihi</label>
                <input type="date" name="valid_from" value="{{ old('valid_from', $condition->valid_from?->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Bitiş Tarihi</label>
                <input type="date" name="valid_to" value="{{ old('valid_to', $condition->valid_to?->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="1" {{ old('status', $condition->status) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status', $condition->status) == 0 ? 'selected' : '' }}>Pasif</option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Notlar</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $condition->notes) }}</textarea>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top">
            <a href="{{ route('admin.pricing-conditions.index') }}" class="btn btn-light">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
