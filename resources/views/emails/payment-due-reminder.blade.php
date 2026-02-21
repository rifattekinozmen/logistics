<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Vade Hatırlatması</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, 
                @if($isOverdue || $daysUntil <= 0) #C41E5A 0%, #a01948 100%
                @elseif($daysUntil <= 3) #F59E0B 0%, #D97706 100%
                @else #3D69CE 0%, #274A9B 100%
                @endif
            );
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .summary-box {
            background: #DCE8FC;
            border-left: 4px solid #3D69CE;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .summary-label {
            font-weight: 600;
            color: #555;
        }
        .summary-value {
            font-weight: 700;
            color: #3D69CE;
            font-size: 18px;
        }
        .payment-list {
            margin: 20px 0;
        }
        .payment-item {
            background: #F0F4FA;
            border-left: 4px solid 
                @if($isOverdue || $daysUntil <= 0) #C41E5A
                @elseif($daysUntil <= 3) #F59E0B
                @else #3D69CE
                @endif;
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .payment-description {
            font-weight: 700;
            color: #0F1A2E;
            font-size: 16px;
        }
        .payment-amount {
            font-weight: 700;
            font-size: 18px;
            color: @if($isOverdue || $daysUntil <= 0) #C41E5A @else #3D69CE @endif;
        }
        .payment-detail {
            font-size: 14px;
            color: #555;
            margin: 4px 0;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-overdue {
            background: #FCE8F0;
            color: #C41E5A;
        }
        .badge-critical {
            background: #FCE8F0;
            color: #C41E5A;
        }
        .badge-warning {
            background: #FFF4E5;
            color: #F59E0B;
        }
        .badge-info {
            background: #DCE8FC;
            color: #3D69CE;
        }
        .footer {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .urgent-notice {
            background: #FCE8F0;
            border: 2px solid #C41E5A;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }
        .urgent-notice p {
            margin: 0;
            color: #C41E5A;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            @if($isOverdue)
                🚨 ACİL: Gecikmiş Ödemeler
            @elseif($daysUntil <= 0)
                🚨 ACİL: Bugün Vadesi Gelen Ödemeler
            @elseif($daysUntil <= 3)
                ⚠️ DİKKAT: {{ $daysUntil }} Gün Sonra Vadesi Gelecek Ödemeler
            @else
                💳 Hatırlatma: {{ $daysUntil }} Gün Sonra Vadesi Gelecek Ödemeler
            @endif
        </h1>
        <p>{{ now()->format('d.m.Y') }}</p>
    </div>
    
    <div class="content">
        @if($isOverdue)
            <div class="urgent-notice">
                <p>⚠️ Aşağıdaki ödemelerin vadesi GEÇMİŞTİR. Lütfen acil işlem yapınız!</p>
            </div>
        @elseif($daysUntil <= 0)
            <div class="urgent-notice">
                <p>⚠️ Aşağıdaki ödemelerin vadesi BUGÜN dolmaktadır. Lütfen işlem yapınız!</p>
            </div>
        @endif
        
        <p>Merhaba,</p>
        <p>{{ $payments->count() }} adet ödemenin vadesi 
            @if($isOverdue) 
                geçmiş durumda:
            @elseif($daysUntil <= 0)
                bugün dolmaktadır:
            @else
                {{ $daysUntil }} gün içinde dolacak:
            @endif
        </p>
        
        <div class="summary-box">
            <div class="summary-row">
                <span class="summary-label">Toplam Ödeme Sayısı:</span>
                <span class="summary-value">{{ $payments->count() }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Toplam Tutar:</span>
                <span class="summary-value">{{ number_format($payments->sum('amount'), 2) }} ₺</span>
            </div>
        </div>
        
        <div class="payment-list">
            @foreach($payments as $payment)
                <div class="payment-item">
                    <div class="payment-header">
                        <div class="payment-description">{{ $payment->description }}</div>
                        <div class="payment-amount">{{ number_format($payment->amount, 2) }} ₺</div>
                    </div>
                    <div class="payment-detail">
                        <strong>Vade Tarihi:</strong> {{ $payment->due_date?->format('d.m.Y') }}
                        @if($isOverdue)
                            <span class="badge badge-overdue">{{ $payment->due_date?->diffInDays(now()) }} Gün Gecikmiş</span>
                        @elseif($daysUntil <= 0)
                            <span class="badge badge-critical">Bugün</span>
                        @elseif($daysUntil <= 3)
                            <span class="badge badge-warning">{{ $daysUntil }} Gün</span>
                        @else
                            <span class="badge badge-info">{{ $daysUntil }} Gün</span>
                        @endif
                    </div>
                    <div class="payment-detail">
                        <strong>Ödeme Tipi:</strong> 
                        @switch($payment->payment_type)
                            @case('invoice') Fatura @break
                            @case('salary') Maaş @break
                            @case('tax') Vergi @break
                            @case('insurance') Sigorta @break
                            @case('fuel') Yakıt @break
                            @case('maintenance') Bakım @break
                            @case('other') Diğer @break
                            @default {{ $payment->payment_type }}
                        @endswitch
                    </div>
                    @if($payment->supplier)
                        <div class="payment-detail">
                            <strong>Tedarikçi:</strong> {{ $payment->supplier->name }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            <strong>Not:</strong> Ödeme hatırlatmaları vade tarihinden 7, 3 gün önce ve vade günü otomatik olarak gönderilir.
        </p>
    </div>
    
    <div class="footer">
        <p>Bu e-posta <strong>Logistics Yönetim Sistemi</strong> tarafından otomatik olarak gönderilmiştir.</p>
        <p style="font-size: 12px; margin-top: 10px;">
            {{ now()->format('d.m.Y H:i') }}
        </p>
    </div>
</body>
</html>
