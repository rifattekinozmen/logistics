@extends('layouts.app')

@section('title', 'Finansal Analiz - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Finansal Analiz</h2>
        <p class="text-secondary mb-0">Gelir, gider ve karlılık metrikleri</p>
    </div>
    <div class="d-flex gap-2">
        <select class="form-select" id="periodSelector" onchange="window.location.href='?period='+this.value">
            <option value="7" {{ $period == '7' ? 'selected' : '' }}>Son 7 Gün</option>
            <option value="30" {{ $period == '30' ? 'selected' : '' }}>Son 30 Gün</option>
            <option value="90" {{ $period == '90' ? 'selected' : '' }}>Son 90 Gün</option>
            <option value="365" {{ $period == '365' ? 'selected' : '' }}>Son 1 Yıl</option>
        </select>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Toplam Gelir</span>
                <span class="material-symbols-outlined text-success">trending_up</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ number_format($metrics['revenue'], 2) }} ₺</h3>
            <p class="text-secondary small mb-0">Son {{ $period }} gün</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Toplam Gider</span>
                <span class="material-symbols-outlined text-danger">trending_down</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ number_format($metrics['expenses'], 2) }} ₺</h3>
            <p class="text-secondary small mb-0">Son {{ $period }} gün</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Net Kar</span>
                <span class="material-symbols-outlined text-{{ $metrics['net_profit'] >= 0 ? 'success' : 'danger' }}">
                    {{ $metrics['net_profit'] >= 0 ? 'arrow_upward' : 'arrow_downward' }}
                </span>
            </div>
            <h3 class="h2 fw-bold text-{{ $metrics['net_profit'] >= 0 ? 'success' : 'danger' }} mb-1">
                {{ number_format($metrics['net_profit'], 2) }} ₺
            </h3>
            <p class="text-secondary small mb-0">Son {{ $period }} gün</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Kar Marjı</span>
                <span class="material-symbols-outlined text-info">percent</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ number_format($metrics['profit_margin'], 1) }}%</h3>
            <p class="text-secondary small mb-0">Net kar / Gelir</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Gelir Trendi</h3>
            <div style="height: 350px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Aylık Karşılaştırma</h3>
            <div class="mb-4" style="height: 220px;">
                <canvas id="monthlyComparisonChart"></canvas>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="small fw-semibold text-secondary">Ay</th>
                            <th class="small fw-semibold text-secondary text-end">Gelir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metrics['monthly_trend'] as $month)
                        <tr>
                            <td class="small">{{ $month['month'] }}</td>
                            <td class="small text-end fw-semibold">{{ number_format($month['total'], 0) }} ₺</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.chartData = {
    revenue: {
        labels: @json(array_column($metrics['monthly_trend'], 'month')),
        values: @json(array_column($metrics['monthly_trend'], 'total'))
    },
    monthlyComparison: {
        labels: @json(array_column($metrics['monthly_trend'], 'month')),
        values: @json(array_column($metrics['monthly_trend'], 'total'))
    }
};
</script>
@vite(['resources/js/analytics-charts.js'])
@endpush
@endsection
