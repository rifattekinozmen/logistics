# Faz 3 – Bildirim Kanalları (WhatsApp/SMS) ve Reporting API Taslağı

**Dosya konumu:** `docs/architecture/08-notifications-and-reporting.md`

Bu doküman, Faz 3 kapsamında planlanan **WhatsApp/SMS bildirim kanalları** ile **Reporting API** tasarımını özetler.

İlgili mevcut dosyalar:

- Bildirim komutları: `app/Notification/Console/Commands/*`
- Notification senaryoları: `docs/modules/04-modules-documentation.md` ve `docs/ROADMAP.md`
- Dashboard/Analytics: `app/Analytics/Services/AnalyticsDashboardService.php`, `resources/views/admin/analytics/*.blade.php`

---

## 1. Bildirim Kanalları – Soyut Arayüz

Amaç: E-posta dışında SMS ve WhatsApp kanallarını projeye eklerken, kodu belirli sağlayıcılara (Twilio, operatör API'leri vb.) sıkı sıkıya bağlamamak.

Önerilen arayüz:

```php
namespace App\Notification\Contracts;

interface NotificationChannel
{
    /**
     * @param  string  $to  Alıcı (telefon numarası, WhatsApp ID vb.)
     * @param  string  $template  Mesaj şablon kimliği (örn. 'payment_due', 'document_expiry')
     * @param  array<string, mixed>  $data  Şablonda kullanılacak değişkenler
     */
    public function send(string $to, string $template, array $data = []): void;
}
```

Konkret implementasyon örnekleri:

- `App\Notification\Channels\SmsChannel implements NotificationChannel`
- `App\Notification\Channels\WhatsappChannel implements NotificationChannel`

Konfigürasyon:

- SMS/WhatsApp sağlayıcı bilgileri için `config/notifications.php` veya ilgili `config/services.php` alanları.
- Örn:

```php
return [
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'),
        'from' => env('SMS_FROM'),
    ],
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'twilio'),
        'from' => env('WHATSAPP_FROM'),
    ],
];
```

---

## 2. Bildirim Senaryoları – Kanal Matrisi

Mevcut senaryolar (docs/modules/04-modules-documentation.md):

- Belge hatırlatma (document expiry)
- Ödeme hatırlatma (payment due)
- AI kritik uyarılar (high severity)
- Bakım hatırlatmaları (filo)

Önerilen kanal matrisi:

| Senaryo                    | Email | SMS | WhatsApp | Notlar                              |
|---------------------------|-------|-----|----------|-------------------------------------|
| Belge hatırlatmaları      | ✔     | ✔   | Opsiyonel| Kısa özet + link                    |
| Ödeme hatırlatmaları      | ✔     | ✔   | ✔        | Son gün/g gecikmiş ödemede ekstra  |
| AI kritik uyarılar        | ✔     | ✔   | Opsiyonel| Yüksek severity (high) durumlarında |
| Filo bakım hatırlatmaları | ✔     | ✖   | Opsiyonel| Sadece filo yöneticilerine          |

Kanal seçimi config üzerinden:

```php
// config/notifications.php
return [
    'channels' => [
        'document_expiry' => ['mail', 'sms'],
        'payment_due' => ['mail', 'sms', 'whatsapp'],
        'ai_critical' => ['mail', 'sms'],
    ],
];
```

Bildirim komutları (`CheckDocumentExpiryCommand`, `CheckPaymentDueCommand`) bu konfigurasyonu okuyarak ilgili kanalları tetikler.

---

## 3. Güvenlik ve Rate Limiting (Bildirimler)

- **SMS/WhatsApp spam** riskine karşı:
  - Aynı kullanıcıya aynı tip bildirim **belirli bir zaman aralığında** sadece bir kez gönderilmeli (ör. günde 1).
  - Bu amaçla `notification_logs` veya mevcut NotificationLog modeli kullanılabilir.
- **Outage durumları:**
  - Sağlayıcı hatasında, loglama ve fallback mekanizması (ör. SMS başarısızsa sadece email).
- **Konfigürasyon ile kapatma:**
  - Production öncesi ortamda SMS/WhatsApp kanalları config üzerinden global olarak devre dışı bırakılabilmeli.

---

## 4. Reporting API Taslağı

Amaç: BI araçlarının (Power BI vb.) sisteme doğrudan DB erişimi olmadan, JSON API üzerinden agregasyon verisi çekebilmesi.

Önerilen namespace:

- Controller: `App\Analytics\Controllers\Api\ReportingController`
- Route grubu: `routes/api.php` altında `/api/v1/reporting/*` prefix'i.

Örnek endpoint'ler:

1. `GET /api/v1/reporting/finance-summary`
   - Parametreler: `company_id`, `from`, `to`
   - Response:
     - `revenue`, `expenses`, `net_profit`, `profit_margin`
     - `monthly_trend` (aynı formatta)

2. `GET /api/v1/reporting/fleet-utilization`
   - Parametreler: `company_id`, `from`, `to`
   - Response:
     - `total_vehicles`, `active_vehicles`, `idle_vehicles`, `utilization_rate`
     - `vehicle_utilization` (araç bazlı)

3. `GET /api/v1/reporting/operations-kpi`
   - Parametreler: `company_id`, `from`, `to`
   - Response:
     - `total_orders`, `completed_orders`, `completion_rate`
     - `on_time_delivery_rate`, `status_breakdown`

Bu endpoint'ler, mevcut `AnalyticsDashboardService` metodlarını yeniden kullanarak (veya ince bir wrapper ile) veri üretir; UI'dan bağımsızdır.

---

## 5. Reporting API Güvenliği

- Kimlik doğrulama:
  - Sadece **internal/reporting client** için kullanılabilir; örneğin özel bir API token rolü (`reporting_client`) veya IP kısıtlaması (VPN).
- Rate limit:
  - Yüksek hacimli sorgular için sık istek yapılmasının önüne geçmek adına stricter rate limit (örn. dakikada 10 istek).
- Cache:
  - Aynı parametrelerle gelen istekler için kısa süreli cache (örn. 5–15 dakika) kullanılması önerilir (`cache()` veya response cache).

---

## 6. Test Planı (Özet)

Bildirim kanalları:

- Kanal implementasyonlarının (`SmsChannel`, `WhatsappChannel`) **gerçek sağlayıcıya hit etmeden** test edilebilmesi için HTTP client mock'ları veya provider adapter katmanı kullanılmalı.
- Notification komutları için:
  - Config'te hangi kanallar açık/kapalı ise, beklenen sayıda `NotificationChannel::send()` çağrısının yapılması assertion'larla test edilebilir (Pest + mock).

Reporting API:

- ReportingController testleri:
  - Auth/permission kontrolü (yetkisiz istekler 401/403).
  - Boş veri ve dolu veri senaryolarında 200 dönüp beklenen anahtarları içermesi.
  - Büyük veri setlerinde response süresinin kabul edilebilir düzeyde kalmasını sağlamak için gerekirse pagination veya tarih aralığı sınırlaması testleri.

Bu taslak, Faz 3 bildirim ve raporlama geliştirmeleri için rehber olarak kullanılmalıdır.

