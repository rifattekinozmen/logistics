# Analytics KPI Özeti (Finance / Fleet / Operations)

**Dosya konumu:** `docs/analytics/kpi-overview.md`

Bu doküman, admin analytics ekranlarında kullanılan temel metrikleri ve bunların nerede hesaplandığını/test edildiğini özetler.

İlgili kod dosyaları:

- Servis: `app/Analytics/Services/AnalyticsDashboardService.php`
- Blade view'lar: `resources/views/admin/analytics/*.blade.php`
- Testler: `tests/Feature/AnalyticsTest.php`

---

## 1. Finance Analytics (getFinancialMetrics)

**Metod:** `AnalyticsDashboardService::getFinancialMetrics(Company $company, Carbon $start, Carbon $end): array`

**Kullandığı tablolar:**
- `orders` — gelir (freight_price, status='invoiced')
- `payments` — gider (payment_type='outgoing', status=1)

**Dönen metrikler:**
- `revenue` — Belirtilen aralıkta faturalandırılmış siparişlerin `freight_price` toplamı.
- `expenses` — `payments` tablosunda outgoing ve ödenmiş kayıtların `amount` toplamı.
- `net_profit` — `revenue - expenses`.
- `profit_margin` — `(net_profit / revenue) * 100`, revenue 0 ise 0.
- `monthly_trend` — Ay bazlı gelir trendi (ay etiketi + toplam), DB sürücüsüne göre uygun tarih format fonksiyonları ile hesaplanır (SQL Server / MySQL / SQLite desteği).

**Test kapsamı (AnalyticsTest):**
- Finance sayfasına erişim (`admin.analytics.finance`) ve `metrics` view değişkeninin varlığı.
- Örnek bir order + payment ile revenue/expenses/net_profit alanlarının beklenen şekilde dolup dolmadığı.
- Boş veri durumunda revenue=0 ve monthly_trend'in boş da olsa array olması (500 hatası olmaması).

---

## 2. Operations Analytics (getOperationalKpis)

**Metod:** `AnalyticsDashboardService::getOperationalKpis(Company $company): array`

**Kullandığı tablolar:**
- `orders` — sipariş sayıları, durum dağılımı, teslimat süreleri.
- `shipments` — zamanında teslimat oranı (orders ile join).

**Dönen metrikler:**
- `total_orders` — Son 30 günde oluşturulan sipariş adedi.
- `completed_orders` — Son 30 günde status='delivered' olan sipariş adedi.
- `completion_rate` — `(completed_orders / total_orders) * 100`.
- `on_time_deliveries` — `shipments.delivery_date <= shipments.pickup_date` olan teslimatlar.
- `total_deliveries` — Son 30 günde tamamlanan tüm teslimatlar.
- `on_time_delivery_rate` — `(on_time_deliveries / total_deliveries) * 100`.
- `avg_processing_time` — Sipariş oluşturma ile teslimat arasındaki ortalama süre (saat cinsinden), DB sürücüsüne göre uygun timestamp diff ifadesiyle hesaplanır.
- `status_breakdown` — Sipariş durum dağılımı:
  - Her status için: `status`, `label`, `color`, `chartColor`, `count`, `percentage`.
  - Label ve renkler sipariş lifecycle’ına göre sabit bir mapping ile belirlenir.

**Test kapsamı (AnalyticsTest):**
- Operations sayfasına erişim (`admin.analytics.operations`) ve `kpis` değişkeninin varlığı.
- Örnek delivered order ile `total_orders` ≥ 1 ve beklenen ana anahtarların (`completed_orders`, `completion_rate`, `status_breakdown`) bulunması.
- Boş veri durumunda total_orders=0 ve status_breakdown'in array olarak dönmesi.

---

## 3. Fleet Analytics (getFleetPerformance)

**Metod:** `AnalyticsDashboardService::getFleetPerformance(Company $company): array`

**Kullandığı tablolar:**
- `vehicles` + `branches` — filo büyüklüğü ve company scoping.
- `shipments` — aktif sevkiyat sayıları üzerinden utilization tahmini.

**Dönen metrikler:**
- `total_vehicles` — Şirketin aktif araç sayısı (status=1).
- `active_vehicles` — Son 30 günde aktif sevkiyatı olan araç sayısı (distinct vehicles).
- `idle_vehicles` — `total_vehicles - active_vehicles`.
- `utilization_rate` — `(active_vehicles / total_vehicles) * 100`.
- `maintenance_due` — Şimdilik demo amaçlı, toplam araç sayısının yaklaşık %20’si.
- `avg_fuel_efficiency` — Şimdilik sabit demo değeri (ileride gerçek tüketim/verimlilik datası ile değiştirilebilir).
- `vehicle_utilization` — İlk 10 araç için shipment sayısı üzerinden 0–100 arası utilization yüzdesi.
- `maintenance_alerts` — Bakım uyarı listesi (araç plaka/model + rastgele son bakım tarihi, km ve urgency etiketi).

**Test kapsamı (AnalyticsTest):**
- Fleet sayfasına erişim (`admin.analytics.fleet`) ve `performance` değişkeninin varlığı.
- Örnek 3 araçla `total_vehicles=3` ve ana anahtarların (`active_vehicles`, `vehicle_utilization`, `maintenance_alerts`) bulunması.
- Boş veri durumunda total_vehicles=0 ve vehicle_utilization'in array olması.

---

## 4. Test Planı (Özet)

Mevcut `tests/Feature/AnalyticsTest.php` dosyası halihazırda aşağıdaki senaryoları kapsar:

- Finance, Operations, Fleet sayfalarına yetkili kullanıcı erişimi.
- Her sayfa için en az bir **dolu veri** senaryosu (örnek order/payment/vehicle ile).
- Her sayfa için **boş veri** senaryosu (hiç kayıt yokken 500 hatası almadan render).
- Analytics route'larına giriş yapmadan erişilememesi (auth zorunluluğu).

İleride gelişmiş metrikler eklendiğinde:

- Yeni metrikler için **en az birer happy path** testi (örneğin yeni bir KPI eklenirse, testte bu anahtarın varlığı ve tip kontrolü).
- Kritik oran/sayılar için extreme durumlar (0 bölme, hiç shipment yokken oranların 0 olması vb.) ayrıca test edilmelidir.

