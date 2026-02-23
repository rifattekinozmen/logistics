@props(['personnel', 'editable' => false])
@php
    $primaryPhone = $personnel?->mobil_telefon ?? $personnel?->telefon;
@endphp

<div class="border rounded-3 p-4 bg-white shadow-sm mb-4 overflow-visible personnel-header-card" style="border-color: var(--bs-primary-200) !important;">
    <div class="d-flex align-items-start gap-4 flex-wrap">
        <div class="position-relative personnel-avatar-wrap flex-shrink-0">
            @if($personnel?->photo_path)
                <img src="{{ Storage::url($personnel->photo_path) }}" alt="{{ $personnel?->ad_soyad }}" class="rounded-circle object-fit-cover personnel-avatar-preview" style="width: 80px; height: 80px;" id="personnel-header-avatar-img">
            @else
                <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center overflow-hidden personnel-avatar-placeholder" style="width: 80px; height: 80px;" id="personnel-header-avatar-placeholder">
                    <span class="material-symbols-outlined text-primary" style="font-size: 48px;">person</span>
                </div>
            @endif
        </div>
        <div class="flex-grow-1 min-w-0">
            <h3 class="h4 fw-bold text-dark text-uppercase mb-1">{{ $personnel?->ad_soyad ?? 'Yeni Personel' }}</h3>
            <span class="badge {{ ($personnel?->aktif ?? true) ? 'bg-success-200 text-success' : 'bg-secondary' }} px-3 py-2 rounded-pill fw-semibold">{{ ($personnel?->aktif ?? true) ? 'Aktif' : 'Pasif' }}</span>
            <div class="d-flex flex-column gap-2 mt-2">
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    @if($primaryPhone)
                        <a href="tel:{{ preg_replace('/\s/', '', $primaryPhone) }}" class="btn btn-sm border border-primary-200 bg-white text-primary rounded-pill px-3 py-1 personnel-contact-btn mb-0">
                            <span class="material-symbols-outlined">call</span> Ara
                        </a>
                        <a href="sms:{{ preg_replace('/\s/', '', $primaryPhone) }}" class="btn btn-sm border border-primary-200 bg-white text-primary rounded-pill px-3 py-1 personnel-contact-btn mb-0">
                            <span class="material-symbols-outlined">sms</span> SMS
                        </a>
                        <a href="https://wa.me/90{{ preg_replace('/[^0-9]/', '', ltrim($primaryPhone, '0')) }}" target="_blank" rel="noopener" class="btn btn-sm border border-primary-200 bg-white text-success rounded-pill px-3 py-1 personnel-contact-btn mb-0">
                            <span class="material-symbols-outlined">chat</span> WhatsApp
                        </a>
                    @endif
                    @if($personnel?->email)
                        <a href="mailto:{{ $personnel->email }}" class="btn btn-sm border border-primary-200 bg-white text-primary rounded-pill px-3 py-1 personnel-contact-btn mb-0">
                            <span class="material-symbols-outlined">mail</span> Mail
                        </a>
                    @endif
                </div>
                @if($editable)
                    <div class="d-flex align-items-center gap-2">
                        <label class="btn btn-sm border border-primary-200 bg-white text-primary rounded-pill px-3 py-1 personnel-contact-btn mb-0 cursor-pointer">
                            <span class="material-symbols-outlined">add_a_photo</span> Fotoğraf Ekle/Değiştir
                            <input type="file" name="photo" id="personnel-photo-input-header" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="d-none">
                        </label>
                        <small class="text-secondary">JPG, PNG, max 2MB</small>
                        @error('photo')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
