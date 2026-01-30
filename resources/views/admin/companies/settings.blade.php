@extends('layouts.app')

@section('title', 'Firma Ayarları - Logistics')
@section('page-title', 'Firma Ayarları')
@section('page-subtitle', $company->name)

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">check_circle</span>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">error</span>
    <strong>Hata!</strong> Lütfen formu kontrol edin.
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- Company Info Card -->
    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 text-center" style="border-color: var(--bs-primary-200);">
            <!-- Logo Section -->
            <div class="mb-3">
                @php
                    $companyLogoUrl = $company->logo_url;
                @endphp
                @if($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}?v={{ time() }}" alt="{{ $company->name }}" class="rounded-3xl shadow-lg mx-auto mb-3" style="max-width: 100%; height: auto; max-height: 150px; object-fit: contain;" id="sidebar-logo-preview">
                @else
                    <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary mx-auto mb-3" style="width: 120px; height: 120px;" id="sidebar-logo-placeholder">
                        <span class="material-symbols-outlined" style="font-size: 40px;">business</span>
                    </div>
                @endif
                
                <!-- Logo Upload Controls -->
                <div class="mt-3">
                    <input type="file" name="logo" accept="image/*,.svg,image/svg+xml" class="form-control form-control-sm border-primary-200 focus:border-primary focus:ring-primary mb-2" id="sidebar-logo-input" style="display: none;">
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-primary btn-sm" id="sidebar-logo-upload-btn" onclick="document.getElementById('sidebar-logo-input').click();">
                            <span class="material-symbols-outlined" style="font-size: 18px;">upload</span>
                            <span class="d-none d-md-inline">Yükle</span>
                        </button>
                        @if($companyLogoUrl)
                        <button type="button" class="btn btn-danger btn-sm" id="sidebar-logo-delete-btn" onclick="deleteLogo(event)">
                            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                            <span class="d-none d-md-inline">Sil</span>
                        </button>
                        @endif
                    </div>
                    <small class="text-secondary d-block mt-2">JPG, PNG, GIF veya SVG (Max: 2MB)</small>
                </div>
            </div>
            <h3 class="h5 fw-bold text-dark mb-1">{{ $company->short_name ?? $company->name }}</h3>
            @if($company->short_name && $company->short_name !== $company->name)
            <p class="text-secondary mb-1 small">{{ $company->name }}</p>
            @endif
            @if($company->tax_number)
            <p class="text-secondary mb-3">
                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">receipt</span>
                {{ $company->tax_number }}
            </p>
            @endif
            
            <!-- Status -->
            <div class="mt-3 pt-3 border-top">
                <span class="badge {{ $company->is_active ? 'bg-success-200 text-success' : 'bg-secondary-200 text-secondary' }} px-3 py-2 rounded-pill">
                    {{ $company->is_active ? 'Aktif' : 'Pasif' }}
                </span>
                @if(session('active_company_id') == $company->id)
                <span class="badge bg-primary text-white px-3 py-2 rounded-pill ms-2">Aktif Firma</span>
                @endif
            </div>
            
            @if($company->currency)
            <div class="mt-3">
                <p class="small text-secondary mb-1 fw-semibold">Para Birimi</p>
                <p class="text-dark mb-0">{{ $company->currency }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Company Settings Form -->
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <!-- Tabs -->
            <ul class="nav nav-tabs border-0 mb-4" id="companySettingsTabs" role="tablist">
                <!-- 1. Genel Bilgiler -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-3xl me-2" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <span class="material-symbols-outlined me-2" style="font-size: 18px;">info</span>
                        Genel Bilgiler
                    </button>
                </li>
                <!-- 2. İletişim & Adresler -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-3xl me-2" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button" role="tab">
                        <span class="material-symbols-outlined me-2" style="font-size: 18px;">location_on</span>
                        İletişim & Adresler
                    </button>
                </li>
                <!-- 3. Sistem Ayarları -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-3xl me-2" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">
                        <span class="material-symbols-outlined me-2" style="font-size: 18px;">settings</span>
                        Sistem Ayarları
                    </button>
                </li>
                <!-- 4. Dijital Mali Hizmetlerim -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-3xl me-2" id="digital-services-tab" data-bs-toggle="tab" data-bs-target="#digital-services" type="button" role="tab">
                        <span class="material-symbols-outlined me-2" style="font-size: 18px;">receipt_long</span>
                        Dijital Mali Hizmetlerim
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="companySettingsTabsContent">
                <!-- General Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <h4 class="h5 fw-bold text-dark mb-4">Genel Bilgiler</h4>
                    
                    <form action="{{ route('admin.companies.settings.general.update', $company) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <!-- Ticari Ünvan / Ad Soyad -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Ticari Ünvan / Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $company->name) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('name') is-invalid border-danger @enderror" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ünvan -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Ünvan</label>
                                <input type="text" name="title" value="{{ old('title', $company->title) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('title') is-invalid border-danger @enderror">
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- VKN/TCKN -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">VKN/TCKN <span class="text-danger">*</span></label>
                                <input type="text" name="tax_number" value="{{ old('tax_number', $company->tax_number) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('tax_number') is-invalid border-danger @enderror" required>
                                @error('tax_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kısa İsim -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Kısa İsim</label>
                                <input type="text" name="short_name" value="{{ old('short_name', $company->short_name) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('short_name') is-invalid border-danger @enderror">
                                @error('short_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ülke -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Ülke <span class="text-danger">*</span></label>
                                <select name="country_id" id="country_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('country_id') is-invalid border-danger @enderror" required>
                                    <option value="">Seçiniz</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id', $company->country_id) == $country->id ? 'selected' : '' }}>
                                            {{ $country->name_tr }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- İl -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">İl <span class="text-danger">*</span></label>
                                <select name="city_id" id="city_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('city_id') is-invalid border-danger @enderror" required>
                                    <option value="">Seçiniz</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('city_id', $company->city_id) == $city->id ? 'selected' : '' }}>
                                            {{ $city->name_tr }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('city_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- İlçe -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">İlçe <span class="text-danger">*</span></label>
                                <select name="district_id" id="district_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('district_id') is-invalid border-danger @enderror" required>
                                    <option value="">Seçiniz</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}" {{ old('district_id', $company->district_id) == $district->id ? 'selected' : '' }}>
                                            {{ $district->name_tr }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('district_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Adres -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Adres <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('address') is-invalid border-danger @enderror" rows="2" required>{{ old('address', $company->address) }}</textarea>
                                @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Posta Kodu -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Posta Kodu</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('postal_code') is-invalid border-danger @enderror">
                                @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- İşletme Merkezi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">İşletme Merkezi <span class="text-danger">*</span></label>
                                <input type="text" name="headquarters_city" value="{{ old('headquarters_city', $company->headquarters_city) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('headquarters_city') is-invalid border-danger @enderror" required>
                                @error('headquarters_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ad Soyad -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="authorized_person_name" value="{{ old('authorized_person_name', $company->authorized_person_name) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('authorized_person_name') is-invalid border-danger @enderror" required>
                                @error('authorized_person_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cep Telefonu -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Cep Telefonu <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_phone" value="{{ old('mobile_phone', $company->mobile_phone) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('mobile_phone') is-invalid border-danger @enderror" required>
                                @error('mobile_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sabit Hat -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Sabit Hat</label>
                                <input type="text" name="landline_phone" value="{{ old('landline_phone', $company->landline_phone) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('landline_phone') is-invalid border-danger @enderror">
                                @error('landline_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Faks -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Faks</label>
                                <input type="text" name="fax" value="{{ old('fax', $company->fax) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('fax') is-invalid border-danger @enderror">
                                @error('fax')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Yetkili e-Posta -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Yetkili e-Posta <span class="text-danger">*</span></label>
                                <input type="email" name="authorized_email" value="{{ old('authorized_email', $company->authorized_email) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('authorized_email') is-invalid border-danger @enderror" required>
                                @error('authorized_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Firma e-Posta -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Firma e-Posta</label>
                                <input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('email') is-invalid border-danger @enderror">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- İnternet Sitesi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">İnternet Sitesi</label>
                                <input type="url" name="website" value="{{ old('website', $company->website) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('website') is-invalid border-danger @enderror" placeholder="https://">
                                @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vergi Dairesi İli -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Vergi Dairesi İli <span class="text-danger">*</span></label>
                                <select name="tax_office_city_id" id="tax_office_city_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('tax_office_city_id') is-invalid border-danger @enderror" required>
                                    <option value="">Seçiniz</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('tax_office_city_id', $company->tax_office_city_id) == $city->id ? 'selected' : '' }}>
                                            {{ $city->name_tr }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tax_office_city_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vergi Dairesi Adı -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Vergi Dairesi Adı <span class="text-danger">*</span></label>
                                <input type="text" name="tax_office" value="{{ old('tax_office', $company->tax_office) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('tax_office') is-invalid border-danger @enderror" required>
                                @error('tax_office')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- MERSIS Numarası -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">MERSIS Numarası</label>
                                <input type="text" name="mersis_no" value="{{ old('mersis_no', $company->mersis_no) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('mersis_no') is-invalid border-danger @enderror">
                                @error('mersis_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sicil Numarası -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Sicil Numarası</label>
                                <input type="text" name="trade_registry_no" value="{{ old('trade_registry_no', $company->trade_registry_no) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('trade_registry_no') is-invalid border-danger @enderror">
                                @error('trade_registry_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sermaye Miktarı -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Sermaye Miktarı</label>
                                <input type="number" name="capital_amount" value="{{ old('capital_amount', $company->capital_amount) }}" step="0.01" min="0" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('capital_amount') is-invalid border-danger @enderror">
                                @error('capital_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- e-Fatura Pk Etiket Bilgisi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">e-Fatura Pk Etiket Bilgisi</label>
                                <input type="text" name="e_invoice_pk_tag" value="{{ old('e_invoice_pk_tag', $company->e_invoice_pk_tag) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('e_invoice_pk_tag') is-invalid border-danger @enderror">
                                @error('e_invoice_pk_tag')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- e-İrsaliye Pk Etiket Bilgisi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">e-İrsaliye Pk Etiket Bilgisi</label>
                                <input type="text" name="e_waybill_pk_tag" value="{{ old('e_waybill_pk_tag', $company->e_waybill_pk_tag) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('e_waybill_pk_tag') is-invalid border-danger @enderror">
                                @error('e_waybill_pk_tag')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- e-Fatura Gb Etiket Bilgisi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">e-Fatura Gb Etiket Bilgisi</label>
                                <input type="text" name="e_invoice_gb_tag" value="{{ old('e_invoice_gb_tag', $company->e_invoice_gb_tag) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('e_invoice_gb_tag') is-invalid border-danger @enderror">
                                @error('e_invoice_gb_tag')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- e-İrsaliye Gb Etiket Bilgisi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">e-İrsaliye Gb Etiket Bilgisi</label>
                                <input type="text" name="e_waybill_gb_tag" value="{{ old('e_waybill_gb_tag', $company->e_waybill_gb_tag) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('e_waybill_gb_tag') is-invalid border-danger @enderror">
                                @error('e_waybill_gb_tag')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Firma Apikey -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Firma Apikey</label>
                                <input type="text" name="api_key" value="{{ old('api_key', $company->api_key) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('api_key') is-invalid border-danger @enderror">
                                @error('api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Para Birimi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Para Birimi <span class="text-danger">*</span></label>
                                <select name="currency" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('currency') is-invalid border-danger @enderror" required>
                                    <option value="TRY" {{ old('currency', $company->currency) === 'TRY' ? 'selected' : '' }}>TRY - Türk Lirası</option>
                                    <option value="USD" {{ old('currency', $company->currency) === 'USD' ? 'selected' : '' }}>USD - Amerikan Doları</option>
                                    <option value="EUR" {{ old('currency', $company->currency) === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                </select>
                                @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Varsayılan KDV Oranı -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Varsayılan KDV Oranı (%) <span class="text-danger">*</span></label>
                                <input type="number" name="default_vat_rate" value="{{ old('default_vat_rate', $company->default_vat_rate) }}" step="0.01" min="0" max="100" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('default_vat_rate') is-invalid border-danger @enderror" required>
                                @error('default_vat_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <!-- Stamp Upload -->
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Kaşe/İmza</label>
                                <div class="mb-2">
                                    @php
                                        $companyStampUrl = $company->stamp_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($company->stamp_path) : null;
                                    @endphp
                                    @if($companyStampUrl)
                                    <img src="{{ $companyStampUrl }}?v={{ time() }}" alt="Kaşe" class="rounded-3xl border" style="max-width: 100%; height: auto; max-height: 200px; object-fit: contain; border-color: var(--bs-primary-200) !important;" id="stamp-preview">
                                    @else
                                    <div class="bg-primary-200 rounded-3xl d-flex align-items-center justify-content-center" style="width: 100%; min-height: 100px; padding: 20px;" id="stamp-placeholder">
                                        <span class="text-primary fw-semibold">Dosya yükleyiniz</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <input type="file" name="stamp" accept="image/*" class="form-control border-primary-200 focus:border-primary focus:ring-primary" id="stamp-input">
                                    <button type="button" class="btn btn-primary btn-sm" id="stamp-upload-btn" onclick="uploadStamp(event)">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">upload</span>
                                    </button>
                                </div>
                                <small class="text-secondary">JPG, PNG veya GIF (Max: 2MB)</small>
                            </div>

                            <!-- Is Active -->
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="is_active">
                                        Firma Aktif
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.companies.index') }}" class="btn btn-light">İptal</a>
                                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                                        Kaydet
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Addresses Tab -->
                <div class="tab-pane fade" id="addresses" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="h5 fw-bold text-dark mb-0">İletişim & Adresler</h4>
                        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="openAddressModal()">
                            <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                            Yeni Adres Ekle
                        </button>
                    </div>

                    @if($addresses->isEmpty())
                    <div class="text-center py-5">
                        <span class="material-symbols-outlined text-secondary mb-3" style="font-size: 48px;">location_off</span>
                        <p class="text-secondary mb-0">Henüz adres eklenmemiş.</p>
                    </div>
                    @else
                    <div class="row g-3">
                        @foreach($addresses as $address)
                        <div class="col-md-6">
                            <div class="border rounded-3xl p-3 {{ $address->is_default ? 'border-primary bg-primary-50' : '' }}" style="border-color: var(--bs-primary-200);">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-dark mb-0">{{ $address->title }}</h6>
                                    <div class="d-flex gap-2">
                                        @if($address->is_default)
                                        <span class="badge bg-primary text-white rounded-pill">Varsayılan</span>
                                        @endif
                                        <button type="button" class="btn btn-link text-primary p-1" onclick="openAddressModal({{ $address->id }})" title="Düzenle">
                                            <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                                        </button>
                                        <form action="{{ route('admin.companies.addresses.delete', [$company, $address->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-1" title="Sil">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p class="small text-secondary mb-1">{{ $address->address }}</p>
                                <p class="small text-secondary mb-0">{{ $address->district }}, {{ $address->city }}, {{ $address->country }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <h4 class="h5 fw-bold text-dark mb-4">Sistem Ayarları</h4>
                    
                    <form action="{{ route('admin.companies.settings.update', $company) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <!-- Work Start Time -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">İş Başlangıç Saati</label>
                                <input type="time" name="work_start_time" value="{{ old('work_start_time', $settings->get('work_start_time')?->setting_value ?? '09:00') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary">
                            </div>

                            <!-- Work End Time -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">İş Bitiş Saati</label>
                                <input type="time" name="work_end_time" value="{{ old('work_end_time', $settings->get('work_end_time')?->setting_value ?? '18:00') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary">
                            </div>

                            <!-- Overtime Enabled -->
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="overtime_enabled" value="1" id="overtime_enabled" {{ old('overtime_enabled', $settings->get('overtime_enabled')?->setting_value) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="overtime_enabled">
                                        Mesai İzni
                                    </label>
                                </div>
                            </div>

                            <!-- Negative Stock Allowed -->
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="negative_stock_allowed" value="1" id="negative_stock_allowed" {{ old('negative_stock_allowed', $settings->get('negative_stock_allowed')?->setting_value) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="negative_stock_allowed">
                                        Negatif Stok İzni
                                    </label>
                                </div>
                            </div>

                            <!-- AI Enabled -->
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ai_enabled" value="1" id="ai_enabled" {{ old('ai_enabled', $settings->get('ai_enabled')?->setting_value) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="ai_enabled">
                                        AI Özellikleri Aktif
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.companies.index') }}" class="btn btn-light">İptal</a>
                                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                                        Ayarları Kaydet
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Digital Services Tab -->
                <div class="tab-pane fade" id="digital-services" role="tabpanel" aria-labelledby="digital-services-tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="h5 fw-bold text-dark mb-0">Dijital Mali Hizmetlerim</h4>
                    </div>

                    @if($digitalServices->isEmpty())
                        <div class="text-center py-5">
                            <span class="material-symbols-outlined text-secondary mb-3" style="font-size: 48px;">info</span>
                            <p class="text-secondary mb-1">Bu firmaya ait tanımlı dijital mali hizmet bulunmuyor.</p>
                            <p class="text-secondary small mb-0">
                                e-Fatura, e-Arşiv, e-İrsaliye vb. hizmetleri entegrasyon üzerinden buraya tanımlayabilirsiniz.
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Hizmet</th>
                                        <th>Durum</th>
                                        <th>Aktif / Eklenme</th>
                                        <th>Etiketler</th>
                                        <th>Aktivasyon Kodu</th>
                                        <th>Kapatma Talebi</th>
                                        <th>Operasyon Özeti</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($digitalServices as $service)
                                        @php
                                            $maskedCode = $service->activation_code
                                                ? substr($service->activation_code, 0, 4).'••••••••'.substr($service->activation_code, -4)
                                                : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $service->getDisplayName() }}</div>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.companies.digital-services.toggle', [$company, $service]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $service->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                                        {{ $service->is_active ? 'Aktif' : 'Pasif' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="small text-secondary">
                                                @if($service->activated_at)
                                                    <div>Aktif: {{ $service->activated_at->format('d.m.Y H:i') }}</div>
                                                @endif
                                                @if($service->added_at)
                                                    <div>Eklenme: {{ $service->added_at->format('d.m.Y H:i') }}</div>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($service->gb_label)
                                                    <div class="text-secondary">GB: {{ $service->gb_label }}</div>
                                                @endif
                                                @if($service->pk_label)
                                                    <div class="text-secondary">PK: {{ $service->pk_label }}</div>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($service->activation_code)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="text-secondary">{{ $maskedCode }}</span>
                                                        <button type="button" class="btn btn-light btn-sm"
                                                            onclick="navigator.clipboard.writeText('{{ $service->activation_code }}')">
                                                            Kopyala
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-secondary">Tanımsız</span>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($service->close_request_status === 'none')
                                                    <span class="text-secondary">Oluşturulmadı</span>
                                                @elseif($service->close_request_status === 'requested')
                                                    <span class="text-warning fw-semibold">Talep Oluşturuldu</span>
                                                    @if($service->close_requested_at)
                                                        <div class="text-secondary">
                                                            {{ $service->close_requested_at->format('d.m.Y H:i') }}
                                                        </div>
                                                    @endif
                                                @elseif($service->close_request_status === 'completed')
                                                    <span class="text-success fw-semibold">Kapatıldı</span>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($service->stats_last_24h)
                                                    <div class="text-secondary">
                                                        Son 24 saatte {{ $service->stats_last_24h }} belge işlendi
                                                    </div>
                                                @endif
                                                @if($service->last_activity_at)
                                                    <div class="text-secondary">
                                                        Son aktivite: {{ $service->last_activity_at->diffForHumans() }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-light btn-sm">
                                                        Aktivasyon Aracını İndir
                                                    </button>
                                                    <form action="{{ route('admin.companies.digital-services.close-request', [$company, $service]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-outline-danger btn-sm"
                                                            @if($service->close_request_status !== 'none') disabled @endif>
                                                            Hizmeti Kapat
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="addressModalLabel">Yeni Adres Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addressForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="addressMethod" value="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Başlık <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="addressTitle" class="form-control border-primary-200 focus:border-primary focus:ring-primary" placeholder="Örn: Merkez, Şube" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Adres <span class="text-danger">*</span></label>
                        <textarea name="address" id="addressAddress" class="form-control border-primary-200 focus:border-primary focus:ring-primary" rows="3" required></textarea>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">İlçe <span class="text-danger">*</span></label>
                            <input type="text" name="district" id="addressDistrict" class="form-control border-primary-200 focus:border-primary focus:ring-primary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Şehir <span class="text-danger">*</span></label>
                            <input type="text" name="city" id="addressCity" class="form-control border-primary-200 focus:border-primary focus:ring-primary" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Ülke <span class="text-danger">*</span></label>
                        <input type="text" name="country" id="addressCountry" class="form-control border-primary-200 focus:border-primary focus:ring-primary" value="Türkiye" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="addressIsDefault">
                            <label class="form-check-label fw-semibold text-dark" for="addressIsDefault">
                                Varsayılan Adres
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary rounded-3xl" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary rounded-3xl d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let editingAddressId = null;
const addresses = @json($addresses->keyBy('id'));

// Ülke, İl, İlçe dropdown'ları için dinamik yükleme
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country_id');
    const citySelect = document.getElementById('city_id');
    const districtSelect = document.getElementById('district_id');
    const taxOfficeCitySelect = document.getElementById('tax_office_city_id');

    // Ülke değiştiğinde il listesini güncelle
    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/cities?country_id=${countryId}`)
                    .then(response => response.json())
                    .then(data => {
                        citySelect.innerHTML = '<option value="">Seçiniz</option>';
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name_tr;
                            citySelect.appendChild(option);
                        });
                        districtSelect.innerHTML = '<option value="">Seçiniz</option>';
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                citySelect.innerHTML = '<option value="">Seçiniz</option>';
                districtSelect.innerHTML = '<option value="">Seçiniz</option>';
            }
        });
    }

    // İl değiştiğinde ilçe listesini güncelle
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const cityId = this.value;
            if (cityId) {
                fetch(`/api/districts?city_id=${cityId}`)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '<option value="">Seçiniz</option>';
                        data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name_tr;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                districtSelect.innerHTML = '<option value="">Seçiniz</option>';
            }
        });
    }

    // Vergi dairesi ili için de ülke değiştiğinde güncelle
    if (countrySelect && taxOfficeCitySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/cities?country_id=${countryId}`)
                    .then(response => response.json())
                    .then(data => {
                        const currentValue = taxOfficeCitySelect.value;
                        taxOfficeCitySelect.innerHTML = '<option value="">Seçiniz</option>';
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name_tr;
                            if (city.id == currentValue) {
                                option.selected = true;
                            }
                            taxOfficeCitySelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    }
});

// Sidebar logo input event listener
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLogoInput = document.getElementById('sidebar-logo-input');
    if (sidebarLogoInput) {
        sidebarLogoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                uploadLogoFromSidebar(e);
            }
        });
    }
});

function uploadLogoFromSidebar(event) {
    const fileInput = document.getElementById('sidebar-logo-input');
    const file = fileInput.files[0];
    
    if (!file) {
        return;
    }
    
    // Dosya boyutu kontrolü (2MB = 2097152 bytes)
    if (file.size > 2097152) {
        alert('Logo dosyası 2MB\'dan büyük olamaz.');
        fileInput.value = '';
        return;
    }
    
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    
    const button = document.getElementById('sidebar-logo-upload-btn');
    const originalHtml = button ? button.innerHTML : '';
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    fetch('{{ route("admin.companies.settings.logo.update", $company) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        if (response.ok) {
            return response.json();
        }
        return response.text().then(text => {
            console.error('Error response:', text);
            throw new Error('Logo yükleme başarısız: ' + response.status);
        });
    })
    .then(data => {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
        
        if (data && data.success) {
            // Sidebar logo preview'ı güncelle
            const sidebarLogoPreview = document.getElementById('sidebar-logo-preview');
            const sidebarLogoPlaceholder = document.getElementById('sidebar-logo-placeholder');
            const sidebarLogoDeleteBtn = document.getElementById('sidebar-logo-delete-btn');
            
            if (data.logo_url) {
                if (sidebarLogoPreview) {
                    sidebarLogoPreview.src = data.logo_url + '?v=' + Date.now();
                    sidebarLogoPreview.style.display = 'block';
                } else {
                    // Eğer preview yoksa, yeni bir img elementi oluştur
                    const newImg = document.createElement('img');
                    newImg.id = 'sidebar-logo-preview';
                    newImg.src = data.logo_url + '?v=' + Date.now();
                    newImg.alt = '{{ $company->name }}';
                    newImg.className = 'rounded-3xl shadow-lg mx-auto mb-3';
                    newImg.style.cssText = 'max-width: 100%; height: auto; max-height: 150px; object-fit: contain;';
                    
                    if (sidebarLogoPlaceholder && sidebarLogoPlaceholder.parentNode) {
                        sidebarLogoPlaceholder.parentNode.insertBefore(newImg, sidebarLogoPlaceholder);
                        sidebarLogoPlaceholder.style.display = 'none';
                    }
                }
                
                if (sidebarLogoPlaceholder) {
                    sidebarLogoPlaceholder.style.display = 'none';
                }
                
                // Silme butonunu ekle (eğer yoksa)
                if (!sidebarLogoDeleteBtn) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'btn btn-danger btn-sm';
                    deleteBtn.id = 'sidebar-logo-delete-btn';
                    deleteBtn.onclick = function(e) { deleteLogo(e); };
                    deleteBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px;">delete</span><span class="d-none d-md-inline">Sil</span>';
                    
                    const uploadBtn = document.getElementById('sidebar-logo-upload-btn');
                    if (uploadBtn && uploadBtn.parentNode) {
                        uploadBtn.parentNode.appendChild(deleteBtn);
                    }
                }
            }
            
            // Ana formdaki logo preview'ı da güncelle
            const logoPreview = document.getElementById('logo-preview');
            const logoPlaceholder = document.getElementById('logo-placeholder');
            const logoDeleteBtn = document.getElementById('logo-delete-btn');
            
            if (data.logo_url) {
                if (logoPreview) {
                    logoPreview.src = data.logo_url + '?v=' + Date.now();
                    logoPreview.style.display = 'block';
                    if (logoPlaceholder) {
                        logoPlaceholder.style.display = 'none';
                    }
                } else {
                    const newImg = document.createElement('img');
                    newImg.id = 'logo-preview';
                    newImg.src = data.logo_url + '?v=' + Date.now();
                    newImg.alt = 'Logo';
                    newImg.className = 'rounded-3xl border';
                    newImg.style.cssText = 'max-width: 100%; height: auto; max-height: 200px; object-fit: contain; border-color: var(--bs-primary-200) !important;';
                    
                    if (logoPlaceholder && logoPlaceholder.parentNode) {
                        logoPlaceholder.parentNode.insertBefore(newImg, logoPlaceholder);
                        logoPlaceholder.style.display = 'none';
                    }
                }
                
                // Ana formdaki silme butonunu ekle (eğer yoksa)
                if (!logoDeleteBtn) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'btn btn-danger btn-sm';
                    deleteBtn.id = 'logo-delete-btn';
                    deleteBtn.onclick = function(e) { deleteLogo(e); };
                    deleteBtn.title = 'Logoyu Sil';
                    deleteBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px;">delete</span>';
                    
                    const uploadBtn = document.getElementById('logo-upload-btn');
                    if (uploadBtn && uploadBtn.parentNode) {
                        uploadBtn.parentNode.appendChild(deleteBtn);
                    }
                }
            }
            
            // Başarı mesajı göster
            alert('Logo başarıyla yüklendi!');
            
            // Sayfayı yenile (sidebar için)
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Logo yüklenirken bir hata oluştu.');
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }
        
        // Input'u temizle
        fileInput.value = '';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Logo yüklenirken bir hata oluştu: ' + error.message);
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
        fileInput.value = '';
    });
}

function openAddressModal(addressId = null) {
    editingAddressId = addressId;
    const form = document.getElementById('addressForm');
    const modalLabel = document.getElementById('addressModalLabel');
    
    if (addressId && addresses[addressId]) {
        // Düzenleme modu
        const address = addresses[addressId];
        modalLabel.textContent = 'Adres Düzenle';
        form.action = `{{ route('admin.companies.addresses.update', [$company, ':id']) }}`.replace(':id', addressId);
        document.getElementById('addressMethod').value = 'PUT';
        document.getElementById('addressTitle').value = address.title;
        document.getElementById('addressAddress').value = address.address;
        document.getElementById('addressDistrict').value = address.district;
        document.getElementById('addressCity').value = address.city;
        document.getElementById('addressCountry').value = address.country;
        document.getElementById('addressIsDefault').checked = address.is_default;
    } else {
        // Yeni ekleme modu
        modalLabel.textContent = 'Yeni Adres Ekle';
        form.action = `{{ route('admin.companies.addresses.store', $company) }}`;
        document.getElementById('addressMethod').value = 'POST';
        form.reset();
        document.getElementById('addressCountry').value = 'Türkiye';
    }
}

function uploadLogo(event) {
    if (event) {
        event.preventDefault();
    }
    
    const fileInput = document.getElementById('logo-input');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Lütfen bir logo dosyası seçin.');
        return;
    }
    
    // Dosya boyutu kontrolü (2MB = 2097152 bytes)
    if (file.size > 2097152) {
        alert('Logo dosyası 2MB\'dan büyük olamaz.');
        return;
    }
    
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    
    const button = document.getElementById('logo-upload-btn');
    const originalHtml = button ? button.innerHTML : '';
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    fetch('{{ route("admin.companies.settings.logo.update", $company) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        if (response.ok) {
            return response.json();
        }
        return response.text().then(text => {
            console.error('Error response:', text);
            throw new Error('Logo yükleme başarısız: ' + response.status);
        });
    })
    .then(data => {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
        
        if (data && data.success) {
            // Logo preview'ı güncelle
            const logoPreview = document.getElementById('logo-preview');
            const logoPlaceholder = document.getElementById('logo-placeholder');
            
            if (data.logo_url) {
                if (logoPreview) {
                    logoPreview.src = data.logo_url + '?v=' + Date.now();
                    logoPreview.style.display = 'block';
                    if (logoPlaceholder) {
                        logoPlaceholder.style.display = 'none';
                    }
                } else {
                    // Eğer preview yoksa, yeni bir img elementi oluştur
                    const newImg = document.createElement('img');
                    newImg.id = 'logo-preview';
                    newImg.src = data.logo_url + '?v=' + Date.now();
                    newImg.alt = 'Logo';
                    newImg.className = 'rounded-3xl border';
                    newImg.style.cssText = 'max-width: 100%; height: auto; max-height: 200px; object-fit: contain; border-color: var(--bs-primary-200) !important;';
                    
                    if (logoPlaceholder && logoPlaceholder.parentNode) {
                        logoPlaceholder.parentNode.insertBefore(newImg, logoPlaceholder);
                        logoPlaceholder.style.display = 'none';
                    }
                }
            }
            
            // Başarı mesajı göster
            alert('Logo başarıyla yüklendi!');
            
            // Sayfayı yenile (sidebar için)
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Logo yüklenirken bir hata oluştu.');
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Logo yüklenirken bir hata oluştu: ' + error.message);
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    });
}

function deleteLogo(event) {
    if (event) {
        event.preventDefault();
    }
    
    if (!confirm('Logoyu silmek istediğinize emin misiniz?')) {
        return;
    }
    
    const button = document.getElementById('logo-delete-btn');
    const originalHtml = button ? button.innerHTML : '';
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    fetch('{{ route("admin.companies.settings.logo.delete", $company) }}', {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        if (response.ok) {
            return response.json();
        }
        return response.text().then(text => {
            console.error('Error response:', text);
            throw new Error('Logo silme başarısız: ' + response.status);
        });
    })
    .then(data => {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
        
        if (data && data.success) {
            // Sidebar logo preview'ı kaldır
            const sidebarLogoPreview = document.getElementById('sidebar-logo-preview');
            const sidebarLogoPlaceholder = document.getElementById('sidebar-logo-placeholder');
            const sidebarLogoDeleteBtn = document.getElementById('sidebar-logo-delete-btn');
            
            if (sidebarLogoPreview) {
                sidebarLogoPreview.style.display = 'none';
            }
            if (sidebarLogoPlaceholder) {
                sidebarLogoPlaceholder.style.display = 'flex';
            }
            if (sidebarLogoDeleteBtn) {
                sidebarLogoDeleteBtn.remove();
            }
            
            // Ana formdaki logo preview'ı kaldır
            const logoPreview = document.getElementById('logo-preview');
            const logoPlaceholder = document.getElementById('logo-placeholder');
            const logoDeleteBtn = document.getElementById('logo-delete-btn');
            
            if (logoPreview) {
                logoPreview.style.display = 'none';
            }
            if (logoPlaceholder) {
                logoPlaceholder.style.display = 'flex';
            }
            if (logoDeleteBtn) {
                logoDeleteBtn.remove();
            }
            
            // Başarı mesajı göster
            alert('Logo başarıyla silindi!');
            
            // Sayfayı yenile (sidebar için)
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Logo silinirken bir hata oluştu.');
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Logo silinirken bir hata oluştu: ' + error.message);
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    });
}

function uploadStamp(event) {
    if (event) {
        event.preventDefault();
    }
    
    const fileInput = document.getElementById('stamp-input');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Lütfen bir kaşe dosyası seçin.');
        return;
    }
    
    // Dosya boyutu kontrolü (2MB = 2097152 bytes)
    if (file.size > 2097152) {
        alert('Kaşe dosyası 2MB\'dan büyük olamaz.');
        return;
    }
    
    const formData = new FormData();
    formData.append('stamp', file);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    
    const button = document.getElementById('stamp-upload-btn');
    const originalHtml = button ? button.innerHTML : '';
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    fetch('{{ route("admin.companies.settings.stamp.update", $company) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        if (response.ok) {
            return response.json();
        }
        return response.text().then(text => {
            console.error('Error response:', text);
            throw new Error('Kaşe yükleme başarısız: ' + response.status);
        });
    })
    .then(data => {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
        
        if (data && data.success) {
            // Stamp preview'ı güncelle
            const stampPreview = document.getElementById('stamp-preview');
            const stampPlaceholder = document.getElementById('stamp-placeholder');
            
            if (data.stamp_url) {
                if (stampPreview) {
                    stampPreview.src = data.stamp_url + '?v=' + Date.now();
                    stampPreview.style.display = 'block';
                    if (stampPlaceholder) {
                        stampPlaceholder.style.display = 'none';
                    }
                } else {
                    // Eğer preview yoksa, yeni bir img elementi oluştur
                    const newImg = document.createElement('img');
                    newImg.id = 'stamp-preview';
                    newImg.src = data.stamp_url + '?v=' + Date.now();
                    newImg.alt = 'Kaşe';
                    newImg.className = 'rounded-3xl border';
                    newImg.style.cssText = 'max-width: 100%; height: auto; max-height: 200px; object-fit: contain; border-color: var(--bs-primary-200) !important;';
                    
                    if (stampPlaceholder && stampPlaceholder.parentNode) {
                        stampPlaceholder.parentNode.insertBefore(newImg, stampPlaceholder);
                        stampPlaceholder.style.display = 'none';
                    }
                }
            }
            
            // Başarı mesajı göster
            alert('Kaşe başarıyla yüklendi!');
            
            // Sayfayı yenile
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Kaşe yüklenirken bir hata oluştu.');
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Kaşe yüklenirken bir hata oluştu: ' + error.message);
        if (button) {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    });
}
</script>
@endpush
@endsection
