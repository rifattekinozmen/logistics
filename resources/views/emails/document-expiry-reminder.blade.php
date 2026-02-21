<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belge Süre Hatırlatması</title>
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
                @if($daysUntil <= 0) #C41E5A 0%, #a01948 100%
                @elseif($daysUntil <= 7) #F59E0B 0%, #D97706 100%
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
        .document-list {
            margin: 20px 0;
        }
        .document-item {
            background: #F0F4FA;
            border-left: 4px solid 
                @if($daysUntil <= 0) #C41E5A
                @elseif($daysUntil <= 7) #F59E0B
                @else #3D69CE
                @endif;
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .document-title {
            font-weight: 700;
            color: #0F1A2E;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .document-detail {
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
            margin-left: 8px;
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
            @if($daysUntil <= 0)
                🚨 ACİL: Bugün Süresi Dolan Belgeler
            @elseif($daysUntil <= 7)
                ⚠️ DİKKAT: {{ $daysUntil }} Gün Sonra Süresi Dolacak Belgeler
            @else
                📋 Hatırlatma: {{ $daysUntil }} Gün Sonra Süresi Dolacak Belgeler
            @endif
        </h1>
        <p>{{ now()->format('d.m.Y') }}</p>
    </div>
    
    <div class="content">
        @if($daysUntil <= 0)
            <div class="urgent-notice">
                <p>⚠️ Aşağıdaki belgelerin süresi BUGÜN dolmaktadır. Lütfen acil işlem yapınız!</p>
            </div>
        @endif
        
        <p>Merhaba,</p>
        <p>{{ $documents->count() }} adet belgenin süresi dolmak üzere:</p>
        
        <div class="document-list">
            @foreach($documents as $document)
                <div class="document-item">
                    <div class="document-title">
                        {{ $document->title }}
                        @if($daysUntil <= 0)
                            <span class="badge badge-critical">Bugün Bitiyor</span>
                        @elseif($daysUntil <= 7)
                            <span class="badge badge-warning">{{ $daysUntil }} Gün</span>
                        @else
                            <span class="badge badge-info">{{ $daysUntil }} Gün</span>
                        @endif
                    </div>
                    <div class="document-detail">
                        <strong>Belge Tipi:</strong> {{ $document->document_type }}
                    </div>
                    <div class="document-detail">
                        <strong>Süre Tarihi:</strong> {{ $document->expiry_date?->format('d.m.Y') }}
                    </div>
                    @if($document->related)
                        <div class="document-detail">
                            <strong>İlişkili:</strong> 
                            @if($document->related_type === 'App\Models\Vehicle')
                                Araç - {{ $document->related->plate }}
                            @elseif($document->related_type === 'App\Models\Employee')
                                Personel - {{ $document->related->name }}
                            @else
                                {{ class_basename($document->related_type) }}
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            <strong>Not:</strong> Belge süre tarihlerine göre otomatik hatırlatmalar 30, 15, 7 gün öncesinden ve süre günü gönderilir.
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
