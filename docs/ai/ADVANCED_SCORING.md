# Advanced AI Scoring – Fleet / Finance / Document

**Dosya konumu:** `docs/ai/ADVANCED_SCORING.md`

Bu doküman, AI servisleri (`AIFleetService`, `AIFinanceService`, `AIDocumentService`) için Faz 3'te uygulanacak gelişmiş skor ve anomaly kurallarının taslağını özetler.

İlgili mevcut dosyalar:

- `app/AI/Services/AIFleetService.php`
- `app/AI/Services/AIFinanceService.php`
- `app/AI/Services/AIDocumentService.php`
- AI job: `app/AI/Jobs/RunAIAnalysisJob.php`

Tüm AI raporları `ai_reports` tablosuna benzer bir yapı ile yazılır:

```php
[
    'type' => 'finance' | 'fleet' | 'document_compliance' | ...,
    'summary_text' => 'Kısa özet',
    'severity' => 'low' | 'medium' | 'high',
    'data_snapshot' => [...],
    'generated_at' => now(),
]
```

---

## 1. AIFleetService – Bakım & Kullanım Skoru

Mevcut davranış (özet):

- `predictMaintenanceNeeds(Vehicle $vehicle)`:
  - `maintenance_score` (0–100) — muayene süresi ve km'ye göre hesaplanıyor.
  - `status` — `excellent`, `good`, `fair`, `needs_attention`.
  - `upcoming_maintenance` — periyodik muayene, yağ/lastik değişimi vb.
- `analyze(int $companyId)`:
  - `maintenanceAnomalies` listesi oluşturur (bakım skoru düşük veya `needs_attention` olan araçlar).
  - `optimizeFleetDeployment` ile filo kullanım anomalilerini raporlar.

### Planlanan gelişmiş kurallar

1. **Bakım risk skoru (0–100):**
   - Temel bileşenler:
     - `inspection_age_score` — son muayeneden geçen gün sayısına göre (INSPECTION_INTERVAL_DAYS + WARNING/CRITICAL eşikleri).
     - `mileage_score` — `current_mileage` ve bakım periyotlarına göre.
   - Kombine skor:

   ```php
   $maintenanceRisk = 100 - $maintenanceScore; // mevcut maintenanceScore zaten 0–100
   ```

2. **Trend analizi (son 3–6 ay):**
   - Her araç için son N `VehicleInspection` ve varsa bakım kayıtları üzerinden:
     - “son 6 ayda kaç kez kritik eşik aşıldı?” gibi metrikler.
   - Raporda:
     - `trend` alanı: `improving` | `stable` | `worsening`.

3. **Anomaly scoring:**
   - Şirket bazında:

   ```php
   $fleetAvgScore = avg(maintenance_score of active vehicles);
   $vehicleDeviation = $vehicleScore - $fleetAvgScore;
   ```

   - Çok düşük skor ve negatif sapma durumunda:
     - `severity = 'high'`.
   - Hafif sapma:
     - `severity = 'medium'` veya `low`.

4. **Rapor örneği (data_snapshot):**

```php
[
    'vehicle_id' => 5,
    'plate' => '34 ABC 123',
    'maintenance_score' => 42.5,
    'maintenance_risk' => 57.5,
    'days_since_inspection' => 210,
    'fleet_avg_score' => 78.3,
    'deviation' => -35.8,
    'trend' => 'worsening',
]
```

---

## 2. AIFinanceService – Finansal Risk & Anomali Skoru

Mevcut davranış (özet):

- `analyze()`:
  - Geciken ödemeleri (`analyzeOverduePayments`) ve yaklaşan ödemeleri (`analyzeUpcomingPayments`) raporlar.
  - `detectOverdueAnomaly()` ile son 3 ay ortalaması ile karşılaştırıp anomaly raporu üretir.

### Planlanan gelişmiş kurallar

1. **Finansal risk skoru (0–100):**
   - Girdiler:
     - `overdue_total` — toplam geciken tutar.
     - `overdue_count` — geciken ödeme sayısı.
     - `avg_monthly_paid_3m` — son 3 ayda aylık ortalama ödeme.
   - Örnek formül:

   ```php
   $overdueRatio = $overdueTotal / max($avgMonthlyPaid3m, 1);
   $riskScore = min(100, $overdueRatio * 25); // oran 4x ise ~100
   ```

2. **Nakit akışı volatilite analizi:**
   - Son 6–12 ay için aylık ödeme/gelen para akışına bakarak:
     - Standart sapma / ortalama oranı (`coefficient_of_variation`) ile volatilite ölçümü.
   - `volatility` alanı:
     - `< 0.5` → `low`
     - `0.5–1.0` → `medium`
     - `> 1.0` → `high`

3. **Severity belirleme:**

```php
if ($riskScore >= 80 || $volatility === 'high') {
    $severity = 'high';
} elseif ($riskScore >= 50 || $volatility === 'medium') {
    $severity = 'medium';
} else {
    $severity = 'low';
}
```

4. **Rapor snapshot örneği:**

```php
[
    'overdue_total' => 120000.0,
    'overdue_count' => 12,
    'avg_monthly_paid_3m' => 60000.0,
    'overdue_ratio' => 2.0,
    'risk_score' => 80,
    'volatility' => 'medium',
]
```

---

## 3. AIDocumentService – Uygunluk & Risk Skoru

Mevcut davranış (özet):

- `analyze()`:
  - Son 30 günde süresi dolacak belgeleri (`expiringSoon`) tespit eder ve adet + kritik (≤7 gün) sayısına göre severity atar.
  - Eksik `file_path`/`category` bilgisi olan belgeleri raporlar.
- `validateCompliance($document)`:
  - Basit bir `is_valid`, `compliance_score`, `errors`, `warnings` döner.

### Planlanan gelişmiş kurallar

1. **Belge risk skoru (0–100):**
   - Girdiler:
     - `total_expiring_30d`
     - `critical_expiring_7d`
     - `incomplete_count`
   - Örneğin:

```php
$expiryRisk = min(100, $critical_expiring_7d * 10 + $total_expiring_30d * 2);
$dataQualityRisk = min(100, $incomplete_count * 5);
$documentRiskScore = min(100, ($expiryRisk * 0.7) + ($dataQualityRisk * 0.3));
```

2. **Compliance skor map'i:**
   - 0–49 → `severity='high'` (yüksek risk)
   - 50–79 → `severity='medium'`
   - 80–100 → `severity='low'`

3. **Rapor snapshot örneği:**

```php
[
    'total_expiring_30d' => 25,
    'critical_expiring_7d' => 6,
    'incomplete_count' => 8,
    'document_risk_score' => 78,
]
```

---

## 4. Ortak Risk/Severity Standardı

Tüm AI servisleri için önerilen ortak şema:

- Skor aralığı: **0–100** (0 en kötü, 100 en iyi).
- Severity haritalaması:

```php
if ($score >= 80) {
    $severity = 'low';
} elseif ($score >= 50) {
    $severity = 'medium';
} else {
    $severity = 'high';
}
```

Bu standardizasyon sayesinde:

- Dashboard ve bildirim sistemleri (örn. AI kritik uyarı banner'ı) tüm AI raporlarını **tek severity/score modeli** üzerinden işleyebilir.
- `ai_reports` tablosuna ileride `risk_score` alanı eklemek gerekirse tüm domain'lerde aynı yorumlama yapılabilir.

---

## 5. Test Planı (Özet)

Gelecekte bu kurallar implemente edilirken aşağıdaki testler önerilir:

- **Unit testler:**
  - Her servis için örnek veri setleri ile skor hesaplamalarının beklenen aralıklarda kalması (0–100).
  - Sınır durumları (hiç veri yok, çok küçük/büyük değerler) için severity'nin doğru seçilmesi.
- **Feature testler:**
  - `RunAIAnalysisJob` sonrası `ai_reports` tablosuna yazılan kayıtların type/severity/score alanlarının tutarlılığı.
  - High severity raporlar için bildirim/uyarı mekanizmalarının (dashboard banner, bildirim paneli) tetiklenmesi.

Bu doküman, Faz 3'te AI servislerinin büyütülmesi sırasında referans olarak kullanılmalıdır; gerçek implementasyon yapılırken formüller ayarlanabilir, ancak scoring aralığı ve severity mapping korunmalıdır.

