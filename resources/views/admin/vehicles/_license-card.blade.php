<div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
    <h3 class="h4 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined text-primary">directions_car</span>
        Ruhsat Kartı
    </h3>

    @php
        $licenseStageId = 'vehicle-license-stage-' . ($vehicle->id ?? 'preview');
    @endphp

    <div class="vehicle-license-stage" id="{{ $licenseStageId }}">
        <div class="d-flex justify-content-center">
            <div class="vehicle-license-pages">
                {{-- Sol sayfa (Ön yüz) --}}
                <div class="vehicle-license-front-page w-full h-full bg-white border-[6px] border-[#6b63a6] rounded-md p-1.5 text-[10px] text-slate-900 flex flex-col">
                <div class="grid grid-cols-2 border border-slate-900 flex-1">
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(Y.1) VERİLDİĞİ İL / İLÇE</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->branch?->name ?? '-' }}
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(B) İLK TESCİL TARİHİ</span>
                        <span class="mt-1 text-[11px]">
                            {{-- Henüz alan yok, ileride eklenecek --}}
                            -
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(A) PLAKA</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->plate }}
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(I) TESCİL TARİHİ</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(D.1) MARKASI</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->brand ?? '-' }}
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(D.2) TİPİ</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->series ?? '-' }}
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(D.3) TİCARİ ADI</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->model ?? '-' }}
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(D.4) MODEL YILI</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->year ?? '-' }}
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(D.5) CİNSİ</span>
                        <span class="mt-1 text-[11px]">
                            {{-- Araç türünü kısa açıklama olarak gösterelim --}}
                            {{ $typeLabels[$vehicle->vehicle_type] ?? $vehicle->vehicle_type ?? '-' }}
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(R) RENGİ</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->color ?? '-' }}
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(E) ŞASİ NO</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->vin_number ?? '-' }}
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(F.1) AZAMİ YÜKLÜ AĞIRLIK</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->capacity_kg ? number_format($vehicle->capacity_kg, 2).' kg' : '-' }}
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(G) KATAR AĞIRLIĞI</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(S.1) KOLTUK SAYISI</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(P.1) SİLİNDİR HACMİ</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(P.2) MOTOR GÜCÜ</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(P.3) YAKIT CİNSİ</span>
                        <span class="mt-1 text-[11px]">
                            {{ $fuelLabels[$vehicle->fuel_type] ?? $vehicle->fuel_type ?? '-' }}
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(K) TİP ONAY NO</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                </div>
                </div>

                {{-- Sağ sayfa (Arka yüz) --}}
                <div class="vehicle-license-back-page w-full h-full bg-white border-[6px] border-[#6b63a6] rounded-md p-1.5 text-[10px] text-slate-900 flex flex-col">
                <div class="grid grid-cols-1 border border-slate-900 flex-1">
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(V.9) T.C. KİMLİK NO / VERGİ NO</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(C.1.1) SOYADI / TİCARİ ÜNVANI</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(C.1.2) ADI</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(C.1.3) ADRESİ</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(Z.1) ARAÇ ÜZERİNDE HAK VE MENFAAT</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(Z.3.1) NOTER SATIŞ TARİHİ</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(Z.3.2) NOTER SATIŞ NO</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>
                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(Z.3.3) NOTERİN ADI</span>
                        <span class="mt-1 text-[11px]">
                            -
                        </span>
                    </div>

                    <div class="border border-slate-900 px-2 py-1 flex flex-col">
                        <span class="text-[10px] font-semibold">(Z.2) DİĞER BİLGİLER</span>
                        <span class="mt-1 text-[11px]">
                            {{ $vehicle->notes ?? '-' }}
                        </span>
                    </div>

                    <div class="qr-area border-t flex items-center justify-center h-16 text-[16px]">
                        ▣
                    </div>
                </div>

                <div class="text-right font-semibold text-[11px] pt-1">
                    BELGE SERİ NO: {{ $vehicle->license_number ?? '________' }}
                </div>
            </div>
            </div>
        </div>

        <button type="button"
                class="btn btn-sm btn-outline-primary mt-3 d-inline-flex align-items-center gap-1"
                onclick="document.getElementById('{{ $licenseStageId }}')?.classList.toggle('vehicle-license-back')">
            <span class="material-symbols-outlined" style="font-size: 1rem;">sync</span>
            Ön / Arka Göster
        </button>
    </div>
</div>

