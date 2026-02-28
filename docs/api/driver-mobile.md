# Driver Mobile API Sözleşmesi

**Dosya konumu:** `docs/api/driver-mobile.md`

Bu doküman, Driver mobil uygulaması için v1 ve v2 REST API sözleşmesini özetler. Tüm endpoint'ler **Sanctum token** ile korunur (`auth:sanctum` middleware) ve URL prefix'i `/api/v1` veya `/api/v2`'dir.

---

## v1 – Temel Driver API

Route tanımı: `routes/api.php` içindeki `Route::prefix('v1')->middleware(['auth:sanctum'])->group(...)` altında:

### 1. `GET /api/v1/driver/shipments`

**Amaç:** Giriş yapmış şoföre atanmış sevkiyatları listelemek.

- **Query parametreleri:**
  - `status` (opsiyonel, string) — `pending`, `assigned`, `loaded`, `in_transit`, `delivered`, `cancelled`
- **Response 200:**
  - `success` (bool)
  - `data` (array of shipments)
    - `id` (int)
    - `order_number` (string|null)
    - `customer_name` (string|null)
    - `pickup_address` (string|null)
    - `delivery_address` (string|null)
    - `status` (string)
    - `vehicle_plate` (string|null)
    - `pickup_date` (ISO8601 string|null)
    - `delivery_date` (ISO8601 string|null)
    - `created_at` (ISO8601 string)
- **Response 404:** `success=false`, `message='Personel kaydı bulunamadı.'`

### 2. `PUT /api/v1/driver/shipments/{shipment}/status`

**Amaç:** Şoföre atanmış sevkiyatın durumunu güncellemek (workflow: assigned → loaded → in_transit → delivered).

- **Path parametreleri:**
  - `shipment` (int) — sevkiyat ID
- **Request body (JSON):**
  - `status` (zorunlu, string) — `assigned`, `loaded`, `in_transit`, `delivered`
  - `notes` (opsiyonel, string, max 1000)
- **Davranış:**
  - Shipment, sadece atanmış driver tarafından güncellenebilir; aksi durumda 403.
  - `status='loaded'` ve `pickup_date` boş ise, `pickup_date = now()`.
  - `status='delivered'` ise, `delivery_date = now()` ve ilgili `order` için `delivered_at` + `status='delivered'`.
- **Response 200:**
  - `success` (bool)
  - `message` (string)
  - `data`:
    - `id` (int)
    - `status` (string)
- **Response 403:** `success=false`, `message='Bu sevkiyata erişim yetkiniz yok.'`
- **Response 422:** doğrulama hataları.

### 3. `POST /api/v1/driver/shipments/{shipment}/pod`

**Amaç:** Teslimat kanıtı (POD) belgesini yüklemek.

- **Path parametreleri:**
  - `shipment` (int)
- **Request (multipart/form-data):**
  - `pod_file` (zorunlu, dosya) — `jpeg,png,jpg,pdf`, max 5MB
  - `notes` (opsiyonel, string, max 1000)
- **Davranış:**
  - Sadece sevkiyata atanmış driver yükleme yapabilir; aksi halde 403.
  - Dosya `storage/app/public/pods` altında saklanır.
  - İlgili `order` için `documents` ilişkisi üzerinden yeni `Document` kaydı oluşturulur (`category='pod'`).
  - İsteğe bağlı olarak sevkiyatın `notes` alanı güncellenir.
- **Response 200:**
  - `success` (bool)
  - `message` (string)
  - `data`:
    - `document_id` (int)
    - `file_url` (string — public URL)

### 4. `POST /api/v1/driver/location`

**Amaç:** Şoför konumunu bildirmek (v1’de sadece log olarak tutulur).

- **Request body (JSON):**
  - `latitude` (zorunlu, numeric, -90..90)
  - `longitude` (zorunlu, numeric, -180..180)
  - `shipment_id` (opsiyonel, int, mevcut bir shipment id'si)
- **Davranış:**
  - Şu an için konumlar `Log::info('Driver location update', [...])` ile loglanır.
  - Gelecekte `driver_locations` veya GPS tablosuna yazılmak üzere genişletilebilir (Faz 3 GPS planı ile uyumlu).
- **Response 200:**
  - `success` (bool)
  - `message` (string)

---

## v2 – Gelişmiş Driver API

Route tanımı: `Route::prefix('v2')->middleware(['auth:sanctum'])->group(...)` altında:

### 5. `GET /api/v2/driver/dashboard`

**Amaç:** Şoför için günlük dashboard verisini sağlamak.

- **Response 200:**
  - `success` (bool)
  - `data`:
    - `driver`:
      - `id` (int)
      - `name` (string)
      - `phone` (string|null)
    - `stats`:
      - `total_today` (int) — bugün için toplam sevkiyat sayısı
      - `pending` (int)
      - `in_transit` (int)
      - `completed_this_week` (int)
      - `total_completed` (int)
    - `today_shipments` — `ShipmentResource` koleksiyonu (sipariş, müşteri ve araç bilgileri dahil)
    - `last_location` (nullable object):
      - `latitude` (float|null)
      - `longitude` (float|null)
      - `updated_at` (ISO8601|null)
- **Response 404:** `success=false`, `message='Driver profile not found'`

### 6. `POST /api/v2/driver/checkin`

**Amaç:** Konuma bağlı check-in yapmak ve isteğe bağlı olarak shipment kaydını güncellemek.

- **Request body (JSON):**
  - `latitude` (zorunlu, numeric, -90..90)
  - `longitude` (zorunlu, numeric, -180..180)
  - `shipment_id` (opsiyonel, int, `shipments.id` için `exists` kuralı)
- **Davranış:**
  - Giriş yapmış kullanıcının `employee` kaydı alınır; yoksa 404 döner.
  - `last_known_location` alanı driver üzerinde güncellenir (JSON sütun veya cast).
  - `shipment_id` verilmişse ve shipment ilgili driver’a aitse, shipment’ın `current_location` alanı da güncellenir.
- **Response 200:**
  - `success` (bool)
  - `message` (string)
  - `data`:
    - `timestamp` (ISO8601)
    - `location`:
      - `latitude`, `longitude`, `updated_at`

---

## Authentication ve Ortak Kurallar

- Tüm endpoint'ler `auth:sanctum` ile korunur; mobil uygulama, login sonrası aldığı token ile istek yapar.
- Multi-tenant mimari (company_id) driver kullanıcılarına da uygulanır; driver'ın erişebileceği shipments ve orders, aktif şirket bağlamına göre filtrelenir (global scope/middleware ile).
- Response gövdeleri mümkün olduğunca sabit tutulmalıdır; yeni alan eklenirken geri uyum gözetilmelidir (eski client sürümleri hata almamalı).

---

## Gelecek Fazlar ile Uyum

- **Faz 2:** Bu doküman, mevcut v1/v2 endpoint'lerinin mobil uygulama için yeterli olduğunu gösterir; offline ve push notification için ek olaylar/endpoint'ler gerekecek, ancak bunlar ayrı dokümanda (`docs/api/driver-mobile-events.md`) tanımlanabilir.
- **Faz 3 GPS:** `POST /api/v1/driver/location` ve `POST /api/v2/driver/checkin` endpoint'leri, ileride VehicleGpsPosition tablosu ve real-time filo haritası ile entegre olacak şekilde tasarlanmıştır; payload formatı şimdiden bu hedefe göre sade tutulmuştur.

