@extends('layouts.app')

@section('title', 'Yeni Araç - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Araç Ekle</h2>
        <p class="text-secondary mb-0">Yeni bir araç kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
    <form action="{{ route('admin.vehicles.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Plaka <span class="text-danger">*</span></label>
                <input type="text" name="plate" value="{{ old('plate') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('plate') is-invalid border-danger @enderror" required>
                @error('plate')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Ruhsat No</label>
                <input type="text" name="license_number" value="{{ old('license_number') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('license_number') is-invalid border-danger @enderror">
                @error('license_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-info-200 focus:border-info focus:ring-info @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pasif</option>
                    <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Bakımda</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Marka <span class="text-danger">*</span></label>
                <select name="brand" id="brand" class="form-select border-info-200 focus:border-info focus:ring-info @error('brand') is-invalid border-danger @enderror" required>
                    <option value="">Marka seçiniz</option>
                </select>
                <div id="newBrandInput" class="mt-2" style="display: none;">
                    <input type="text" name="new_brand" class="form-control" placeholder="Yeni marka adı" maxlength="100">
                </div>
                @error('brand')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Seri</label>
                <select name="series" id="series" class="form-select border-info-200 focus:border-info focus:ring-info @error('series') is-invalid border-danger @enderror">
                    <option value="">Seri seçiniz</option>
                </select>
                @error('series')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Model <span class="text-danger">*</span></label>
                <select name="model" id="model" class="form-select border-info-200 focus:border-info focus:ring-info @error('model') is-invalid border-danger @enderror" required>
                    <option value="">Model seçiniz</option>
                </select>
                <div id="newModelInput" class="mt-2" style="display: none;">
                    <input type="text" name="new_model" class="form-control" placeholder="Yeni model adı" maxlength="100">
                </div>
                @error('model')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Yıl</label>
                <input type="number" name="year" value="{{ old('year') }}" min="1900" max="{{ date('Y') }}" class="form-control border-info-200 focus:border-info focus:ring-info">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Renk</label>
                <select name="color" class="form-select border-info-200 focus:border-info focus:ring-info @error('color') is-invalid border-danger @enderror">
                    <option value="">Seçiniz</option>
                    @foreach(($colors ?? []) as $key => $value)
                        <option value="{{ $key }}" {{ old('color') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                @error('color')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Araç Türü <span class="text-danger">*</span></label>
                <select name="vehicle_type" id="vehicle_type" class="form-select border-info-200 focus:border-info focus:ring-info @error('vehicle_type') is-invalid border-danger @enderror" required>
                    <option value="">Seçiniz</option>
                    <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>Otomobil</option>
                    <option value="truck" {{ old('vehicle_type') === 'truck' ? 'selected' : '' }}>Arazi, SUV & Pickup</option>
                    <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Minivan & Panelvan</option>
                    <option value="motorcycle" {{ old('vehicle_type') === 'motorcycle' ? 'selected' : '' }}>Motosiklet</option>
                    <option value="bus" {{ old('vehicle_type') === 'bus' ? 'selected' : '' }}>Ticari Araçlar</option>
                    <option value="electric" {{ old('vehicle_type') === 'electric' ? 'selected' : '' }}>Elektrikli Araçlar</option>
                    <option value="rental" {{ old('vehicle_type') === 'rental' ? 'selected' : '' }}>Kiralık Araçlar</option>
                    <option value="marine" {{ old('vehicle_type') === 'marine' ? 'selected' : '' }}>Deniz Araçları</option>
                    <option value="damaged" {{ old('vehicle_type') === 'damaged' ? 'selected' : '' }}>Hasarlı Araçlar</option>
                    <option value="caravan" {{ old('vehicle_type') === 'caravan' ? 'selected' : '' }}>Karavan</option>
                    <option value="classic" {{ old('vehicle_type') === 'classic' ? 'selected' : '' }}>Klasik Araçlar</option>
                    <option value="aircraft" {{ old('vehicle_type') === 'aircraft' ? 'selected' : '' }}>Hava Araçları</option>
                    <option value="atv" {{ old('vehicle_type') === 'atv' ? 'selected' : '' }}>ATV</option>
                    <option value="utv" {{ old('vehicle_type') === 'utv' ? 'selected' : '' }}>UTV</option>
                    <option value="disabled" {{ old('vehicle_type') === 'disabled' ? 'selected' : '' }}>Engelli Plakalı Araçlar</option>
                    <option value="other" {{ old('vehicle_type') === 'other' ? 'selected' : '' }}>Diğer</option>
                </select>
                @error('vehicle_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Araç Tipi</label>
                <select name="vehicle_subtype" id="vehicle_subtype" class="form-select border-info-200 focus:border-info focus:ring-info @error('vehicle_subtype') is-invalid border-danger @enderror">
                    <option value="">Seçiniz</option>
                </select>
                @error('vehicle_subtype')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Yakıt Türü</label>
                <select name="fuel_type" class="form-select border-info-200 focus:border-info focus:ring-info @error('fuel_type') is-invalid border-danger @enderror">
                    <option value="">Seçiniz</option>
                    <option value="gasoline" {{ old('fuel_type') === 'gasoline' ? 'selected' : '' }}>Benzin</option>
                    <option value="diesel" {{ old('fuel_type') === 'diesel' ? 'selected' : '' }}>Dizel</option>
                    <option value="electric" {{ old('fuel_type') === 'electric' ? 'selected' : '' }}>Elektrik</option>
                    <option value="hybrid" {{ old('fuel_type') === 'hybrid' ? 'selected' : '' }}>Hibrit</option>
                </select>
                @error('fuel_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vites Türü</label>
                <select name="transmission" class="form-select border-info-200 focus:border-info focus:ring-info @error('transmission') is-invalid border-danger @enderror">
                    <option value="">Seçiniz</option>
                    <option value="manual" {{ old('transmission') === 'manual' ? 'selected' : '' }}>Manuel</option>
                    <option value="automatic" {{ old('transmission') === 'automatic' ? 'selected' : '' }}>Otomatik</option>
                    <option value="other" {{ old('transmission') === 'other' ? 'selected' : '' }}>Diğer</option>
                </select>
                @error('transmission')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Sahiplik Türü</label>
                <select name="owner_type" class="form-select border-info-200 focus:border-info focus:ring-info @error('owner_type') is-invalid border-danger @enderror">
                    <option value="">Seçiniz</option>
                    <option value="owned" {{ old('owner_type') === 'owned' ? 'selected' : '' }}>Şirket Aracı</option>
                    <option value="rented" {{ old('owner_type') === 'rented' ? 'selected' : '' }}>Kiralık Araç</option>
                </select>
                @error('owner_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kapasite (kg)</label>
                <input type="number" step="0.01" name="capacity_kg" value="{{ old('capacity_kg') }}" class="form-control border-info-200 focus:border-info focus:ring-info">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kapasite (m³)</label>
                <input type="number" step="0.01" name="capacity_m3" value="{{ old('capacity_m3') }}" class="form-control border-info-200 focus:border-info focus:ring-info">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kilometre</label>
                <input type="number" name="mileage" value="{{ old('mileage') }}" min="0" class="form-control border-info-200 focus:border-info focus:ring-info @error('mileage') is-invalid border-danger @enderror">
                @error('mileage')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Şube</label>
                <select name="branch_id" class="form-select border-info-200 focus:border-info focus:ring-info">
                    <option value="">Şube Seçin</option>
                    @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Motor No</label>
                <input type="text" name="engine_number" value="{{ old('engine_number') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('engine_number') is-invalid border-danger @enderror">
                @error('engine_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Şasi (VIN)</label>
                <input type="text" name="vin_number" value="{{ old('vin_number') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('vin_number') is-invalid border-danger @enderror">
                @error('vin_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">HGS No</label>
                <input type="text" name="hgs_number" value="{{ old('hgs_number') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('hgs_number') is-invalid border-danger @enderror">
                @error('hgs_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">HGS Bankası</label>
                <select name="hgs_bank" class="form-select border-info-200 focus:border-info focus:ring-info @error('hgs_bank') is-invalid border-danger @enderror">
                    <option value="">Seçiniz</option>
                    @foreach(($hgsBanks ?? []) as $key => $value)
                        <option value="{{ $key }}" {{ old('hgs_bank') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                @error('hgs_bank')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold text-dark">Notlar</label>
                <textarea name="notes" rows="3" class="form-control border-info-200 focus:border-info focus:ring-info @error('notes') is-invalid border-danger @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-info-200);">
            <a href="{{ route('admin.vehicles.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Araç Ekle</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
    (function () {
        const subtypeMap = {
            van: { minibus: 'Minibüs & Midibüs', bus: 'Otobüs' },
            truck: { truck: 'Kamyon & Kamyonet', tractor: 'Çekici', trailer: 'Dorse', caravan: 'Römork' },
            bus: {
                minibus: 'Minibüs & Midibüs', bus: 'Otobüs', truck: 'Kamyon & Kamyonet',
                tractor: 'Çekici', trailer: 'Dorse', caravan: 'Römork',
                bodywork: 'Karoser & Üst Yapı', recovery: 'Oto Kurtarıcı & Taşıyıcı', commercial: 'Ticari Hat & Ticari Plaka',
            },
        };
        const vehicleSubtypeBrands = {
            minibus: ['BMC', 'Citroen', 'Fiat', 'Ford Otosan', 'GAZ', 'Hyundai', 'Isuzu', 'Iveco - Otoyol', 'Karsan', 'Kia', 'Magirus', 'MAN', 'Mercedes-Benz', 'Mitsubishi', 'Opel', 'Otokar', 'Peugeot', 'Renault', 'Tata', 'Temsa', 'Tenax', 'Volkswagen'],
            bus: ['AKIA', 'BMC', 'Ford', 'Güleryüz', 'Isobus', 'Isuzu', 'Iveco', 'Karsan', 'MAN', 'Mercedes-Benz', 'Mitsubishi', 'Neoplan', 'Otokar', 'Scania', 'Setra', 'Temsa', 'Tezeller', 'Türkkar'],
            truck: ['Alke', 'Anadol', 'Arora', 'Askam', 'Avia', 'Bedford', 'BMC', 'Chrysler', 'Citroen', 'Dacia', 'Daewoo', 'DAF', 'Daihatsu', 'DFM', 'DFSK', 'Dodge', 'FAW', 'Fiat', 'Folkvan', 'Ford Trucks', 'GAZ', 'HFKanuni', 'Hino', 'Hyundai', 'International', 'Isuzu', 'Iveco', 'IZH', 'JAC', 'Kia', 'Kuba', 'MAN', 'Mazda', 'Mercedes-Benz', 'Mitsubishi - Fuso', 'Mitsubishi - Temsa', 'Nissan', 'Opel', 'Otokar', 'Peugeot', 'Piaggio', 'Proton', 'Relive', 'Renault Trucks', 'Samsung', 'Scania', 'Skoda', 'Suzuki', 'Tata', 'Tenax', 'Toyota', 'Turkar', 'Volkswagen', 'Volvo', 'Diğer Markalar'],
            tractor: ['Askam', 'BMC', 'Chrysler', 'DAF', 'Fiat', 'Ford Trucks', 'Gaz', 'Habaş', 'Iveco', 'Mack', 'MAN', 'MAZ', 'Mercedes-Benz', 'Renault Trucks', 'Scania', 'Tatra', 'Volvo'],
            trailer: ['Damperli', 'Lowbed', 'Kuru Yük', 'Tenteli', 'Frigorifik', 'Tanker', 'Tekstil', 'Silobas', 'Konteyner Taşıyıcı & Şasi Grubu', 'Özel Amaçlı Dorseler'],
            caravan: ['Kamyon Römorkları', 'Tarım Römorkları', 'Taşıma Römorkları', 'Özel Amaçlı Römorklar'],
            bodywork: ['Damperli Grup', 'Sabit Kabin'],
            recovery: ['Tekli Araç', 'Çoklu Araç'],
            commercial: ['Taksi Plakası', 'Minibüs & Dolmuş Hattı', 'Otobüs Hattı', 'Servis Plakası', 'Deniz Hattı', 'Nakliye Araçları'],
        };
        const vehicleSubtypeBrandSeries = {
            tractor: {
                'Mercedes-Benz': ['Actros', 'Arocs', 'Axor'],
                'Ford Trucks': ['Cargo', 'F-Line', 'F-Max'],
            },
        };
        const vehicleSubtypeBrandSeriesModels = {
            tractor: {
                'Mercedes-Benz': {
                    Actros: ['1840', '1841', '1842', '1843', '1844', '1845', '1846', '1848', '1850', '1851', '1853', '1863', '1941 LS', '3341', '3346'],
                    Arocs: ['1842 LS', '3342 S', '3351 S', '3358 S'],
                    Axor: ['1835 LS', '1836 LS', '1840 LS', '1938 LS', '2622'],
                },
                'Ford Trucks': {
                    Cargo: ['1830', '1835T', '1838T', '1842T', '1846T', '1848T', '1848T Midilli', '3548T'],
                    'F-Line': ['1845 T'],
                    'F-Max': ['500', '500 Comfort Plus', '500 L', '500 Midilli', '1842 T', '1850 T'],
                },
            },
        };
        const serverBrands = @json($brands ?? []);
        const serverModels = @json($models ?? []);
        const oldSubtype = '{{ old('vehicle_subtype') }}';
        const oldBrand = '{{ old('brand') }}';
        const oldSeries = '{{ old('series') }}';
        const oldModel = '{{ old('model') }}';

        function updateSubtypeOptions() {
            const typeSelect = document.getElementById('vehicle_type');
            const subtypeSelect = document.getElementById('vehicle_subtype');
            if (!typeSelect || !subtypeSelect) return;
            const currentType = typeSelect.value;
            subtypeSelect.innerHTML = '<option value="">Seçiniz</option>';
            if (!currentType || !subtypeMap[currentType]) {
                updateBrandDropdown();
                return;
            }
            Object.entries(subtypeMap[currentType]).forEach(([value, label]) => {
                const opt = document.createElement('option');
                opt.value = value;
                opt.textContent = label;
                if (value === oldSubtype) opt.selected = true;
                subtypeSelect.appendChild(opt);
            });
            updateBrandDropdown();
        }

        function updateBrandDropdown() {
            const subtypeSelect = document.getElementById('vehicle_subtype');
            const brandSelect = document.getElementById('brand');
            if (!subtypeSelect || !brandSelect) return;
            const subtype = subtypeSelect.value;
            const brands = (subtype && vehicleSubtypeBrands[subtype]) ? vehicleSubtypeBrands[subtype] : serverBrands;
            brandSelect.innerHTML = '<option value="">Marka seçiniz</option>';
            brands.forEach(function (b) {
                const opt = document.createElement('option');
                opt.value = b;
                opt.textContent = b;
                if (b === oldBrand) opt.selected = true;
                brandSelect.appendChild(opt);
            });
            const otherOpt = document.createElement('option');
            otherOpt.value = 'other';
            otherOpt.textContent = '+ Yeni Marka Ekle';
            if (oldBrand === 'other') otherOpt.selected = true;
            brandSelect.appendChild(otherOpt);
            updateSeriesDropdown();
            toggleNewBrand();
        }

        function updateSeriesDropdown() {
            const subtypeSelect = document.getElementById('vehicle_subtype');
            const brandSelect = document.getElementById('brand');
            const seriesSelect = document.getElementById('series');
            if (!subtypeSelect || !brandSelect || !seriesSelect) return;
            const subtype = subtypeSelect.value;
            const brand = brandSelect.value;
            seriesSelect.innerHTML = '<option value="">Seri seçiniz</option>';
            if (subtype && brand && vehicleSubtypeBrandSeries[subtype] && vehicleSubtypeBrandSeries[subtype][brand]) {
                vehicleSubtypeBrandSeries[subtype][brand].forEach(function (s) {
                    const opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    if (s === oldSeries) opt.selected = true;
                    seriesSelect.appendChild(opt);
                });
            }
            updateModelDropdown();
        }

        function updateModelDropdown() {
            const subtypeSelect = document.getElementById('vehicle_subtype');
            const brandSelect = document.getElementById('brand');
            const seriesSelect = document.getElementById('series');
            const modelSelect = document.getElementById('model');
            if (!subtypeSelect || !brandSelect || !seriesSelect || !modelSelect) return;
            const subtype = subtypeSelect.value;
            const brand = brandSelect.value;
            const series = seriesSelect.value;
            let models = [];
            if (subtype && brand && series && vehicleSubtypeBrandSeriesModels[subtype] && vehicleSubtypeBrandSeriesModels[subtype][brand] && vehicleSubtypeBrandSeriesModels[subtype][brand][series]) {
                models = vehicleSubtypeBrandSeriesModels[subtype][brand][series];
            } else {
                models = serverModels;
            }
            modelSelect.innerHTML = '<option value="">Model seçiniz</option>';
            models.forEach(function (m) {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = m;
                if (m === oldModel) opt.selected = true;
                modelSelect.appendChild(opt);
            });
            const otherOpt = document.createElement('option');
            otherOpt.value = 'other';
            otherOpt.textContent = '+ Yeni Model Ekle';
            if (oldModel === 'other') otherOpt.selected = true;
            modelSelect.appendChild(otherOpt);
            toggleNewModel();
        }

        function toggleNewBrand() {
            const brandSelect = document.getElementById('brand');
            const newBrandInput = document.getElementById('newBrandInput');
            if (!brandSelect || !newBrandInput) return;
            newBrandInput.style.display = brandSelect.value === 'other' ? 'block' : 'none';
        }

        function toggleNewModel() {
            const modelSelect = document.getElementById('model');
            const newModelInput = document.getElementById('newModelInput');
            if (!modelSelect || !newModelInput) return;
            newModelInput.style.display = modelSelect.value === 'other' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('vehicle_type');
            const subtypeSelect = document.getElementById('vehicle_subtype');
            const brandSelect = document.getElementById('brand');
            const seriesSelect = document.getElementById('series');
            const modelSelect = document.getElementById('model');
            if (typeSelect) typeSelect.addEventListener('change', updateSubtypeOptions);
            if (subtypeSelect) subtypeSelect.addEventListener('change', updateBrandDropdown);
            if (brandSelect) brandSelect.addEventListener('change', function () { updateSeriesDropdown(); toggleNewBrand(); });
            if (seriesSelect) seriesSelect.addEventListener('change', function () { updateModelDropdown(); });
            if (modelSelect) modelSelect.addEventListener('change', toggleNewModel);
            updateSubtypeOptions();
        });
    })();
</script>
@endpush
@endsection
