@props(['personnel' => null, 'live' => false])
@php
    $formatDate = fn ($d) => $d ? $d->format('d.m.Y') : '—';
    $adi = $personnel ? $personnel->adi : '';
    $soyadi = $personnel ? $personnel->soyadi : '';
    $cinsiyetShort = $personnel && $personnel->cinsiyet
        ? (\App\Enums\Cinsiyet::tryFrom($personnel->cinsiyet)?->idCardShort() ?? $personnel->cinsiyet)
        : '—';
    $uyruk = $personnel?->country
        ? ($personnel->country->name_tr . ' / ' . ($personnel->country->code ?? 'TUR'))
        : 'T.C. / TUR';
    $uyrukCode = $personnel?->country?->code ?? 'TUR';
    $hasPhoto = (bool) ($personnel?->photo_path ?? false);
    $photoSrc = $hasPhoto ? Storage::url($personnel->photo_path) : '';

    // MRZ (TD1) için ham değerler - her satır 30 karakter
    $docNo = strtoupper(preg_replace('/[^A-Z0-9]/', '', $personnel?->kimlik_seri_no ?? ''));
    $docNoPadded = str_pad(substr($docNo, 0, 9), 9, '<') . str_repeat('<', 15);
    $dob = $personnel?->dogum_tarihi ? $personnel->dogum_tarihi->format('ymd') : '000000';
    $sex = ($personnel?->cinsiyet ?? '') === 'Kadın' ? 'F' : 'M';
    $expiry = $personnel?->son_gecerlilik_tarihi ? $personnel->son_gecerlilik_tarihi->format('ymd') : '000000';
    $mrzSurname = strtoupper(preg_replace('/[^A-Za-z]/', '', \Illuminate\Support\Str::ascii($soyadi ?: 'XXX')));
    $mrzGiven = strtoupper(preg_replace('/[^A-Za-z]/', '', \Illuminate\Support\Str::ascii($adi ?: 'XXX')));
    $mrzLine1 = 'I<TUR' . substr($docNoPadded, 0, 26);
    $mrzLine2 = $dob . $sex . $expiry . $uyrukCode . str_repeat('<', 7) . '2';
    $mrzLine3 = str_pad(substr($mrzSurname . '<<' . $mrzGiven, 0, 30), 30, '<');
@endphp

<div class="tr-id-stage" id="tr-id-stage-{{ $live ? 'edit' : 'show' }}">
    <div class="tr-id-card-wrapper">
        {{-- ÖN YÜZ --}}
        <div class="tr-id-card tr-id-front">
            <div class="tr-id-card-dots" aria-hidden="true"></div>
            <div class="tr-id-header">
                <h4 class="tr-id-title">TÜRKİYE CUMHURİYETİ KİMLİK KARTI</h4>
                <span class="tr-id-subtitle">REPUBLIC OF TURKEY IDENTITY CARD</span>
            </div>

            <svg class="tr-id-flag" viewBox="0 0 300 200" aria-hidden="true">
                <rect width="300" height="200" fill="#f8f9fb"/>
                <!-- Hilal: dış r=100, iç r=80, merkezler arası 22, uçlar açık -->
                <circle cx="100" cy="100" r="100" fill="#E30A17"/>
                <circle cx="122" cy="100" r="80" fill="#f8f9fb"/>
                <!-- Yıldız: çap 50, merkez (259,100), bir köşe sağa bakar -->
                <polygon fill="#E30A17" points="284,100 266.7,105.6 266.7,123.8 256.1,109.1 238.8,114.7 249.5,100 238.8,85.3 256.1,90.9 266.7,76.2 266.7,94.4"/>
            </svg>

            <div class="tr-id-idno">T.C. Kimlik No / TR Identity No</div>
            <div class="tr-id-idno-num" id="preview-tckn">{{ $personnel?->tckn ?? '—' }}</div>

            <div class="tr-id-photo-main">
                <div class="tr-id-photo-placeholder {{ $hasPhoto ? 'd-none' : '' }}" id="preview-photo-placeholder">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <img src="{{ $photoSrc }}" alt="" class="tr-id-photo-img {{ $hasPhoto ? '' : 'd-none' }}" id="preview-photo-img">
                <div class="tr-id-hologram" aria-hidden="true"></div>
            </div>

            <div class="tr-id-info">
                <div class="tr-id-field tr-id-cell-a1">
                    <div class="tr-id-label">Soyadı / Surname</div>
                    <div class="tr-id-value text-uppercase fst-italic" id="preview-soyadi">{{ $soyadi ?: '—' }}</div>
                </div>
                <div class="tr-id-cell-b1"></div>

                <div class="tr-id-field tr-id-cell-a2">
                    <div class="tr-id-label">Adı / Given Name(s)</div>
                    <div class="tr-id-value text-uppercase fst-italic" id="preview-adi">{{ $adi ?: '—' }}</div>
                </div>
                <div class="tr-id-cell-b2"></div>

                <div class="tr-id-field tr-id-cell-a3">
                    <div class="tr-id-label">Doğum Tarihi / Date of Birth</div>
                    <div class="tr-id-value" id="preview-dogum_tarihi">{{ $formatDate($personnel?->dogum_tarihi) }}</div>
                </div>
                <div class="tr-id-cell-b3">
                    <div class="tr-id-field tr-id-row-cinsiyet">
                        <div>
                            <div class="tr-id-label">Cinsiyet / Gender</div>
                            <div class="tr-id-value" id="preview-cinsiyet">{{ $cinsiyetShort }}</div>
                        </div>
                        <div class="tr-id-photo-mini-wrap">
                            <div class="tr-id-photo-mini-placeholder {{ $hasPhoto ? 'd-none' : '' }}" id="preview-photo-mini-placeholder">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <img src="{{ $photoSrc }}" alt="" class="tr-id-photo-mini-img {{ $hasPhoto ? '' : 'd-none' }}" id="preview-photo-mini-img">
                        </div>
                    </div>
                </div>

                <div class="tr-id-field tr-id-cell-a4">
                    <div class="tr-id-label">Seri No / Document No</div>
                    <div class="tr-id-value" id="preview-kimlik_seri_no">{{ $personnel?->kimlik_seri_no ?? '—' }}</div>
                </div>
                <div class="tr-id-cell-b4">
                    <div class="tr-id-field">
                        <div class="tr-id-label">Uyruğu / Nationality</div>
                        <div class="tr-id-value" id="preview-uyruk">{{ $uyruk }}</div>
                    </div>
                </div>

                <div class="tr-id-field tr-id-cell-a5">
                    <div class="tr-id-label">Son Geçerlilik / Valid Until</div>
                    <div class="tr-id-value" id="preview-son_gecerlilik">{{ $formatDate($personnel?->son_gecerlilik_tarihi) }}</div>
                </div>
                <div class="tr-id-cell-b5">
                    <div class="tr-id-signature">
                        <div class="tr-id-label">İmza / Signature</div>
                        <div class="tr-id-signature-line"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ARKA YÜZ --}}
        <div class="tr-id-card tr-id-back">
            <div class="tr-id-card-dots" aria-hidden="true"></div>
            <div class="tr-id-back-inner">
                <div class="tr-id-back-header">TÜRKİYE CUMHURİYETİ KİMLİK KARTI</div>
                <div class="tr-id-barcode"></div>
                <div class="tr-id-back-grid">
                    <div class="tr-id-chip"></div>
                    <div class="tr-id-back-fields">
                        <div class="tr-id-field">
                            <div class="tr-id-label">Anne Adı / Mother's Name</div>
                            <div class="tr-id-value" id="preview-anne_adi">{{ $personnel?->anne_adi ?? '—' }}</div>
                        </div>
                        <div class="tr-id-field">
                            <div class="tr-id-label">Baba Adı / Father's Name</div>
                            <div class="tr-id-value" id="preview-baba_adi">{{ $personnel?->baba_adi ?? '—' }}</div>
                        </div>
                        <div class="tr-id-field">
                            <div class="tr-id-label">Veren Makam / Issuing Authority</div>
                            <div class="tr-id-value tr-id-issuing">İÇİŞLERİ BAKANLIĞI</div>
                        </div>
                    </div>
                </div>
                <div class="tr-id-mrz">
{{ $mrzLine1 }}
{{ $mrzLine2 }}
{{ $mrzLine3 }}
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="tr-id-flip-btn" onclick="document.getElementById('tr-id-stage-{{ $live ? 'edit' : 'show' }}').classList.toggle('tr-id-flipped')">
        Ön / Arka Çevir
    </button>
</div>
