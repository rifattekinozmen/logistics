# PythonBridgeService API & Payload Tasarımı

**Dosya konumu:** `docs/api/python-bridge.md`

Bu doküman, Laravel tarafındaki `PythonBridgeService` ile Python ara katman arasındaki entegrasyon sözleşmesini ve ek payload tipleri için planlanan genişlemeleri özetler.

İlgili kod dosyaları:

- Servis: `app/Integration/Services/PythonBridgeService.php`
- Job: `app/Integration/Jobs/SendToPythonJob.php`
- Komutlar: `routes/console.php` (örn. `analytics:push-python`, `python:push-fuel-shipments`)
- Testler: `tests/Feature/PythonBridgeTest.php`

---

## 1. Temel HTTP Sözleşmesi

`PythonBridgeService::sendToPython(array $data, string $action = 'process'): array`

Laravel → Python istek formatı:

```json
{
  "action": "process",
  "data": { ... },
  "timestamp": "2026-02-28T10:30:00+03:00"
}
```

- Endpoint: `config('services.python.endpoint', 'http://localhost:8001/api/process')`
- Metod: `POST`
- Timeout: 30 saniye

Response beklentisi (örnek):

```json
{
  "success": true,
  "result": { "some_metric": 123 }
}
```

Laravel tarafında dönen yapı:

```php
[
    'success' => true,
    'response' => $response->json(),
]
```

Hata durumunda:

- HTTP 2xx dışı cevaplarda `Exception("Python bridge hatası: ...")` fırlatılır.
- Hata log'lanır: `Log::error('Python bridge hatası', ['action' => ..., 'data' => ..., 'exception' => ...])`.

---

## 2. Queue-First Kullanım

`PythonBridgeService::sendToPythonAsync(array $data, string $action = 'process'): void`

- `SendToPythonJob` kuyruk job'u dispatch edilir.
- `SendToPythonJob` içinde `sendToPython()` senkron çağrılır.
- Queue konfigürasyonu: `queue` ismi job içinde `onQueue('default')` ile atanır, `tries=2`.

Testler (`tests/Feature/PythonBridgeTest.php`):

- `sendToPythonAsync` çağrısında `SendToPythonJob` kuyruğa ekleniyor mu?
- `pushDeliveryDataToPipeline`, `pushOrderDataToPipeline`, `pushFuelAndShipmentsToPython` çağrılarında ilgili job kuyruğa ekleniyor mu?
- Artisan komutlar (`analytics:push-python`, `python:push-fuel-shipments`) en az bir `SendToPythonJob` dispatch ediyor mu?

---

## 3. Mevcut Payload Tipleri

### 3.1 Delivery Import Analytics

Metod: `pushDeliveryDataToPipeline(array $payload)`

Payload örneği:

```php
[
    'source' => 'delivery_import',
    'payload' => [
        'batch_id' => 123,
        'rows_count' => 1500,
        'summary' => [
            'total_weight' => 48000,
            'total_volume' => 320,
            // diğer özet alanlar...
        ],
    ],
]
```

Kullanım: Teslimat import pipeline sonrası Python tarafında analiz / anomaly detection için.

### 3.2 Order Optimization

Metod: `pushOrderDataToPipeline(array $ordersData)`

Payload örneği:

```php
[
    'source' => 'orders',
    'payload' => [
        'orders' => [
            ['id' => 1, 'status' => 'pending', 'weight' => 1200, 'volume' => 4.5],
            // ...
        ],
    ],
]
```

Kullanım: Rota/yük optimizasyonu veya sipariş clustering için temel veri seti.

### 3.3 Fuel & Shipments Snapshot

Metod: `buildFuelAndShipmentsPayload(int $days = 7)` + `pushFuelAndShipmentsToPython(int $days = 7)`

Payload yapısı:

```php
[
    'source' => 'fuel_shipments',
    'period_days' => 7,
    'period' => ['start' => '2026-02-21', 'end' => '2026-02-28'],
    'fuel' => [
        'avg_price' => 42.1234,
        'min_price' => 40.5000,
        'max_price' => 43.0000,
        'record_count' => 7,
    ],
    'shipments' => [
        'total' => 120,
        'by_status' => ['pending' => 10, 'in_transit' => 50, 'delivered' => 60],
    ],
]
```

Kullanım: Python tarafında yakıt maliyeti + sevkiyat hacmi ilişkisi için POC analizi.

---

## 4. Planlanan Ek Payload Tipleri (Faz 2)

Bu bölüm henüz implemente edilmemiş, ancak genişletme için agreed sözleşmeyi tanımlar.

### 4.1 Finance Risk Snapshot

Kaynak: Tahsilat ve gecikmiş ödemeler.

Önerilen payload:

```php
[
    'source' => 'finance_risk',
    'payload' => [
        'period' => ['start' => 'YYYY-MM-DD', 'end' => 'YYYY-MM-DD'],
        'overdue_payments' => [
            ['id' => 1, 'customer_id' => 10, 'days_overdue' => 15, 'amount' => 12000.50],
            // ...
        ],
        'collection_rate' => 87.5,
        'total_outstanding' => 250000.0,
    ],
]
```

### 4.2 Fleet Maintenance Snapshot

Kaynak: AIFleetService + VehicleInspection/Vehicle modelleri.

Önerilen payload:

```php
[
    'source' => 'fleet_maintenance',
    'payload' => [
        'company_id' => 1,
        'vehicles' => [
            [
                'id' => 5,
                'plate' => '34 ABC 123',
                'maintenance_score' => 42.5,
                'last_inspection_days' => 210,
                'status' => 'needs_attention',
            ],
            // ...
        ],
    ],
]
```

Bu iki yeni payload tipi için:

- Laravel tarafında builder metotları (`buildFinanceRiskPayload`, `buildFleetMaintenancePayload`) eklenmesi planlanır.
- `sendToPythonAsync` çağrısı, `action` parametresi ile Python tarafında ilgili pipeline'lara yönlendirme yapar (örn. `action='finance_risk'`, `action='fleet_maintenance'`).

---

## 5. Test Senaryoları (Plan)

`tests/Feature/PythonBridgeTest.php` genişletme önerileri:

1. **Yeni builder metotları için yapı testi:**
   - `buildFinanceRiskPayload` ve `buildFleetMaintenancePayload` için `->toHaveKeys([...])` kontrolü.
   - Örneğin overdue_payments dizisinde beklenen alanların varlığı (`id`, `customer_id`, `days_overdue`, `amount`).

2. **Async push metodları için Queue testi:**
   - `pushFinanceRiskToPython()`, `pushFleetMaintenanceToPython()` gibi wrapper metodlar eklenirse, her birinin `SendToPythonJob` dispatch ettiğini doğrulamak.

3. **Komut testleri:**
   - Gerekirse yeni artisan komutlar (örn. `python:push-finance-risk`) eklenirse, mevcut `python:push-fuel-shipments` testine benzer bir şekilde exit code ve job dispatch kontrolü.

Bu plan ile PythonBridge hattı, Faz 2'de ek veri setlerini destekleyecek net bir sözleşmeye sahip olur; gerçek implementasyon yapılırken bu dosya referans alınmalıdır.

