@extends('layouts.app')

@section('title', 'Dashboard - Logistics')

@section('content')
<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Hoş Geldiniz, {{ Auth::user()->name }}!</h1>
            <p class="text-secondary mb-0">Logistics yönetim paneline hoş geldiniz. İşte bugünün özeti.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary d-flex align-items-center gap-2" style="border-color: rgba(142, 148, 242, 0.4); color: rgba(142, 148, 242, 0.8);">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                <span>Rapor İndir</span>
            </button>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row g-4 mb-4">
        <!-- Siparişler -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(142, 148, 242, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(142, 148, 242, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="bg-primary rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(142, 148, 242, 0.7), rgba(0, 209, 255, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">shopping_cart</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(76, 175, 80, 0.15); color: #66bb6a;">+{{ round(($stats['orders_pending'] / max($stats['orders_count'], 1)) * 100) }}%</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['orders_count'] ?? 0) }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Toplam Sipariş</p>
                <div class="d-flex gap-3">
                    <div class="grow">
                        <p class="small text-secondary mb-0">Bekleyen</p>
                        <p class="small fw-bold mb-0" style="color: #ffb74d;">{{ $stats['orders_pending'] ?? 0 }}</p>
                    </div>
                    <div class="grow">
                        <p class="small text-secondary mb-0">Teslim Edilen</p>
                        <p class="small fw-bold mb-0" style="color: #81c784;">{{ $stats['orders_delivered'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sevkiyatlar -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(0, 209, 255, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(0, 209, 255, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(79, 195, 247, 0.7), rgba(33, 150, 243, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">local_shipping</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(79, 195, 247, 0.15); color: #4fc3f7;">{{ $stats['shipments_active'] ?? 0 }} Aktif</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['shipments_count'] ?? 0) }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Toplam Sevkiyat</p>
                <div class="d-flex gap-3">
                    <div class="grow">
                        <p class="small text-secondary mb-0">Aktif</p>
                        <p class="small fw-bold mb-0" style="color: #4fc3f7;">{{ $stats['shipments_active'] ?? 0 }}</p>
                    </div>
                    <div class="grow">
                        <p class="small text-secondary mb-0">Tamamlanan</p>
                        <p class="small fw-bold mb-0" style="color: #81c784;">{{ ($stats['shipments_count'] ?? 0) - ($stats['shipments_active'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Araçlar -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(255, 193, 7, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(255, 193, 7, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(255, 183, 77, 0.7), rgba(255, 152, 0, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">directions_car</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(255, 183, 77, 0.15); color: #ffb74d;">{{ $stats['vehicles_active'] ?? 0 }} Müsait</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['vehicles_count'] ?? 0) }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Toplam Araç</p>
                <div class="d-flex gap-3">
                    <div class="grow">
                        <p class="small text-secondary mb-0">Müsait</p>
                        <p class="small fw-bold mb-0" style="color: #81c784;">{{ $stats['vehicles_active'] ?? 0 }}</p>
                    </div>
                    <div class="grow">
                        <p class="small text-secondary mb-0">Kullanımda</p>
                        <p class="small fw-bold mb-0" style="color: #ffb74d;">{{ ($stats['vehicles_count'] ?? 0) - ($stats['vehicles_active'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Müşteriler -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(156, 39, 176, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(156, 39, 176, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(186, 104, 200, 0.7), rgba(156, 39, 176, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">groups</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(186, 104, 200, 0.15); color: #ba68c8;">+15%</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['customers_count'] ?? 0) }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Aktif Müşteri</p>
                <div class="d-flex gap-3">
                    <div class="grow">
                        <p class="small text-secondary mb-0">Personel</p>
                        <p class="small fw-bold mb-0" style="color: #8e9af2;">{{ $stats['employees_count'] ?? 0 }}</p>
                    </div>
                    <div class="grow">
                        <p class="small text-secondary mb-0">Depo</p>
                        <p class="small fw-bold mb-0" style="color: #4fc3f7;">{{ $stats['warehouses_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Finans & Operasyon Özet Kartları -->
    <div class="row g-4 mb-4">
        <!-- Finans Özet -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(244, 67, 54, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(244, 67, 54, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(239, 83, 80, 0.7), rgba(244, 67, 54, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">warning</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(244, 67, 54, 0.15); color: #ef5350;">Geciken</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['overdue']['total_amount'] ?? 0, 2) }} ₺</h3>
                <p class="small fw-semibold text-secondary mb-2">Geciken Ödemeler</p>
                <p class="small text-secondary mb-0">{{ $financeData['overdue']['count'] ?? 0 }} adet ödeme</p>
            </div>
        </div>

        <!-- Bugün Vadesi Gelenler -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(255, 152, 0, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(255, 152, 0, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(255, 183, 77, 0.7), rgba(255, 152, 0, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">today</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(255, 152, 0, 0.15); color: #ff9800;">Bugün</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['due_today']['total_amount'] ?? 0, 2) }} ₺</h3>
                <p class="small fw-semibold text-secondary mb-2">Bugün Vadesi Gelen</p>
                <p class="small text-secondary mb-0">{{ $financeData['due_today']['count'] ?? 0 }} adet ödeme</p>
            </div>
        </div>

        <!-- 7 Gün İçinde -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(33, 150, 243, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(33, 150, 243, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(79, 195, 247, 0.7), rgba(33, 150, 243, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">schedule</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(33, 150, 243, 0.15); color: #2196f3;">7 Gün</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['due_in_7_days']['total_amount'] ?? 0, 2) }} ₺</h3>
                <p class="small fw-semibold text-secondary mb-2">7 Gün İçinde</p>
                <p class="small text-secondary mb-0">{{ $financeData['due_in_7_days']['count'] ?? 0 }} adet ödeme</p>
            </div>
        </div>

        <!-- Bu Ay Ödenenler -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(76, 175, 80, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(76, 175, 80, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(129, 199, 132, 0.7), rgba(76, 175, 80, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">check_circle</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(76, 175, 80, 0.15); color: #4caf50;">Bu Ay</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['paid_this_month']['total_amount'] ?? 0, 2) }} ₺</h3>
                <p class="small fw-semibold text-secondary mb-2">Bu Ay Ödenen</p>
                <p class="small text-secondary mb-0">{{ $financeData['paid_this_month']['count'] ?? 0 }} adet ödeme</p>
            </div>
        </div>
    </div>

    <!-- Operasyon Performans Kartları -->
    <div class="row g-4 mb-4">
        <!-- Teslimat Performans Puanı -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(142, 148, 242, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(142, 148, 242, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="bg-primary rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(142, 148, 242, 0.7), rgba(0, 209, 255, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">speed</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(142, 148, 242, 0.15); color: #8e9af2;">
                        @if(($operationsData['delivery_performance_score'] ?? 0) >= 90) Mükemmel
                        @elseif(($operationsData['delivery_performance_score'] ?? 0) >= 70) İyi
                        @else Orta
                        @endif
                    </span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($operationsData['delivery_performance_score'] ?? 0, 1) }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Teslimat Performans Puanı</p>
                <p class="small text-secondary mb-0">0-100 arası skor</p>
            </div>
        </div>

        <!-- Geciken Sipariş Oranı -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(255, 152, 0, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(255, 152, 0, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(255, 183, 77, 0.7), rgba(255, 152, 0, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">schedule</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(255, 152, 0, 0.15); color: #ff9800;">Gecikme</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($operationsData['delayed_order_rate']['rate'] ?? 0, 1) }}%</h3>
                <p class="small fw-semibold text-secondary mb-2">Geciken Sipariş Oranı</p>
                <p class="small text-secondary mb-0">{{ $operationsData['delayed_order_rate']['count'] ?? 0 }} / {{ $operationsData['delayed_order_rate']['total'] ?? 0 }} sipariş</p>
            </div>
        </div>

        <!-- Araç Doluluk Oranı -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(33, 150, 243, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(33, 150, 243, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(79, 195, 247, 0.7), rgba(33, 150, 243, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">directions_car</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(33, 150, 243, 0.15); color: #2196f3;">Kullanım</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($operationsData['vehicle_utilization']['utilization_rate'] ?? 0, 1) }}%</h3>
                <p class="small fw-semibold text-secondary mb-2">Araç Doluluk Oranı</p>
                <p class="small text-secondary mb-0">{{ $operationsData['vehicle_utilization']['active_vehicles'] ?? 0 }} / {{ $operationsData['vehicle_utilization']['total_vehicles'] ?? 0 }} araç</p>
            </div>
        </div>

        <!-- Ortalama Teslimat Süresi -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(156, 39, 176, 0.15);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, rgba(156, 39, 176, 0.3) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, rgba(186, 104, 200, 0.7), rgba(156, 39, 176, 0.7));">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">access_time</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(156, 39, 176, 0.15); color: #9c27b0;">Süre</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ $operationsData['average_delivery_time'] ? number_format($operationsData['average_delivery_time'], 1) : '-' }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Ortalama Teslimat Süresi</p>
                <p class="small text-secondary mb-0">{{ $operationsData['average_delivery_time'] ? 'Saat' : 'Veri yok' }}</p>
            </div>
        </div>
    </div>

    <!-- AI Özet & Grafik ve Aktiviteler -->
    <div class="row g-4">
        <!-- AI Özet Kutusu -->
        <div class="col-lg-4 order-lg-1 order-2">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h3 class="h5 fw-bold text-dark mb-1">AI Özet</h3>
                        <p class="small text-secondary mb-0">Bugün dikkat edilmesi gerekenler</p>
                    </div>
                    <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">smart_toy</span>
                </div>

                @if(isset($aiReports) && $aiReports->isNotEmpty())
                    <div class="d-flex flex-column gap-3">
                        @foreach($aiReports as $report)
                            @php
                                $severityColors = [
                                    'low' => ['bg' => 'rgba(129, 199, 132, 0.12)', 'border' => 'rgba(129, 199, 132, 0.4)', 'text' => '#388e3c'],
                                    'medium' => ['bg' => 'rgba(255, 202, 40, 0.12)', 'border' => 'rgba(255, 202, 40, 0.4)', 'text' => '#f9a825'],
                                    'high' => ['bg' => 'rgba(239, 83, 80, 0.12)', 'border' => 'rgba(239, 83, 80, 0.4)', 'text' => '#c62828'],
                                ];
                                $colors = $severityColors[$report->severity] ?? $severityColors['low'];
                            @endphp
                            <div class="p-3 rounded-2xl border" style="background: {{ $colors['bg'] }}; border-color: {{ $colors['border'] }};">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge rounded-pill px-2 py-1 text-uppercase small" style="background: rgba(0,0,0,0.02); color: {{ $colors['text'] }};">
                                        {{ $report->type }}
                                    </span>
                                    <small class="text-secondary">{{ $report->generated_at?->diffForHumans() }}</small>
                                </div>
                                <p class="small mb-0" style="color: {{ $colors['text'] }};">{{ $report->summary_text }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 2.5rem; opacity: 0.4;">check_circle</span>
                        <p class="small text-secondary mb-0">AI şu an için kritik bir uyarı tespit etmedi.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Son Aktiviteler -->
        <div class="col-lg-8 order-lg-2 order-1">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="h4 fw-bold text-dark mb-1">Son Aktiviteler</h3>
                        <p class="small text-secondary mb-0">Sistemdeki son işlemler</p>
                    </div>
                    <a href="#" class="btn btn-link fw-semibold p-0 text-decoration-none d-flex align-items-center gap-2" style="font-size: 0.875rem; color: rgba(142, 148, 242, 0.8);">
                        <span>Tümünü Gör</span>
                        <span class="material-symbols-outlined" style="font-size: 1rem;">arrow_forward</span>
                    </a>
                </div>
                <div class="d-flex flex-column gap-3">
                    @forelse($recentActivities as $activity)
                    @php
                        $colorMap = [
                            'success' => ['bg' => 'rgba(129, 199, 132, 0.7)', 'badge' => 'rgba(129, 199, 132, 0.15)', 'text' => 'rgba(76, 175, 80, 0.8)'],
                            'info' => ['bg' => 'rgba(79, 195, 247, 0.7)', 'badge' => 'rgba(79, 195, 247, 0.15)', 'text' => 'rgba(33, 150, 243, 0.8)'],
                            'danger' => ['bg' => 'rgba(239, 154, 154, 0.7)', 'badge' => 'rgba(239, 154, 154, 0.15)', 'text' => 'rgba(244, 67, 54, 0.8)'],
                            'primary' => ['bg' => 'rgba(142, 148, 242, 0.7)', 'badge' => 'rgba(142, 148, 242, 0.15)', 'text' => 'rgba(142, 148, 242, 0.8)'],
                        ];
                        $colors = $colorMap[$activity['color']] ?? $colorMap['primary'];
                    @endphp
                    <div class="d-flex align-items-center gap-3 p-3 rounded-2xl border transition-all hover:shadow-sm position-relative" style="border-color: rgba(0,0,0,0.05); background: rgba(0,0,0,0.01);">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, {{ $colors['bg'] }}, {{ str_replace('0.7', '0.5', $colors['bg']) }});">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem;">{{ $activity['icon'] }}</span>
                        </div>
                        <div class="grow min-w-0">
                            <p class="small fw-bold text-dark mb-1">{{ $activity['title'] }}</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-secondary" style="font-size: 0.875rem;">person</span>
                                <p class="small text-secondary mb-0">{{ $activity['description'] }}</p>
                                <span class="text-secondary">•</span>
                                <p class="small text-secondary mb-0">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                        <span class="badge rounded-pill px-2 py-1 fw-semibold shrink-0" style="background: {{ $colors['badge'] }}; color: {{ $colors['text'] }}; font-size: 0.75rem;">
                            {{ ucfirst($activity['color']) }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <span class="material-symbols-outlined text-secondary mb-3 d-block" style="font-size: 3rem; opacity: 0.3;">notifications_off</span>
                        <p class="text-secondary mb-0">Henüz aktivite bulunmuyor.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Hızlı Erişim -->
        <div class="col-lg-4 order-lg-3 order-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100">
                <div class="mb-4">
                    <h3 class="h4 fw-bold text-dark mb-1">Hızlı Erişim</h3>
                    <p class="small text-secondary mb-0">Sık kullanılan işlemler</p>
                </div>
                <div class="d-flex flex-column gap-3">
                    <a href="{{ route('admin.orders.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(142, 148, 242, 0.08), rgba(0, 209, 255, 0.08)); border-color: rgba(142, 148, 242, 0.15) !important;">
                        <div class="bg-primary rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(142, 148, 242, 0.7), rgba(0, 209, 255, 0.7));">
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                        </div>
                        <div class="grow min-w-0">
                            <p class="small fw-bold text-dark mb-0">Yeni Sipariş</p>
                            <p class="small text-secondary mb-0">Sipariş oluştur</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.shipments.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md" style="background: rgba(79, 195, 247, 0.08); border-color: rgba(79, 195, 247, 0.15) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(79, 195, 247, 0.7), rgba(33, 150, 243, 0.7));">
                            <span class="material-symbols-outlined">local_shipping</span>
                        </div>
                        <div class="grow min-w-0">
                            <p class="small fw-bold text-dark mb-0">Yeni Sevkiyat</p>
                            <p class="small text-secondary mb-0">Sevkiyat oluştur</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.vehicles.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md" style="background: rgba(255, 183, 77, 0.08); border-color: rgba(255, 183, 77, 0.15) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(255, 183, 77, 0.7), rgba(255, 152, 0, 0.7));">
                            <span class="material-symbols-outlined">directions_car</span>
                        </div>
                        <div class="grow min-w-0">
                            <p class="small fw-bold text-dark mb-0">Yeni Araç</p>
                            <p class="small text-secondary mb-0">Araç ekle</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.employees.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md" style="background: rgba(186, 104, 200, 0.08); border-color: rgba(186, 104, 200, 0.15) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(186, 104, 200, 0.7), rgba(156, 39, 176, 0.7));">
                            <span class="material-symbols-outlined">person_add</span>
                        </div>
                        <div class="grow min-w-0">
                            <p class="small fw-bold text-dark mb-0">Yeni Personel</p>
                            <p class="small text-secondary mb-0">Personel ekle</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md" style="background: rgba(0,0,0,0.01); border-color: rgba(0,0,0,0.05) !important;">
                        <div class="bg-secondary rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm shrink-0" style="width: 48px; height: 48px;">
                            <span class="material-symbols-outlined">list</span>
                        </div>
                        <div class="grow min-w-0">
                            <p class="small fw-bold text-dark mb-0">Sipariş Listesi</p>
                            <p class="small text-secondary mb-0">Tüm siparişler</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary shrink-0">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .transition-all {
        transition: all 0.3s ease;
    }
    
    .hover\:shadow-md:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    
    .hover\:shadow-sm:hover {
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endpush
@endsection
