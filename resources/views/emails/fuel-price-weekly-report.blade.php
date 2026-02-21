<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haftalık Motorin Fiyat Raporu</title>
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
            background: linear-gradient(135deg, #3D69CE 0%, #274A9B 100%);
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
            background: #F0F4FA;
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
        }
        .trend {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }
        .trend-up {
            background: #FCE8F0;
            color: #C41E5A;
        }
        .trend-down {
            background: #E0EDE8;
            color: #2D8B6F;
        }
        .trend-stable {
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
        .button {
            display: inline-block;
            background: #3D69CE;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Haftalık Motorin Fiyat Raporu</h1>
        <p>{{ $summary['start_date'] }} - {{ $summary['end_date'] }}</p>
    </div>
    
    <div class="content">
        <p>Merhaba,</p>
        <p>Bu hafta için motorin fiyat raporunuz hazır. Detaylı Excel raporu ekte bulunmaktadır.</p>
        
        <div class="summary-box">
            <h3 style="margin-top: 0; color: #3D69CE;">📈 Haftalık Özet</h3>
            
            <div class="summary-row">
                <span class="summary-label">Toplam Kayıt:</span>
                <span class="summary-value">{{ $summary['total_records'] }}</span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Ortalama Satın Alma Fiyatı:</span>
                <span class="summary-value">{{ number_format($summary['avg_purchase_price'], 2) }} ₺</span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Ortalama İstasyon Fiyatı:</span>
                <span class="summary-value">{{ number_format($summary['avg_station_price'], 2) }} ₺</span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Ortalama Fark:</span>
                <span class="summary-value">
                    {{ number_format($summary['avg_difference'], 2) }} ₺ 
                    ({{ number_format($summary['avg_difference_percent'], 1) }}%)
                </span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Fiyat Trendi:</span>
                <span class="summary-value">
                    @if($summary['trend'] === 'up')
                        <span class="trend trend-up">↑ Artış</span>
                    @elseif($summary['trend'] === 'down')
                        <span class="trend trend-down">↓ Azalış</span>
                    @else
                        <span class="trend trend-stable">→ Stabil</span>
                    @endif
                </span>
            </div>
        </div>
        
        <p>Detaylı analiz için ekteki Excel raporunu inceleyebilirsiniz.</p>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            <strong>Not:</strong> Bu rapor otomatik olarak her Pazar akşamı 20:00'de oluşturulur.
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
