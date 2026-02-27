@extends('layouts.app')

@section('title', 'Araç Detayı - Logistics')

@section('styles')
@include('admin.vehicles._vehicle_styles')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Araç Detayı</h2>
        <p class="text-secondary mb-0">Plaka: {{ $vehicle->plate }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">info</span>
                Genel Bilgiler
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Plaka</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->plate }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Ruhsat No</label>
                    <p class="text-dark mb-0">{{ $vehicle->license_number ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Durum</label>
                    <div>
                        @php
                            $statusColors = [0 => 'secondary', 1 => 'success', 2 => 'warning'];
                            $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'Bakımda'];
                            $color = $statusColors[$vehicle->status] ?? 'secondary';
                            $label = $statusLabels[$vehicle->status] ?? '-';
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2 rounded-pill">
                            {{ $label }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Marka</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->brand }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Model</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->model }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Seri</label>
                    <p class="text-dark mb-0">{{ $vehicle->series ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Yıl</label>
                    <p class="text-dark mb-0">{{ $vehicle->year ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Renk</label>
                    <p class="text-dark mb-0">{{ $vehicle->color ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Araç Türü</label>
                    <p class="text-dark mb-0">
                        @php
                            $typeLabels = [
                                'car' => 'Otomobil',
                                'truck' => 'Arazi, SUV & Pickup',
                                'van' => 'Minivan & Panelvan',
                                'motorcycle' => 'Motosiklet',
                                'bus' => 'Ticari Araçlar',
                                'electric' => 'Elektrikli Araçlar',
                                'rental' => 'Kiralık Araçlar',
                                'marine' => 'Deniz Araçları',
                                'damaged' => 'Hasarlı Araçlar',
                                'caravan' => 'Karavan',
                                'classic' => 'Klasik Araçlar',
                                'aircraft' => 'Hava Araçları',
                                'atv' => 'ATV',
                                'utv' => 'UTV',
                                'disabled' => 'Engelli Plakalı Araçlar',
                                'other' => 'Diğer',
                            ];
                        @endphp
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                            {{ $typeLabels[$vehicle->vehicle_type] ?? $vehicle->vehicle_type }}
                        </span>
                    </p>
                </div>
                @if($vehicle->vehicle_subtype)
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Araç Tipi (Alt)</label>
                    <p class="text-dark mb-0">
                        @php
                            $subtypeLabels = [
                                'minibus' => 'Minibüs & Midibüs',
                                'bus' => 'Otobüs',
                                'truck' => 'Kamyon & Kamyonet',
                                'tractor' => 'Çekici',
                                'trailer' => 'Dorse',
                                'caravan' => 'Römork',
                                'bodywork' => 'Karoser & Üst Yapı',
                                'recovery' => 'Oto Kurtarıcı & Taşıyıcı',
                                'commercial' => 'Ticari Hat & Ticari Plaka',
                            ];
                        @endphp
                        {{ $subtypeLabels[$vehicle->vehicle_subtype] ?? $vehicle->vehicle_subtype }}
                    </p>
                </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Yakıt Türü</label>
                    <p class="text-dark mb-0">
                        @php
                            $fuelLabels = [
                                'gasoline' => 'Benzin',
                                'diesel' => 'Dizel',
                                'electric' => 'Elektrik',
                                'hybrid' => 'Hibrit',
                            ];
                        @endphp
                        {{ $vehicle->fuel_type ? ($fuelLabels[$vehicle->fuel_type] ?? $vehicle->fuel_type) : '-' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Vites Türü</label>
                    <p class="text-dark mb-0">
                        @php
                            $transmissionLabels = [
                                'manual' => 'Manuel',
                                'automatic' => 'Otomatik',
                                'other' => 'Diğer',
                            ];
                        @endphp
                        {{ $vehicle->transmission ? ($transmissionLabels[$vehicle->transmission] ?? $vehicle->transmission) : '-' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Sahiplik Türü</label>
                    <p class="text-dark mb-0">
                        @php
                            $ownerLabels = [
                                'owned' => 'Şirket Aracı',
                                'rented' => 'Kiralık Araç',
                            ];
                        @endphp
                        {{ $vehicle->owner_type ? ($ownerLabels[$vehicle->owner_type] ?? $vehicle->owner_type) : '-' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Kilometre</label>
                    <p class="text-dark mb-0">
                        {{ $vehicle->mileage !== null ? number_format($vehicle->mileage).' km' : '-' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Motor No</label>
                    <p class="text-dark mb-0">{{ $vehicle->engine_number ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Şasi (VIN)</label>
                    <p class="text-dark mb-0">{{ $vehicle->vin_number ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">description</span>
                Ruhsat (Ek) Bilgileri
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(B) İlk Tescil Tarihi</label>
                    <p class="text-dark mb-0">{{ $vehicle->first_registration_date?->format('d.m.Y') ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(Y.2) Tescil Sıra No</label>
                    <p class="text-dark mb-0">{{ $vehicle->registration_sequence_no ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(I) Tescil Tarihi</label>
                    <p class="text-dark mb-0">{{ $vehicle->registration_date?->format('d.m.Y') ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(G.1) Net Ağırlığı (kg)</label>
                    <p class="text-dark mb-0">{{ $vehicle->net_weight_kg !== null ? number_format($vehicle->net_weight_kg, 0, ',', '.').' kg' : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(G) Katar Ağırlığı (kg)</label>
                    <p class="text-dark mb-0">{{ $vehicle->train_weight_kg !== null ? number_format($vehicle->train_weight_kg, 0, ',', '.').' kg' : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(G.2) Römork Azami Yüklü (kg)</label>
                    <p class="text-dark mb-0">{{ $vehicle->trailer_max_weight_kg !== null ? number_format($vehicle->trailer_max_weight_kg, 0, ',', '.').' kg' : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(S.1) Koltuk Sayısı</label>
                    <p class="text-dark mb-0">{{ $vehicle->seat_count !== null ? (string) $vehicle->seat_count : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(S.2) Ayakta Yolcu Sayısı</label>
                    <p class="text-dark mb-0">{{ $vehicle->standing_passenger_count !== null ? (string) $vehicle->standing_passenger_count : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(P.1) Silindir Hacmi (cm³)</label>
                    <p class="text-dark mb-0">{{ $vehicle->engine_displacement_cm3 !== null ? number_format($vehicle->engine_displacement_cm3).' cm³' : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(P.2) Motor Gücü (kw)</label>
                    <p class="text-dark mb-0">{{ $vehicle->engine_power_kw !== null ? number_format($vehicle->engine_power_kw, 2).' kw' : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(Y.3) Kullanım Amacı</label>
                    <p class="text-dark mb-0">{{ $vehicle->usage_purpose ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(K) Tip Onay No</label>
                    <p class="text-dark mb-0">{{ $vehicle->type_approval_no ?? '-' }}</p>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">(Y.4) T.C. Kimlik No / Vergi No</label>
                    <p class="text-dark mb-0">{{ $vehicle->owner_id_tax_no ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(C.I.1) Soyadı / Ticari Ünvanı</label>
                    <p class="text-dark mb-0">{{ $vehicle->owner_surname_trade_name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(C.I.2) Adı</label>
                    <p class="text-dark mb-0">{{ $vehicle->owner_first_name ?? '-' }}</p>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">(C.I.3) Adresi</label>
                    <p class="text-dark mb-0">{{ $vehicle->owner_address ?? '-' }}</p>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">(Z.1) Araç Üzerinde Hak ve Menfaati Bulunanlar</label>
                    <p class="text-dark mb-0">{{ $vehicle->rights_holders ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(Z.3.1) Noter Satış Tarihi</label>
                    <p class="text-dark mb-0">{{ $vehicle->notary_sale_date?->format('d.m.Y') ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">(Z.3.2) Noter Satış No</label>
                    <p class="text-dark mb-0">{{ $vehicle->notary_sale_no ?? '-' }}</p>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">(Z.3.3) Noterin Adı</label>
                    <p class="text-dark mb-0">{{ $vehicle->notary_name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @include('admin.vehicles._license-card', [
            'vehicle' => $vehicle,
            'typeLabels' => $typeLabels ?? [
                'car' => 'Otomobil',
                'truck' => 'Arazi, SUV & Pickup',
                'van' => 'Minivan & Panelvan',
                'motorcycle' => 'Motosiklet',
                'bus' => 'Ticari Araçlar',
                'electric' => 'Elektrikli Araçlar',
                'rental' => 'Kiralık Araçlar',
                'marine' => 'Deniz Araçları',
                'damaged' => 'Hasarlı Araçlar',
                'caravan' => 'Karavan',
                'classic' => 'Klasik Araçlar',
                'aircraft' => 'Hava Araçları',
                'atv' => 'ATV',
                'utv' => 'UTV',
                'disabled' => 'Engelli Plakalı Araçlar',
                'other' => 'Diğer',
            ],
            'fuelLabels' => $fuelLabels ?? [
                'gasoline' => 'Benzin',
                'diesel' => 'Dizel',
                'electric' => 'Elektrik',
                'hybrid' => 'Hibrit',
            ],
        ])

        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">straighten</span>
                Kapasite Bilgileri
            </h3>
            <div class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-semibold text-secondary">Kapasite (kg)</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->capacity_kg ? number_format($vehicle->capacity_kg, 2).' kg' : '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Kapasite (m³)</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->capacity_m3 ? number_format($vehicle->capacity_m3, 2).' m³' : '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Şube</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->branch->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">HGS</label>
                    <p class="fw-bold text-dark mb-0">
                        @if($vehicle->hgs_number)
                            {{ $vehicle->hgs_number }}
                            @if($vehicle->hgs_bank)
                                <span class="text-secondary">({{ $vehicle->hgs_bank }})</span>
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if($vehicle->notes)
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">sticky_note_2</span>
                Notlar
            </h3>
            <p class="text-dark mb-0">{{ $vehicle->notes }}</p>
        </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">settings</span>
                Hızlı İşlemler
            </h3>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </a>
                <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Bu aracı silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">delete</span>
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
