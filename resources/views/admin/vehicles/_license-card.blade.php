@php
    $licenseStageId = 'vehicle-license-' . ($vehicle->id ?? 'preview');
@endphp

<div class="bg-white rounded-3xl border p-4 mb-4">
    <h3 class="h4 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined text-primary">directions_car</span>
        Ruhsat Kartı
    </h3>

    <div class="vehicle-license-root" id="{{ $licenseStageId }}">
        <div class="d-flex flex-column align-items-center gap-2">
            <div class="scene" id="{{ $licenseStageId }}-scene">
                <div class="card3d" id="{{ $licenseStageId }}-card">

                    {{-- ÖN YÜZ --}}
                    <div class="face">
                        <div class="doc">

                            <div class="doc-body">

                                {{-- ROW 0: (Y.1) Verildiği İl/İlçe --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(Y.1) Verildiği İl / İlçe</span>
                                        <span class="val sm" data-preview="branch">{{ $vehicle->branch?->name ?? '-' }}</span>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">&nbsp;</span>
                                        <span class="val sm">-</span>
                                    </div>
                                </div>

                                {{-- ROW 1: (A) Plaka | (B) İlk Tescil Tarihi --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(A) Plaka</span>
                                        <div class="plaka-outer">
                                            <div class="plaka-eu">
                                                <span class="tr">TR</span>
                                            </div>
                                            <span class="plaka-num" data-preview="plate">{{ $vehicle->plate }}</span>
                                        </div>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">(B) İlk Tescil Tarihi</span>
                                        <span class="val sm" style="margin-top:3px;" data-preview="first_registration_date">{{ $vehicle->first_registration_date?->format('d.m.Y') ?? '-' }}</span>
                                    </div>
                                </div>

                                {{-- ROW 2: (Y.2) Tescil Sıra No | (I) Tescil Tarihi --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(Y.2) Tescil Sıra No</span>
                                        <span class="val mono" data-preview="registration_sequence_no">{{ $vehicle->registration_sequence_no ?? '-' }}</span>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">(I) Tescil Tarihi</span>
                                        <span class="val red" style="font-size:12px;" data-preview="registration_date">{{ $vehicle->registration_date?->format('d.m.Y') ?? '-' }}</span>
                    </div>
                    </div>

                                {{-- ROW 3: (D.1) Markası | (D.2) Tipi --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(D.1) Markası</span>
                                        <span class="val" data-preview="brand">{{ $vehicle->brand ?? '-' }}</span>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">(D.2) Tipi</span>
                                        <span class="val mono" data-preview="series">{{ $vehicle->series ?? '-' }}</span>
                    </div>
                    </div>

                                {{-- ROW 4: (D.3) Ticari Adı | (D.4) Model Yılı | (J) Araç Sınıfı --}}
                                <div class="row">
                                    <div class="cell w18">
                                        <span class="lbl">(D.3) Ticari Adı</span>
                                        <span class="val" data-preview="model">{{ $vehicle->model ?? '-' }}</span>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">(D.4) Model Yılı</span>
                                        <span class="val lg" data-preview="year">{{ $vehicle->year ?? '-' }}</span>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">(J) Araç Sınıfı</span>
                                        <span class="val lg" data-preview="vehicle_type">{{ $typeLabels[$vehicle->vehicle_type] ?? $vehicle->vehicle_type ?? '-' }}</span>
                    </div>
                    </div>

                                {{-- ROW 5: (D.5) Cinsi | (R) Rengi --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(D.5) Cinsi</span>
                                        <span class="val xs" data-preview="cinsi">{{ $typeLabels[$vehicle->vehicle_type] ?? $vehicle->vehicle_type ?? '-' }}</span>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">(R) Rengi</span>
                                        <span class="val xs" data-preview="color">{{ $vehicle->color ?? '-' }}</span>
                    </div>
                    </div>

                                {{-- ROW 6: (P.5) Motor No — tam genişlik --}}
                                <div class="cell cell-tight">
                                    <span class="lbl">(P.5) Motor No</span>
                                    <span class="val mono" data-preview="engine_number">{{ $vehicle->engine_number ?? '-' }}</span>
                    </div>

                                {{-- ROW 7: (E) Şase No — tam genişlik --}}
                                <div class="cell cell-tight">
                                    <span class="lbl">(E) Şase No</span>
                                    <span class="val mono" data-preview="vin_number">{{ $vehicle->vin_number ?? '-' }}</span>
                    </div>

                                {{-- ROW 8: (G.1) Net Ağırlığı | (F.1) Azami Yüklü Ağırlığı --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(G.1) Net Ağırlığı</span>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <span class="val" data-preview="net_weight_kg">{{ $vehicle->net_weight_kg !== null ? number_format($vehicle->net_weight_kg, 0, ',', '.') : '-' }}</span>
                                            <span class="val xs" style="font-size:9px;font-weight:400;">kg.</span>
                                        </div>
                                    </div>
                                    <div class="cell cell-pad-left">
                                        <span class="lbl">(F.1) Azami Yüklü Ağırlığı</span>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <span class="val">
                                                <span data-preview="capacity_kg">{{ $vehicle->capacity_kg ? number_format($vehicle->capacity_kg, 0, ',', '.') : '-' }}</span>
                        </span>
                                            <span class="val xs" style="font-size:9px;font-weight:400;">kg.</span>
                                        </div>
                    </div>
                    </div>

                                {{-- ROW 9: (G) Katar Ağırlığı | (G.2) Römork Azami Yüklü Ağırlığı --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(G) Katar Ağırlığı</span>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <span class="val" data-preview="train_weight_kg">{{ $vehicle->train_weight_kg !== null ? number_format($vehicle->train_weight_kg, 0, ',', '.') : '-' }}</span>
                                            <span class="val xs" style="font-size:9px;font-weight:400;">kg.</span>
                                        </div>
                                    </div>
                                    <div class="cell cell-pad-left">
                                        <span class="lbl">(G.2) Römork Azami Yüklü</span>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <span class="val" data-preview="trailer_max_weight_kg">{{ $vehicle->trailer_max_weight_kg !== null ? number_format($vehicle->trailer_max_weight_kg, 0, ',', '.') : '-' }}</span>
                                            <span class="val xs" style="font-size:9px;font-weight:400;">kg.</span>
                                        </div>
                    </div>
                    </div>

                                {{-- ROW 10: (S.1) Koltuk Sayısı | (S.2) Ayakta Yolcu --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(S.1) Koltuk Sayısı (Sür.Dahil)</span>
                                        <span class="val" data-preview="seat_count">{{ $vehicle->seat_count !== null ? (string) $vehicle->seat_count : '-' }}</span>
                                    </div>
                                    <div class="cell">
                                        <span class="lbl">(S.2) Ayakta Yolcu Sayısı</span>
                                        <span class="val" data-preview="standing_passenger_count">{{ $vehicle->standing_passenger_count !== null ? (string) $vehicle->standing_passenger_count : '-' }}</span>
                    </div>
                    </div>

                                {{-- ROW 11: (P.1) Silindir Hacmi | (P.2) Motor Gücü --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(P.1) Silindir Hacmi</span>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <span class="val" data-preview="engine_displacement_cm3">{{ $vehicle->engine_displacement_cm3 !== null ? number_format($vehicle->engine_displacement_cm3) : '-' }}</span>
                                            <span class="val xs" style="font-size:9px;font-weight:400;">cm³</span>
                                        </div>
                    </div>
                                    <div class="cell cell-pad-left">
                                        <span class="lbl">(P.2) Motor Gücü</span>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <span class="val" data-preview="engine_power_kw">{{ $vehicle->engine_power_kw !== null ? number_format($vehicle->engine_power_kw, 2) : '-' }}</span>
                                            <span class="val xs" style="font-size:9px;font-weight:400;">kw</span>
                    </div>
                </div>
                </div>

                                {{-- ROW 12: (P.3) Yakıt Cinsi | (Q) Güç Ağırlık Oranı --}}
                                <div class="row">
                                    <div class="cell">
                                        <span class="lbl">(P.3) Yakıt Cinsi</span>
                                        <span class="val" data-preview="fuel_type">{{ $fuelLabels[$vehicle->fuel_type] ?? $vehicle->fuel_type ?? '-' }}</span>
                                    </div>
                                    <div class="cell cell-val-right">
                                        <span class="lbl">(Q) Güç Ağırlık Oranı (Motosiklet)</span>
                                        <span class="val xs soft">-</span>
                                    </div>
                                </div>

                                {{-- ROW 13: (Y.3) Kullanım Amacı | (K) Tip Onay No — grow --}}
                                <div class="row grow">
                                    <div class="cell grow">
                                        <span class="lbl">(Y.3) Kullanım Amacı</span>
                                        <span class="val xs" data-preview="usage_purpose">{{ $vehicle->usage_purpose ?? '-' }}</span>
                                    </div>
                                    <div class="cell grow">
                                        <span class="lbl">(K) Tip Onay No</span>
                                        <span class="val mono xs" data-preview="type_approval_no">{{ $vehicle->type_approval_no ?? '-' }}</span>
                                    </div>
                                </div>

                            </div>{{-- /doc-body --}}

                            {{-- Ön yüzde referansa göre footer yok --}}

                    </div>
                    </div>{{-- /face --}}


                    {{-- ARKA YÜZ --}}
                    <div class="face face-back">
                        <div class="doc">

                            <div class="doc-body">

                                {{-- ROW 0: (Y.4) T.C. Kimlik / Vergi No --}}
                                <div class="cell">
                                    <span class="lbl">(Y.4) T.C. Kimlik No / Vergi No</span>
                                    <span class="val navy" style="font-size:17px; letter-spacing:0.07em; margin-top:0;" data-preview="owner_id_tax_no">{{ $vehicle->owner_id_tax_no ?? '-' }}</span>
                    </div>

                                {{-- ROW 1: (C.I.1) Ünvan --}}
                                <div class="cell" style="min-height:26px;">
                                    <span class="lbl">(C.I.1) Soyadı / Ticari Ünvanı</span>
                                    <span class="val xs soft" data-preview="owner_surname_trade_name">{{ $vehicle->owner_surname_trade_name ?? '-' }}</span>
                    </div>

                                {{-- ROW 2: (C.I.2) Adı --}}
                                <div class="cell">
                                    <span class="lbl">(C.I.2) Adı</span>
                                    <span class="val navy" style="font-size:12px;" data-preview="owner_first_name">{{ $vehicle->owner_first_name ?? '-' }}</span>
                    </div>

                                {{-- ROW 3: (C.I.3) Adresi --}}
                                <div class="cell">
                                    <span class="lbl">(C.I.3) Adresi</span>
                                    <span class="val xs soft" style="margin-top:3px; line-height:1.65;" data-preview="owner_address">{{ $vehicle->owner_address ?? '-' }}</span>
                    </div>

                                {{-- ROW 4: (Z.1) Hak-Menfaat | (Z.3.1) Noter Satış Tarihi --}}
                                <div class="row" style="min-height:38px;">
                                    <div class="cell" style="flex:1.2;">
                                        <span class="lbl">(Z.1) Araç Üzerinde Hak ve Menfaati Bulunanlar</span>
                                        <span class="val xs soft" data-preview="rights_holders">{{ $vehicle->rights_holders ?? '-' }}</span>
                    </div>
                                    <div class="cell" style="flex:0.8;">
                                        <span class="lbl">(Z.3.1) Noter Satış Tarihi</span>
                                        <span class="val xs" data-preview="notary_sale_date">{{ $vehicle->notary_sale_date?->format('d.m.Y') ?? '-' }}</span>
                    </div>
                    </div>

                                {{-- Referanstaki gibi ekstra boş iki satır kaldırıldı --}}

                                {{-- ROW 7: (Z.2) Diğer Bilgiler | (Z.3.4) QR / Mühür — grow --}}
                                <div class="row grow">
                                    <div class="cell grow">
                                        <span class="lbl">(Z.2) Diğer Bilgiler</span>
                                        <span class="val xs soft" style="margin-top:4px; line-height:1.65;">
                                            <span data-preview="notes">{{ $vehicle->notes ?? '-' }}</span>
                        </span>
                    </div>
                                    <div class="cell grow noter-labels" style="align-items:center; justify-content:center; padding:6px;">
                                        <div class="lbl-line"><span class="lbl">(Z.3.1) Noter Satış Tarihi</span></div>
                                        <div class="lbl-line"><span class="lbl">(Z.3.2) Noter Satış No</span></div>
                                        <div class="lbl-line"><span class="lbl">(Z.3.3) Noterin Adı</span></div>
                                        <span class="lbl" style="text-align:center; margin-top:4px; display:block;">(Z.3.4) Noter Mühür-İmza</span>
                                        <div class="qr-wrap">
                                            <svg width="70" height="70" viewBox="0 0 23 23"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <rect width="23" height="23" fill="#f5f8ff" rx="1.5"/>
                                                <rect x="1.5" y="1.5" width="7" height="7" rx="0.8" fill="none"
                                                      stroke="#2c4a8a" stroke-width="0.9"/>
                                                <rect x="3" y="3" width="4" height="4" rx="0.4" fill="#2c4a8a"/>
                                                <rect x="14.5" y="1.5" width="7" height="7" rx="0.8" fill="none"
                                                      stroke="#2c4a8a" stroke-width="0.9"/>
                                                <rect x="16" y="3" width="4" height="4" rx="0.4" fill="#2c4a8a"/>
                                                <rect x="1.5" y="14.5" width="7" height="7" rx="0.8" fill="none"
                                                      stroke="#2c4a8a" stroke-width="0.9"/>
                                                <rect x="3" y="16" width="4" height="4" rx="0.4" fill="#2c4a8a"/>
                                                <rect x="9.5"  y="1.5" width="1.2" height="1"   fill="#2c4a8a"/>
                                                <rect x="11"   y="1.5" width="1.8" height="1"   fill="#2c4a8a"/>
                                                <rect x="13.2" y="1.5" width="1"   height="1"   fill="#2c4a8a"/>
                                                <rect x="9.5"  y="3"   width="2"   height="1"   fill="#2c4a8a"/>
                                                <rect x="12.2" y="3"   width="1"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="9.5"  y="5"   width="1"   height="2"   fill="#2c4a8a"/>
                                                <rect x="11"   y="5"   width="1.5" height="1"   fill="#2c4a8a"/>
                                                <rect x="13"   y="5"   width="1"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="10"   y="8"   width="1.5" height="1"   fill="#2c4a8a"/>
                                                <rect x="12.5" y="8"   width="2"   height="1"   fill="#2c4a8a"/>
                                                <rect x="1.5"  y="9.5" width="1.5" height="1"   fill="#2c4a8a"/>
                                                <rect x="4"    y="9.5" width="2"   height="1"   fill="#2c4a8a"/>
                                                <rect x="7"    y="9.5" width="1"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="9.5"  y="9.5" width="2"   height="2"   fill="#2c4a8a"/>
                                                <rect x="12.5" y="9.5" width="1"   height="1"   fill="#2c4a8a"/>
                                                <rect x="14.5" y="9.5" width="2"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="17.5" y="9.5" width="3"   height="1"   fill="#2c4a8a"/>
                                                <rect x="1.5"  y="11.5" width="2"  height="1"   fill="#2c4a8a"/>
                                                <rect x="5"    y="11.5" width="1"  height="1"   fill="#2c4a8a"/>
                                                <rect x="7"    y="11.5" width="1"  height="1"   fill="#2c4a8a"/>
                                                <rect x="12"   y="11.5" width="2"  height="2"   fill="#2c4a8a"/>
                                                <rect x="15.5" y="11.5" width="1"  height="1"   fill="#2c4a8a"/>
                                                <rect x="18.5" y="11.5" width="2"  height="2"   fill="#2c4a8a"/>
                                                <rect x="9.5"  y="13.5" width="1.5" height="1.5" fill="#2c4a8a"/>
                                                <rect x="12"   y="13.5" width="1"   height="1"   fill="#2c4a8a"/>
                                                <rect x="14.5" y="13.5" width="2"   height="1"   fill="#2c4a8a"/>
                                                <rect x="17.5" y="13.5" width="1.5" height="1"   fill="#2c4a8a"/>
                                                <rect x="9.5"  y="15.5" width="1"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="11.5" y="15.5" width="2.5" height="1"   fill="#2c4a8a"/>
                                                <rect x="15"   y="15.5" width="1"   height="2"   fill="#2c4a8a"/>
                                                <rect x="17"   y="15.5" width="1"   height="1"   fill="#2c4a8a"/>
                                                <rect x="19.5" y="15.5" width="1"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="9.5"  y="18"   width="2"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="13"   y="18"   width="1.5" height="1"   fill="#2c4a8a"/>
                                                <rect x="16"   y="18"   width="1"   height="1.5" fill="#2c4a8a"/>
                                                <rect x="18"   y="18"   width="2.5" height="1.5" fill="#2c4a8a"/>
                                            </svg>
                                        </div>
                    </div>
                </div>

                            </div>{{-- /doc-body --}}

                            <div class="row back-bottom-row">
                                <div class="cell" style="flex:1.2;">
                                    <span class="lbl">(Y.5) Onaylayan Sicil-İmza</span>
                                    <span class="val xs" style="margin-top:2px;">-</span>
                                </div>
                                <div class="cell" style="flex:0.8;">
                                    <span class="lbl">BELGE</span>
                                    <span class="val xs" data-preview="license_number">Seri {{ $vehicle->license_number ?? '________' }}</span>
                </div>
            </div>

                        </div>
                    </div>{{-- /face-back --}}

                </div>{{-- /card3d --}}
            </div>{{-- /scene --}}

            <div class="d-flex gap-2">
                <button type="button"
                        id="{{ $licenseStageId }}-flip-btn"
                        class="btn btn-sm btn-primary shadow-sm">
                    Ön/Arka
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const rootId = @json($licenseStageId);
            const card = document.getElementById(rootId + '-card');
            const scene = document.getElementById(rootId + '-scene');
            const flipBtn = document.getElementById(rootId + '-flip-btn');

            if (!card || !scene) {
                return;
            }

            let flipped = false;

            function setFlipped(value) {
                flipped = value;
                card.classList.toggle('flipped', flipped);
                if (!flipped) {
                    card.style.transform = '';
                }
            }

            scene.addEventListener('click', function () {
                setFlipped(!flipped);
            });

            if (flipBtn) {
                flipBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    setFlipped(!flipped);
                });
            }
        })();
    </script>
@endpush
