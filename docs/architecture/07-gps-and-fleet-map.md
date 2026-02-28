# Faz 3 – GPS & Filo Harita Mimarisi Taslağı

**Dosya konumu:** `docs/architecture/07-gps-and-fleet-map.md`

Bu doküman, Faz 3 kapsamında planlanan **real-time GPS takip** ve **filo harita** özelliğinin mevcut kodla (VehicleGpsPosition, VehicleGpsController, Driver API) nasıl entegre edileceğine dair mimari taslağı özetler.

İlgili mevcut dosyalar:

- Model: `app/Models/VehicleGpsPosition.php`
- API Controller: `app/Vehicle/Controllers/Api/VehicleGpsController.php`
- Driver API v1/v2:
  - `app/Driver/Controllers/Api/DriverController.php` (`updateLocation`)
  - `app/Driver/Controllers/Api/V2/DriverV2Controller.php` (`checkIn`)
- Routes: `routes/api.php` (`/api/v1/gps/*`, `/api/v1/vehicles/{vehicle}/gps/latest`)
- Analytics/Fleet ekranı: `resources/views/admin/analytics/fleet.blade.php`

---

## 1. Veri Modeli: VehicleGpsPosition

Mevcut model: `App\Models\VehicleGpsPosition`

```php
protected $table = 'vehicle_gps_positions';

protected $fillable = [
    'vehicle_id',
    'latitude',
    'longitude',
    'recorded_at',
    'source', // device, driver_app, manual
];
```

Önerilen migration alanları (MSSQL uyumlu):

- `id` (bigint, PK)
- `vehicle_id` (bigint, FK -> vehicles.id)
- `latitude` (decimal(10,8))
- `longitude` (decimal(11,8))
- `recorded_at` (datetime2)
- `source` (nvarchar(50))
- `created_at`, `updated_at`

Önerilen index'ler:

- `IX_vehicle_gps_positions_vehicle_recorded` (`vehicle_id`, `recorded_at` desc)
- `IX_vehicle_gps_positions_recorded` (`recorded_at` desc)

---

## 2. Driver → GPS Veri Akışı

### v1 – `POST /api/v1/driver/location`

- Şu an: sadece log yazıyor (driver id, shipment id, lat/lng).
- Faz 3'te:
  - Bu endpoint `VehicleGpsPosition` için insert atmayacak; sadece driver'ın son bilinen konumu için kullanılmaya devam edebilir (ör. `employees.last_known_location`).

### v2 – `POST /api/v2/driver/checkin`

- Şu an: `employees.last_known_location` ve opsiyonel olarak shipment `current_location` alanını JSON olarak güncelliyor.
- Faz 3'te:
  - Check-in anında aynı veriler (`latitude`, `longitude`, `updated_at`) ile **opsiyonel** olarak `VehicleGpsPosition` kaydı da oluşturulabilir:
    - Eğer driver bir araca atanmışsa (`vehicle_id` biliniyorsa), `vehicle_gps_positions` tablosuna bir satır eklenir.
  - Böylece hem driver profili hem de filo harita için tek kaynak oluşturulur.

---

## 3. Cihaz Tabanlı GPS → Fleet API

`VehicleGpsController` halihazırda placeholder olarak aşağıdaki endpoint'leri sağlar:

- `GET /api/v1/gps/positions` — Son konum kayıtları listesi (limit parametresi ile).
- `GET /api/v1/vehicles/{vehicle}/gps/latest` — Belirli bir aracın son konumu.
- `POST /api/v1/gps/positions` — Araç konumu kaydetme (device/driver_app/manual kaynaklı).

Planlanan kullanım:

- **Cihaz tabanlı entegrasyon:** Takip cihazı/IoT gateway, belirli aralıklarla `POST /api/v1/gps/positions` çağırır.
- **Driver app entegrasyonu:** Driver mobil uygulaması, opsiyonel olarak bu endpoint'i de kullanabilir (örneğin rota takibi için).

---

## 4. Filo Harita Ekranı

Hedef: Admin panelinde (örn. `admin.analytics.fleet`) tüm aktif araçları harita üzerinde göstermek.

### Backend

- `VehicleGpsController::index` ile:
  - Son X kaydı (varsayılan 50) çekilir, her kayıt için `vehicle_id`, `plate`, `latitude`, `longitude`, `recorded_at`, `source` alanları döner.
- Geliştirme:
  - Sadece **en son** kayıtları göstermek için `vehicle_id` bazında GROUP BY/ROW_NUMBER ile son satır seçen bir sorgu veya ayrı bir `vehicle_last_position` view'ı tasarlanabilir.

### Frontend

- `resources/views/admin/analytics/fleet.blade.php` içinde:
  - Leaflet/OpenStreetMap tabanlı basit bir harita komponenti (ör. JS ile `div#fleet-map` üzerine).
  - `/api/v1/gps/positions?limit=100` endpoint'inden AJAX ile veri çekilip marker olarak render edilir.
  - Marker popup'larında:
    - Araç plakası
    - Son güncelleme zamanı (`recorded_at`)
    - Kaynak (`source`)

### Real-time vs Polling

- İlk aşamada:
  - Her 30–60 saniyede bir AJAX poll ile konumlar yeniden çekilir.
- Daha sonra (ileride WebSocket eklenirse):
  - Laravel broadcast event'i (`VehicleLocationUpdated`) tetiklenir.
  - Harita, WebSocket üzerinden gelen event ile canlı güncellenir.

---

## 5. Güvenlik ve Rate Limiting

- Tüm GPS endpoint'leri `auth:sanctum` veya cihaz bazlı API key/middleware ile korunmalıdır.
- Rate limit:
  - Driver app için: örneğin `60 requests/minute` civarında, middleware ile sınırlandırılabilir.
  - Cihaz entegrasyonu için daha yüksek rate gerekirse, IP veya client bazında ayrı rate limit tanımı yapılabilir.
- Veri saklama:
  - `vehicle_gps_positions` tablosu hızla büyüyebilir; rolling window (örn. son 90 gün) veya arşivleme stratejisi planlanmalıdır.

---

## 6. Test Planı (Özet)

Unit/Feature test önerileri:

- `VehicleGpsControllerTest` (Feature):
  - `index` endpoint'i boş/çok veri ile 200 döner, `data` array yapısında.
  - `latest` endpoint'i kayıt yokken `data=null` ve açıklayıcı mesaj döner.
  - `store` endpoint'i geçerli body ile 201 ve DB'de satır oluşur.
- `DriverV2ControllerTest` (Feature):
  - `checkIn` çağrısı sonrasında driver `last_known_location` alanı güncellenir.
  - İleride `VehicleGpsPosition` ile entegrasyon yapılırsa, check-in sonrası ilgili araç için GPS kaydı oluştuğu test edilir.

Bu taslak, Faz 3 GPS ve filo harita geliştirmeleri için kod yazmaya başlamadan önce referans mimari olarak kullanılmalıdır.

