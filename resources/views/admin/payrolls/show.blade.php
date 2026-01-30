@extends('layouts.app')

@section('title', 'Bordro Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Bordro Detayı</h2>
        <p class="text-secondary mb-0">Bordro No: <span class="fw-semibold">{{ $payroll->payroll_number }}</span></p>
    </div>
    <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">
        Listeye Dön
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h5 class="fw-bold mb-3">Bordro Bilgileri</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Bordro No</dt>
                <dd class="col-sm-8"><span class="fw-bold">{{ $payroll->payroll_number }}</span></dd>

                <dt class="col-sm-4">Personel</dt>
                <dd class="col-sm-8">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</dd>

                <dt class="col-sm-4">Dönem</dt>
                <dd class="col-sm-8">
                    {{ $payroll->period_start->format('d.m.Y') }} - {{ $payroll->period_end->format('d.m.Y') }}
                </dd>

                <dt class="col-sm-4">Brüt Maaş</dt>
                <dd class="col-sm-8">{{ number_format($payroll->base_salary, 2) }} ₺</dd>

                <dt class="col-sm-4">Fazla Mesai</dt>
                <dd class="col-sm-8">{{ number_format($payroll->overtime_amount, 2) }} ₺</dd>

                <dt class="col-sm-4">Prim</dt>
                <dd class="col-sm-8">{{ number_format($payroll->bonus, 2) }} ₺</dd>

                <dt class="col-sm-4">Kesinti</dt>
                <dd class="col-sm-8">{{ number_format($payroll->deduction, 2) }} ₺</dd>

                <dt class="col-sm-4">Vergi</dt>
                <dd class="col-sm-8">{{ number_format($payroll->tax, 2) }} ₺</dd>

                <dt class="col-sm-4">SGK</dt>
                <dd class="col-sm-8">{{ number_format($payroll->social_security, 2) }} ₺</dd>

                <dt class="col-sm-4">Net Maaş</dt>
                <dd class="col-sm-8">
                    <span class="fw-bold text-dark fs-5">{{ number_format($payroll->net_salary, 2) }} ₺</span>
                </dd>

                <dt class="col-sm-4">Durum</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ match($payroll->status) { 'paid' => 'success', 'finalized' => 'info', default => 'warning' } }}-200 text-{{ match($payroll->status) { 'paid' => 'success', 'finalized' => 'info', default => 'warning' } }} rounded-pill px-3 py-2">
                        {{ match($payroll->status) { 'paid' => 'Ödendi', 'finalized' => 'Kesinleşti', default => 'Taslak' } }}
                    </span>
                </dd>

                @if($payroll->notes)
                    <dt class="col-sm-4">Notlar</dt>
                    <dd class="col-sm-8">{{ $payroll->notes }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
