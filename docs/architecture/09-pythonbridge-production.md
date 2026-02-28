# Faz 3 – PythonBridgeService Production Hazırlık Taslağı

**Dosya konumu:** `docs/architecture/09-pythonbridge-production.md`

Bu doküman, PythonBridgeService hattını POC aşamasından production seviyesine taşımak için ihtiyaç duyulan **hata yönetimi**, **konfigürasyon** ve **izleme** kurallarını özetler.

İlgili dosyalar:

- Servis: `app/Integration/Services/PythonBridgeService.php`
- Job: `app/Integration/Jobs/SendToPythonJob.php`
- Testler: `tests/Feature/PythonBridgeTest.php`
- Queue konfigürasyonu: `config/queue.php`

---

## 1. Konfigürasyon Taslağı

Önerilen config dosyası: `config/python_bridge.php`

```php
return [
    'enabled' => env('PYTHON_BRIDGE_ENABLED', false),

    'endpoint' => env('PYTHON_BRIDGE_ENDPOINT', 'http://localhost:8001/api/process'),

    // HTTP timeout (saniye)
    'timeout' => env('PYTHON_BRIDGE_TIMEOUT', 30),

    // Maksimum retry sayısı (sync çağrılar için değil, job bazında)
    'max_retries' => env('PYTHON_BRIDGE_MAX_RETRIES', 3),

    // Ortam/tenant bazlı etki alanı
    'environment' => env('PYTHON_BRIDGE_ENV', 'dev'),
];
```

`.env` örneği:

```env
PYTHON_BRIDGE_ENABLED=true
PYTHON_BRIDGE_ENDPOINT=https://python-bridge.internal/api/process
PYTHON_BRIDGE_TIMEOUT=30
PYTHON_BRIDGE_MAX_RETRIES=3
PYTHON_BRIDGE_ENV=prod
```

`PythonBridgeService::sendToPython()` içerisinde `config('python_bridge.*')` anahtarları kullanılabilir.

---

## 2. Hata Yönetimi ve Retry Stratejisi

### 2.1 Sync Çağrılar

- `sendToPython()` metodu şu anda hatada exception fırlatıyor ve log’luyor.
- Production'da:
  - Network hataları veya 5xx cevaplar için:
    - Belirli hata kodları (örn. 500–502, 504) için *uygulama seviyesinde* küçük bir retry (örn. 1–2 deneme) düşünülebilir.
    - Diğer hatalarda (örn. 400–422) doğrudan hata döndürülmeli.

### 2.2 Job Bazlı Retry (Queue)

- `SendToPythonJob` şu an `tries=2` kullanıyor; production'da:
  - `tries` değeri `config('python_bridge.max_retries')` ile ilişkilendirilebilir.
  - `backoff` süresi (örn. 60–300 saniye) network hatalarında yeniden deneme yoğunluğunu kontrol etmek için kullanılmalı.
- Kritik durumlar:
  - Art arda belirli sayıda failure sonrası (örn. 5 farklı job arka arkaya hata) Python bridge **geçici olarak devre dışı** bırakılabilir (`enabled=false`).

---

## 3. Feature Flag / Fail-Safe Mekanizması

- `PYTHON_BRIDGE_ENABLED` bayrağı:
  - `false` olduğunda:
    - `sendToPythonAsync()` yine job dispatch edebilir ancak job içinde erken dönüş yapıp hiçbir HTTP isteği göndermemeli.
    - Alternatif olarak, job'lar hiç dispatch edilmez (kullanıma göre seçilir).
- Bu sayede:
  - Production ortamında Python tarafında bakım yapılırken Laravel tarafındaki kodlar stabilize kalır, hata üretmez.

---

## 4. Güvenlik & Veri Minimizasyonu

- Python'a gönderilen payload'lardaki hassas alanlar:
  - TCKN, vergi numarası, isim, telefon gibi PII bilgileri **gönderilmemeli** veya anonimleştirilmiş/hashed şekilde gönderilmeli.
- Öneri:
  - Payload'larda müşteri/şirket için **internal ID** veya hash kullanmak (ör. `customer_hash`).
  - Raporlama/analiz açısından gerekmedikçe detaylı satır/doküman içerikleri gönderilmemeli; agregasyonlar tercih edilmeli.

---

## 5. İzleme & Gözlemlenebilirlik

Önerilen metrikler ve loglar:

- Toplam istek sayısı (per action: `analytics`, `fuel_shipments`, `finance_risk` vb.).
- Başarılı istek sayısı ve oranı.
- Hatalı istek sayısı (HTTP 4xx, 5xx ayrımı).
- Ortalama, p95 ve p99 yanıt süreleri.

Uygulama tarafında:

- Laravel log'larında PythonBridge için ayrı bir context kullanılabilir:

```php
Log::channel('python_bridge')->info('Python request success', [...]);
Log::channel('python_bridge')->error('Python request failed', [...]);
```

- İleride bu channel, log shipping/observability aracı (ELK, Grafana, vb.) ile dashboard'lara taşınabilir.

---

## 6. Test Planı (Production Hazırlığı)

Ek test önerileri:

- **Config tabanlı davranış testleri:**
  - `PYTHON_BRIDGE_ENABLED=false` iken job'ların HTTP isteği göndermediğini ve hata üretmediğini doğrulayan testler.
- **Retry & backoff:**
  - Fake HTTP client ile arka arkaya hatalar simüle edilerek, job retry sayısı ve backoff davranışı test edilebilir.
- **Hata durumlarında graceful degradation:**
  - PythonBridge down iken, sistemin çekirdek iş akışlarını (order/shipment/invoice) etkilemeden çalışmaya devam ettiğini doğrulayan yüksek seviye feature testler.

Bu taslak, PythonBridge hattının production koşullarına hazırlanması sırasında referans olarak kullanılmalıdır.

