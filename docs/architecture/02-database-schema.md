# Logistics Project – Database Schema & Migrations

**Amaç:** MSSQL uyumlu, performanslı, modüler ve mobile-ready veritabanı yapısı  
**Not:** Tüm tablolar soft delete + audit uyumlu tasarlanır

---

## 1. CORE & AUTH TABLOLARI

### users
```sql
id (bigint, PK, identity)
name (nvarchar(255))
email (nvarchar(255), UNIQUE)
email_verified_at (datetime2, nullable)
password (nvarchar(255))
phone (nvarchar(20), nullable)
status (tinyint) -- 0: pasif, 1: aktif
last_login_at (datetime2, nullable)
remember_token (nvarchar(100), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `email` (UNIQUE)
- `deleted_at`

---

### roles
```sql
id (bigint, PK, identity)
name (nvarchar(100), UNIQUE) -- admin, operation, accounting, driver, customer
description (nvarchar(500), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### permissions
```sql
id (bigint, PK, identity)
code (nvarchar(100), UNIQUE) -- order.create, order.view, etc.
description (nvarchar(500), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### role_user (Pivot)
```sql
id (bigint, PK, identity)
user_id (bigint, FK -> users.id)
role_id (bigint, FK -> roles.id)
created_at (datetime2)
```

**Indexes:**
- `(user_id, role_id)` (UNIQUE)

---

### permission_role (Pivot)
```sql
id (bigint, PK, identity)
role_id (bigint, FK -> roles.id)
permission_id (bigint, FK -> permissions.id)
created_at (datetime2)
```

**Indexes:**
- `(role_id, permission_id)` (UNIQUE)

---

### audit_logs
```sql
id (bigint, PK, identity)
user_id (bigint, FK -> users.id, nullable)
action (nvarchar(50)) -- created, updated, deleted
table_name (nvarchar(100))
record_id (bigint)
old_data (nvarchar(max), nullable) -- JSON
new_data (nvarchar(max), nullable) -- JSON
ip_address (nvarchar(45), nullable)
user_agent (nvarchar(500), nullable)
created_at (datetime2)
```

**Indexes:**
- `user_id`
- `table_name, record_id`
- `created_at`

---

## 2. ŞİRKET & ORGANİZASYON

### companies
```sql
id (bigint, PK, identity)
name (nvarchar(255))
tax_number (nvarchar(50), nullable)
address (nvarchar(1000), nullable)
phone (nvarchar(20), nullable)
email (nvarchar(255), nullable)
status (tinyint) -- 0: pasif, 1: aktif
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### branches
```sql
id (bigint, PK, identity)
company_id (bigint, FK -> companies.id)
name (nvarchar(255))
address (nvarchar(1000), nullable)
phone (nvarchar(20), nullable)
manager_id (bigint, FK -> users.id, nullable)
status (tinyint)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `company_id`

---

### departments
```sql
id (bigint, PK, identity)
branch_id (bigint, FK -> branches.id)
name (nvarchar(255))
description (nvarchar(500), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `branch_id`

---

### positions
```sql
id (bigint, PK, identity)
department_id (bigint, FK -> departments.id)
name (nvarchar(255))
description (nvarchar(500), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `department_id`

---

## 3. PERSONEL & İK

### employees
```sql
id (bigint, PK, identity)
user_id (bigint, FK -> users.id, nullable)
branch_id (bigint, FK -> branches.id)
position_id (bigint, FK -> positions.id)
employee_number (nvarchar(50), UNIQUE)
first_name (nvarchar(100))
last_name (nvarchar(100))
phone (nvarchar(20), nullable)
email (nvarchar(255), nullable)
salary (decimal(10,2), nullable)
hire_date (date)
status (tinyint) -- 0: pasif, 1: aktif, 2: izinli
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `user_id`
- `branch_id`
- `position_id`
- `employee_number` (UNIQUE)

---

### personnel_attendance
```sql
id (bigint, PK, identity)
employee_id (bigint, FK -> employees.id)
attendance_date (date)
attendance_type (nvarchar(50)) -- full_day, half_day, leave, annual_leave, report, overtime
check_in (datetime2, nullable)
check_out (datetime2, nullable)
total_hours (decimal(5,2), nullable)
overtime_hours (decimal(5,2), default 0)
leave_type (nvarchar(50), nullable)
report_type (nvarchar(50), nullable)
report_document (nvarchar(1000), nullable)
notes (nvarchar(1000), nullable)
approved_by (bigint, FK -> users.id, nullable)
approved_at (datetime2, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `(employee_id, attendance_date)` (UNIQUE)
- `attendance_date`
- `attendance_type`

---

## 4. ARAÇ & FİLO

### vehicles
```sql
id (bigint, PK, identity)
plate (nvarchar(20), UNIQUE)
brand (nvarchar(100))
model (nvarchar(100))
year (int, nullable)
vehicle_type (nvarchar(50)) -- truck, van, car, etc.
capacity_kg (decimal(10,2), nullable)
capacity_m3 (decimal(10,2), nullable)
status (tinyint) -- 0: pasif, 1: aktif, 2: bakımda
branch_id (bigint, FK -> branches.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `plate` (UNIQUE)
- `branch_id`
- `status`

---

### vehicle_inspections
```sql
id (bigint, PK, identity)
vehicle_id (bigint, FK -> vehicles.id)
inspection_date (date)
inspector_name (nvarchar(255), nullable)
status (nvarchar(50)) -- pending, passed, failed, conditional
notes (nvarchar(2000), nullable)
created_by (bigint, FK -> users.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### vehicle_damages
```sql
id (bigint, PK, identity)
inspection_id (bigint, FK -> vehicle_inspections.id, nullable)
vehicle_id (bigint, FK -> vehicles.id)
damage_date (date)
damage_location (nvarchar(50)) -- front, rear, right, left, top, bottom
damage_type (nvarchar(50)) -- scratch, dent, crack, paint_damage
damage_size (nvarchar(50), nullable)
severity (nvarchar(50)) -- minor, moderate, severe
description (nvarchar(2000), nullable)
digital_drawing_data (nvarchar(max), nullable) -- JSON
status (nvarchar(50)) -- detected, approved, repaired, cancelled
photos (nvarchar(max), nullable) -- JSON array
created_by (bigint, FK -> users.id, nullable)
approved_by (bigint, FK -> users.id, nullable)
approved_at (datetime2, nullable)
repaired_at (datetime2, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### work_orders
```sql
id (bigint, PK, identity)
work_order_number (nvarchar(50), UNIQUE)
vehicle_id (bigint, FK -> vehicles.id)
work_order_type (nvarchar(50)) -- maintenance, repair, inspection, emergency
priority (nvarchar(50)) -- low, medium, high, urgent
description (nvarchar(2000), nullable)
estimated_duration (int, nullable)
estimated_cost (decimal(10,2), nullable)
actual_duration (int, nullable)
actual_cost (decimal(10,2), nullable)
status (nvarchar(50)) -- pending_approval, approved, in_progress, completed, cancelled
service_provider_id (bigint, FK -> service_providers.id, nullable)
assigned_technician_id (bigint, FK -> employees.id, nullable)
started_at (datetime2, nullable)
completed_at (datetime2, nullable)
approved_by (bigint, FK -> users.id, nullable)
approved_at (datetime2, nullable)
created_by (bigint, FK -> users.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

## 5. SİPARİŞ & LOJİSTİK

### customers
```sql
id (bigint, PK, identity)
name (nvarchar(255))
tax_number (nvarchar(50), nullable)
phone (nvarchar(20), nullable)
email (nvarchar(255), nullable)
address (nvarchar(1000), nullable)
status (tinyint) -- 0: pasif, 1: aktif
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `tax_number` (UNIQUE, nullable)

---

### orders
```sql
id (bigint, PK, identity)
customer_id (bigint, FK -> customers.id)
order_number (nvarchar(50), UNIQUE)
status (nvarchar(50)) -- pending, assigned, in_transit, delivered, cancelled
pickup_address (nvarchar(1000))
delivery_address (nvarchar(1000))
planned_pickup_date (datetime2, nullable)
planned_delivery_date (datetime2, nullable)
actual_pickup_date (datetime2, nullable)
delivered_at (datetime2, nullable)
total_weight (decimal(10,2), nullable)
total_volume (decimal(10,2), nullable)
is_dangerous (bit, default 0)
notes (nvarchar(2000), nullable)
created_by (bigint, FK -> users.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `customer_id`
- `order_number` (UNIQUE)
- `status`
- `planned_delivery_date`
- `delivered_at`

---

### shipments
```sql
id (bigint, PK, identity)
order_id (bigint, FK -> orders.id)
vehicle_id (bigint, FK -> vehicles.id, nullable)
driver_id (bigint, FK -> employees.id, nullable)
status (nvarchar(50)) -- assigned, loaded, in_transit, delivered
pickup_date (datetime2, nullable)
delivery_date (datetime2, nullable)
qr_code (nvarchar(100), nullable)
notes (nvarchar(1000), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `order_id`
- `vehicle_id`
- `driver_id`
- `status`

---

### delivery_numbers
```sql
id (bigint, PK, identity)
company_id (bigint, FK -> companies.id)
delivery_number (nvarchar(100), UNIQUE)
customer_name (nvarchar(255))
customer_phone (nvarchar(20), nullable)
delivery_address (nvarchar(1000))
location_id (bigint, FK -> locations.id, nullable)
order_id (bigint, FK -> orders.id, nullable)
status (nvarchar(50)) -- new, matched, order_created, shipment_assigned, completed, error
error_message (nvarchar(1000), nullable)
import_batch_id (bigint, FK -> delivery_import_batches.id, nullable)
row_number (int, nullable)
notes (nvarchar(2000), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

## LOGISTICS CORE TABLES (B2B FLOW)

Siparişten faturaya kadar uzanan logistics B2B lifecycle için kritik tablolar:

- **orders**
  - Müşteri talebini ve yük detaylarını tutar.
  - Ana ilişkiler: `orders.customer_id` → `customers.id`, `shipments.order_id` → `orders.id`.
- **shipments**
  - Bir veya daha fazla siparişe karşılık gelen gerçek sevkiyat kayıtları.
  - Araç, şoför ve teslim tarihleri burada tutulur.
- **delivery_numbers**
  - Dış sistemlerden gelen teslimat numaralarının normalleştirilmiş halidir; sipariş ve sevkiyat ile eşleştirme için kullanılır.
- **payments**
  - Ödeme takvimi ve tahsilat bilgilerini tutar (müşteri ve tedarikçi tarafı); due_date/status üzerinden dashboard ve takvimler beslenir.
- **documents**
  - Sevkiyat, fatura, personel ve araçlara bağlı tüm dokümanlar (POD, sözleşme, ruhsat, vb.) için merkezi storage meta tablosu.
- **ai_reports / activity_logs / audit_logs**
  - Analitik, izlenebilirlik ve denetim açısından logistics sürecini destekleyen yan tablolar.

Logistics B2B akışındaki temel ilişki zinciri:

`customers` → `orders` → (`shipments`, `delivery_numbers`) → finansal taraf için `payments` ve doküman tarafı için `documents`.

## 6. DEPO & STOK

### warehouses
```sql
id (bigint, PK, identity)
company_id (bigint, FK -> companies.id)
branch_id (bigint, FK -> branches.id, nullable)
code (nvarchar(50), UNIQUE)
name (nvarchar(255))
address (nvarchar(1000), nullable)
warehouse_type (nvarchar(50)) -- main, transit, temporary
status (tinyint) -- 0: pasif, 1: aktif
manager_id (bigint, FK -> users.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### warehouse_locations
```sql
id (bigint, PK, identity)
warehouse_id (bigint, FK -> warehouses.id)
parent_id (bigint, FK -> warehouse_locations.id, nullable)
location_type (nvarchar(50)) -- zone, aisle, rack, shelf, position
code (nvarchar(50))
name (nvarchar(255))
full_path (nvarchar(500)) -- A-01-B-02-C-03
capacity (decimal(10,2), nullable)
status (tinyint)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### inventory_items
```sql
id (bigint, PK, identity)
company_id (bigint, FK -> companies.id)
sku (nvarchar(100), UNIQUE)
barcode (nvarchar(100), nullable)
name (nvarchar(255))
category (nvarchar(100), nullable)
unit (nvarchar(50)) -- piece, kg, liter, m2, m3
min_stock_level (decimal(10,2), default 0)
max_stock_level (decimal(10,2), nullable)
critical_stock_level (decimal(10,2), nullable)
track_serial (bit, default 0)
track_lot (bit, default 0)
status (tinyint)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### inventory_stocks
```sql
id (bigint, PK, identity)
warehouse_id (bigint, FK -> warehouses.id)
location_id (bigint, FK -> warehouse_locations.id, nullable)
item_id (bigint, FK -> inventory_items.id)
quantity (decimal(10,2))
serial_number (nvarchar(100), nullable)
lot_number (nvarchar(100), nullable)
expiry_date (date, nullable)
created_at (datetime2)
updated_at (datetime2)
```

**Indexes:**
- `(warehouse_id, item_id, location_id)` (composite)
- `barcode` (inventory_items)
- `serial_number, lot_number` (inventory_stocks)

---

## 7. BELGE & DÖKÜMAN YÖNETİMİ

### documents
```sql
id (bigint, PK, identity)
documentable_id (bigint) -- polymorphic
documentable_type (nvarchar(100)) -- App\Models\Employee, App\Models\Vehicle, etc.
category (nvarchar(100)) -- identity, license, insurance, invoice, etc.
name (nvarchar(255))
file_path (nvarchar(1000))
file_size (bigint, nullable)
mime_type (nvarchar(100), nullable)
valid_from (date, nullable)
valid_until (date, nullable)
version (int, default 1)
tags (nvarchar(500), nullable) -- JSON array
uploaded_by (bigint, FK -> users.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `(documentable_id, documentable_type)` (composite)
- `category`
- `valid_until`
- `uploaded_by`

---

## 8. ÖDEME & FİNANS

### payments
```sql
id (bigint, PK, identity)
related_type (nvarchar(100)) -- App\Models\Employee, App\Models\Vehicle, etc.
related_id (bigint) -- polymorphic
payment_type (nvarchar(50)) -- salary, insurance, tax, supplier, customer, etc.
amount (decimal(10,2))
due_date (date)
paid_date (date, nullable)
status (tinyint) -- 0: bekliyor, 1: ödendi, 2: gecikti, 3: iptal
payment_method (nvarchar(50), nullable) -- cash, bank_transfer, etc.
reference_number (nvarchar(100), nullable)
notes (nvarchar(1000), nullable)
created_by (bigint, FK -> users.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

**Indexes:**
- `(related_id, related_type)` (composite)
- `due_date, status`
- `payment_type`

---

## 9. LOKASYON YÖNETİMİ

### countries
```sql
id (bigint, PK, identity)
code (nvarchar(10), UNIQUE) -- ISO country code
name_tr (nvarchar(255))
name_en (nvarchar(255))
phone_code (nvarchar(10), nullable)
currency_code (nvarchar(10), nullable)
is_active (bit, default 1)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### cities
```sql
id (bigint, PK, identity)
country_id (bigint, FK -> countries.id)
code (nvarchar(50), nullable)
name_tr (nvarchar(255))
name_en (nvarchar(255), nullable)
plate_code (nvarchar(10), nullable)
population (bigint, nullable)
is_active (bit, default 1)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### districts
```sql
id (bigint, PK, identity)
city_id (bigint, FK -> cities.id)
code (nvarchar(50), nullable)
name_tr (nvarchar(255))
name_en (nvarchar(255), nullable)
postal_code (nvarchar(20), nullable)
is_active (bit, default 1)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### neighborhoods
```sql
id (bigint, PK, identity)
district_id (bigint, FK -> districts.id)
code (nvarchar(50), nullable)
name_tr (nvarchar(255))
name_en (nvarchar(255), nullable)
postal_code (nvarchar(20), nullable)
is_active (bit, default 1)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

## 10. DİĞER MODÜL TABLOLARI

### fuel_prices
```sql
id (bigint, PK, identity)
company_id (bigint, FK -> companies.id)
price_date (date)
price_type (nvarchar(50)) -- purchase, station
price (decimal(10,4)) -- TL/Litre
supplier_name (nvarchar(255), nullable)
region (nvarchar(100), nullable)
notes (nvarchar(1000), nullable)
created_by (bigint, FK -> users.id, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### shift_templates
```sql
id (bigint, PK, identity)
company_id (bigint, FK -> companies.id)
name (nvarchar(255))
shift_type (nvarchar(50)) -- morning, afternoon, night, custom
start_time (time)
end_time (time)
break_duration (int, nullable)
total_hours (decimal(4,2))
department_id (bigint, FK -> departments.id, nullable)
branch_id (bigint, FK -> branches.id, nullable)
is_active (bit, default 1)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### shift_schedules
```sql
id (bigint, PK, identity)
company_id (bigint, FK -> companies.id)
week_start_date (date)
week_end_date (date)
template_id (bigint, FK -> shift_templates.id, nullable)
status (nvarchar(50)) -- draft, published, active
created_by (bigint, FK -> users.id, nullable)
published_at (datetime2, nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### shift_assignments
```sql
id (bigint, PK, identity)
schedule_id (bigint, FK -> shift_schedules.id)
employee_id (bigint, FK -> employees.id)
shift_date (date)
start_time (time)
end_time (time)
shift_type (nvarchar(50))
total_hours (decimal(4,2))
is_overtime (bit, default 0)
notes (nvarchar(1000), nullable)
created_at (datetime2)
updated_at (datetime2)
deleted_at (datetime2, nullable)
```

---

### notifications
```sql
id (bigint, PK, identity)
user_id (bigint, FK -> users.id, nullable)
notification_type (nvarchar(50)) -- document_expiry, maintenance, penalty, general
channel (nvarchar(50)) -- email, sms, whatsapp, dashboard
title (nvarchar(255))
content (nvarchar(max))
related_type (nvarchar(100), nullable)
related_id (bigint, nullable)
status (nvarchar(50)) -- pending, sent, failed
sent_at (datetime2, nullable)
is_read (bit, default 0)
read_at (datetime2, nullable)
metadata (nvarchar(max), nullable) -- JSON
created_at (datetime2)
updated_at (datetime2)
```

---

### ai_reports
```sql
id (bigint, PK, identity)
type (nvarchar(50)) -- finance, operations, hr, fleet, document
summary_text (nvarchar(max))
severity (nvarchar(20)) -- low, medium, high
data_snapshot (nvarchar(max), nullable) -- JSON
generated_at (datetime2)
created_at (datetime2)
updated_at (datetime2)
```

---

### activity_logs
```sql
id (bigint, PK, identity)
user_id (bigint, FK -> users.id, nullable)
action (nvarchar(50)) -- created, updated, deleted, viewed, login, logout
model_type (nvarchar(255), nullable) -- App\Models\Vehicle, etc.
model_id (bigint, nullable)
table_name (nvarchar(100), nullable)
record_id (bigint, nullable)
old_data (nvarchar(max), nullable) -- JSON
new_data (nvarchar(max), nullable) -- JSON
changed_fields (nvarchar(max), nullable) -- JSON array
ip_address (nvarchar(45), nullable)
user_agent (nvarchar(500), nullable)
description (nvarchar(1000), nullable)
created_at (datetime2)
updated_at (datetime2)
```

---

## PERFORMANS NOTLARI

### Önemli Indexler
- `users.email` (UNIQUE)
- `vehicles.plate` (UNIQUE)
- `orders.order_number` (UNIQUE)
- `documents (documentable_id, documentable_type)` (composite)
- `payments (due_date, status)` (composite)
- `personnel_attendance (employee_id, attendance_date)` (UNIQUE)
- `inventory_stocks (warehouse_id, item_id, location_id)` (composite)

#### Recommended indexes for logistics B2B flows

- `orders (customer_id, status, planned_delivery_date)` → müşteri bazlı sipariş listeleri ve SLA raporları.
- `shipments (status, delivery_date)` → aktif/yolda/teslim edilmiş sevkiyat listeleri ve dashboard widget'ları.
- `delivery_numbers (company_id, status)` → import edilen teslimatların eşleşme ve hata durumlarının hızlı raporlanması.
- `payments (due_date, status, payment_type)` → ödeme takvimi ve geciken ödemeler için takvim/rapor ekranları.
- `documents (category, valid_until)` → sürücü/araç/müşteri belgeleri için süre yaklaşıyor/geçti sorguları.

### Soft Delete
Tüm ana tablolarda `deleted_at` kolonu nullable datetime2 olarak eklenir.

### Audit Trail
Kritik tablolarda `created_by`, `updated_by` gibi kolonlar eklenebilir.

### MSSQL Özellikleri
- Identity kolonlar (auto-increment)
- datetime2 (daha geniş tarih aralığı)
- nvarchar (Unicode desteği)
- JSON kolonlar için nvarchar(max) kullanılabilir (Laravel JSON cast ile)

---

## MODEL İLİŞKİ STRATEJİLERİ

### Personel Modeli İlişkileri
```php
// Employee Model
hasMany(PerformanceReview::class)
hasMany(ExitInterview::class)
hasMany(HealthSafetyRecord::class)
hasMany(TimeTracking::class)
hasMany(Document::class)
belongsToMany(Training::class) through TrainingParticipant
belongsToMany(Benefit::class) through PersonelBenefit
hasMany(Cost::class)
belongsToMany(Project::class) through ProjectMember
hasMany(Leave::class)
hasMany(Advance::class)
hasMany(Attendance::class)
hasMany(Task::class)
hasMany(Payroll::class)
hasMany(Shift::class)
```

### Department Modeli İlişkileri
```php
// Department Model
hasMany(Position::class)
hasMany(Cost::class)
hasMany(JobPosting::class)
hasMany(Employee::class)
```

### Project Modeli İlişkileri
```php
// Project Model
belongsToMany(Employee::class) through ProjectMember
hasMany(TimeTracking::class)
hasMany(Cost::class)
hasMany(Task::class)
```

### View'larda Gösterilecek İlişkiler

#### Personel Show Sayfası
- Performans Değerlendirmeleri (son 5, tümüne link)
- Çıkış Görüşmeleri (varsa)
- İSG Kayıtları (son 5, şiddet seviyesi ile)
- Zaman Takibi (son 30 gün, toplam saatler)
- Dokümanlar (son 10, türüne göre)
- Eğitimler (katıldığı/katılacağı)
- Yan Haklar (aktif olanlar)
- Maliyetler (son 10)
- Projeler (aktif projeler)
- İzinler (yaklaşan/aktif)
- Avanslar (bekleyen/onaylanan)

#### Departman Show Sayfası
- Personeller (aktif personel listesi)
- Pozisyonlar (bu departmana ait)
- Projeler (departman ile ilişkili)
- Maliyetler (toplam maliyet, son 10 kayıt)
- İş İlanları (aktif ilanlar)

#### Proje Show Sayfası
- Personeller (proje üyeleri, rolleri ile)
- Maliyetler (proje maliyetleri, toplam)
- Zaman Takibi (proje için harcanan saatler, toplam)
- Görevler (proje ile ilişkili görevler)

---

**Not:** Bu şema Laravel migration + MSSQL için optimize edilmiştir. Migration dosyaları oluşturulurken bu yapı referans alınmalıdır. Model ilişkileri eager loading ile yüklenmeli, N+1 query problemi önlenmelidir.
