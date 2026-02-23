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
    $hasPhoto = (bool) ($personnel?->photo_path ?? false);
    $photoSrc = $hasPhoto ? Storage::url($personnel->photo_path) : '';
@endphp

<div class="tr-id-card" style="aspect-ratio: 1.586/1;">
    <div class="tr-id-card-dots" aria-hidden="true"></div>
    <div class="tr-id-card-inner">
        <div class="tr-id-card-header">
            <h4 class="tr-id-card-title">TÜRKİYE CUMHURİYETİ KİMLİK KARTI</h4>
            <p class="tr-id-card-subtitle">Republic of Turkey Identity Card</p>
        </div>

        <div class="tr-id-card-grid">
            {{-- Sol: Fotoğraf + TC Kimlik No --}}
            <div class="tr-id-col-photo">
                <div class="tr-id-photo-wrap">
                    <div class="tr-id-photo-placeholder {{ $hasPhoto ? 'd-none' : '' }}" id="preview-photo-placeholder">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <img src="{{ $photoSrc }}" alt="" class="tr-id-photo {{ $hasPhoto ? '' : 'd-none' }}" id="preview-photo-img">
                </div>
                <div class="tr-id-field-block">
                    <div class="tr-id-label">TC Kimlik No / TR Identity No</div>
                    @if($live)
                        <div class="tr-id-value font-monospace" id="preview-tckn">—</div>
                    @else
                        <div class="tr-id-value font-monospace">{{ $personnel?->tckn ?? '—' }}</div>
                    @endif
                </div>
            </div>

            {{-- Orta: Bilgiler + Bayrak --}}
            <div class="tr-id-col-fields">
                <div class="tr-id-fields-row tr-id-flag-pos">
                    <svg class="tr-id-flag-svg" viewBox="0 0 80 56" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="80" height="56" fill="#E30A17" rx="2"/>
                        <path fill="#fff" d="M30 28c0-8 6-14 5-14-1 0-4 5-4 14s3 14 4 14c1 0-5-6-5-14z"/>
                        <circle cx="44" cy="28" r="5" fill="#fff"/>
                        <circle cx="41" cy="28" r="4" fill="#E30A17"/>
                    </svg>
                </div>
                <div class="tr-id-field-block">
                    <div class="tr-id-label">Soyadı / Surname</div>
                    @if($live)
                        <div class="tr-id-value text-uppercase fst-italic" id="preview-soyadi">—</div>
                    @else
                        <div class="tr-id-value text-uppercase fst-italic">{{ $soyadi ?: '—' }}</div>
                    @endif
                </div>
                <div class="tr-id-field-block">
                    <div class="tr-id-label">Adı / Given Name(s)</div>
                    @if($live)
                        <div class="tr-id-value text-uppercase fst-italic" id="preview-adi">—</div>
                    @else
                        <div class="tr-id-value text-uppercase fst-italic">{{ $adi ?: '—' }}</div>
                    @endif
                </div>
                <div class="tr-id-fields-row tr-id-fields-2col">
                    <div class="tr-id-field-block">
                        <div class="tr-id-label">D.Tarihi / DOB</div>
                        @if($live)
                            <div class="tr-id-value" id="preview-dogum_tarihi">—</div>
                        @else
                            <div class="tr-id-value">{{ $formatDate($personnel?->dogum_tarihi) }}</div>
                        @endif
                    </div>
                    <div class="tr-id-field-block">
                        <div class="tr-id-label">Cinsiyet / Gender</div>
                        @if($live)
                            <div class="tr-id-value" id="preview-cinsiyet">—</div>
                        @else
                            <div class="tr-id-value">{{ $cinsiyetShort }}</div>
                        @endif
                    </div>
                </div>
                <div class="tr-id-field-block">
                    <div class="tr-id-label">Seri No / Document No</div>
                    @if($live)
                        <div class="tr-id-value" id="preview-kimlik_seri_no">—</div>
                    @else
                        <div class="tr-id-value">{{ $personnel?->kimlik_seri_no ?? '—' }}</div>
                    @endif
                </div>
                <div class="tr-id-fields-row tr-id-fields-2col">
                    <div class="tr-id-field-block">
                        <div class="tr-id-label">Son Geçerlilik / Valid Until</div>
                        @if($live)
                            <div class="tr-id-value" id="preview-son_gecerlilik">—</div>
                        @else
                            <div class="tr-id-value">{{ $formatDate($personnel?->son_gecerlilik_tarihi) }}</div>
                        @endif
                    </div>
                    <div class="tr-id-col-uyruk">
                        <div class="tr-id-photo-mini">
                            <span class="material-symbols-outlined tr-id-photo-mini-placeholder {{ $hasPhoto ? 'd-none' : '' }}" id="preview-photo-mini-placeholder">person</span>
                            <img src="{{ $photoSrc }}" alt="" class="tr-id-photo-mini-img {{ $hasPhoto ? '' : 'd-none' }}" id="preview-photo-mini-img">
                        </div>
                        <div class="tr-id-field-block">
                            <div class="tr-id-label">Uyruğu / Nationality</div>
                            @if($live)
                                <div class="tr-id-value" id="preview-uyruk">{{ $uyruk }}</div>
                            @else
                                <div class="tr-id-value">{{ $uyruk }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tr-id-field-block">
                    <div class="tr-id-label">İmza / Signature</div>
                    <div class="tr-id-signature">
                        @if($live)
                            <span class="tr-id-value fst-italic" id="preview-imza">—</span>
                        @else
                            <span class="tr-id-value fst-italic">{{ $personnel?->ad_soyad ?? '—' }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
