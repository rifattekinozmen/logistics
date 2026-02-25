# Laravel B2B Ödeme Mimarisi --- TCMB Uyumlu Yasal & Enterprise Teknik Rehber (V3.0)

------------------------------------------------------------------------

## ⚖️ Hukuki Bildirim

Bu doküman Türkiye Cumhuriyet Merkez Bankası (TCMB) düzenlemeleri ve
6493 Sayılı Ödeme Hizmetleri Kanunu esas alınarak hazırlanmış teknik
mimari rehberdir. Hukuki danışmanlık yerine geçmez. Production kullanımı
öncesi hukuk ve mali müşavir onayı önerilir.

------------------------------------------------------------------------

## 1. Amaç

Laravel tabanlı B2B / lojistik platformlarında:

-   Lisans gerektirmeyen ödeme modeli kurmak
-   TCMB mevzuatına uyum sağlamak
-   Fintech seviyesinde teknik altyapı oluşturmak
-   Gelecekte ödeme kuruluşuna dönüşebilecek mimari hazırlamak

------------------------------------------------------------------------

## 2. Mevzuat Özeti (6493 Sayılı Kanun)

### Lisans Gerektiren İşlemler

-   Kullanıcı adına para tutmak
-   Dijital cüzdan (wallet)
-   Kullanıcılar arası para transferi
-   Escrow / emanet ödeme
-   IBAN havuz hesabı

### Lisans Gerektirmeyen Model

Laravel sistemi:

✅ Para tutmaz\
✅ Transfer yapmaz\
✅ Sadece kayıt ve raporlama yapar

------------------------------------------------------------------------

## 3. Yasal Ödeme Akışı

    Müşteri
       ↓
    Ödeme Kuruluşu (iyzico / PayTR / Paynet)
       ↓
    Banka / Kart / FAST
       ↓
    Laravel (Log & Muhasebe)

  Kavram        Açıklama
  ------------- --------------------
  Cari Hesap    Muhasebe kaydı
  Gerçek Para   Provider tarafında

------------------------------------------------------------------------

## 4. Sistem Domain Mimarisi

    app/
     ├── Domains/
     │    ├── Orders
     │    ├── Payments
     │    ├── Ledger
     │    └── Security
     ├── Services/
     │    ├── Payment
     │    └── Fraud
     ├── Jobs
     └── Events

Amaç: - Modüler yapı - Gateway bağımsız sistem - Enterprise
ölçeklenebilirlik

### Bu Projede Karşılığı

Logistics projesinde bu domain yapısının somut karşılıkları kısaca şöyledir:

- **Orders / Payments / Ledger:** `orders`, `payments` ve ilgili cari hareket tabloları ile `PaymentController`, `FinanceDashboardService` ve ödeme hatırlatma cronjob'ları (bkz. `docs/architecture/02-database-schema.md`, `docs/ROADMAP.md`).
- **Security:** RBAC ve multi-tenant izolasyonu için `Auth/RBAC` mimarisi, `CompanyScope` ve `ActiveCompany` middleware'i (bkz. `docs/architecture/01-project-overview.md`, `docs/architecture/06-company-settings-and-switch.md`).
- **Log & Muhasebe:** Ödeme olaylarının sadece kayıt ve raporlanması; para hareketi ödeme kuruluşu/banka tarafında kalır (bu dokümandaki \"Lisans Gerektirmeyen Model\" ile birebir uyumlu).

Teknik uygulama detayları için proje içindeki mimari dokümanlar; mevzuat ve yasal sınırlar için ise bu TCMB uyumlu rehber kanonik referans kabul edilmelidir.

------------------------------------------------------------------------

## 5. Veritabanı Tasarımı

### orders

-   id
-   user_id
-   total_amount
-   currency
-   status
-   payment_status

### payments

-   id
-   order_id
-   gateway
-   gateway_payment_id
-   amount
-   status
-   idempotency_key
-   payload_hash

### ledger_entries (Immutable)

-   id
-   account_id
-   debit
-   credit
-   immutable_hash

### audit_logs

-   actor_id
-   action
-   ip_address
-   payload_hash

------------------------------------------------------------------------

## 6. Payment Service Layer

Akış:

Controller → PaymentManager → GatewayResolver → Adapter

### PaymentManager

-   Intent oluşturur
-   Gateway seçer
-   Webhook doğrular
-   Ledger event üretir

------------------------------------------------------------------------

## 7. PaymentManager (Örnek)

``` php
class PaymentManager
{
    public function createPayment($order, $gateway)
    {
        $adapter = app(GatewayResolver::class)->resolve($gateway);

        return $adapter->createIntent([
            'order_id'=>$order->id,
            'amount'=>$order->total_amount,
            'currency'=>'TRY'
        ]);
    }
}
```

------------------------------------------------------------------------

## 8. Gateway Adapter Interface

``` php
interface PaymentGatewayInterface
{
    public function createIntent(array $data);
    public function verify(array $payload);
}
```

------------------------------------------------------------------------

## 9. Iyzico Adapter (Örnek)

``` php
class IyzicoAdapter implements PaymentGatewayInterface
{
    public function createIntent(array $data){}
    public function verify(array $payload){}
}
```

------------------------------------------------------------------------

## 10. PayTR Adapter (Örnek)

``` php
class PaytrAdapter implements PaymentGatewayInterface
{
    public function createIntent(array $data){}
    public function verify(array $payload){}
}
```

------------------------------------------------------------------------

## 11. Webhook Güvenliği

Akış:

    Webhook → Queue → Validation → DB Commit

Kontroller: - HMAC signature - Timestamp ±5 dk - IP whitelist - Replay
attack koruması

------------------------------------------------------------------------

## 12. Idempotency (Zorunlu)

Her ödeme isteği UUID anahtar ile korunur.

Avantaj: - Double charge engellenir - Retry güvenli olur

------------------------------------------------------------------------

## 13. Fraud Detection

-   Aynı IP tekrar ödeme
-   Kart davranış analizi
-   Başarısız deneme limiti
-   Risk score sistemi

```{=html}
<!-- -->
```
    score > 70 → Manual Review

------------------------------------------------------------------------

## 14. Audit & Loglama

Tutulması gerekenler: - Payment request - Provider response - Webhook
payload - Signature sonucu

Loglar silinemez, sadece arşivlenir.

------------------------------------------------------------------------

## 15. Güvenlik Standartları

Zorunlu: - HTTPS - ENV secrets vault - Key rotation - DB encryption at
rest

Önerilen: - ISO 27001 yaklaşımı - PCI scope dışı mimari

------------------------------------------------------------------------

## 16. Deployment Mimarisi

-   Nginx reverse proxy
-   Queue worker autoscale
-   Redis cache
-   Failover database

------------------------------------------------------------------------

## 17. Backup Politikası

  Süre       İşlem
  ---------- ----------------------
  Günlük     Snapshot
  Haftalık   Encrypted Backup
  Aylık      Offline Cold Storage

AES‑256 önerilir.

------------------------------------------------------------------------

## 18. TCMB Denetim Savunması

Denetçiye açıklama:

-   Sistem ödeme kuruluşu değildir
-   Settlement provider tarafındadır
-   Laravel muhasebe kayıt sistemidir

------------------------------------------------------------------------

## 19. Lisans Geçiş Roadmap

### Faz 1

Gateway entegrasyonu

### Faz 2

White‑label ödeme modeli

### Faz 3

TCMB Başvurusu - ISO27001 - AML sistemi - Risk yönetimi - İç denetim

------------------------------------------------------------------------

## 20. Production Checklist

-   Webhook CSRF hariç
-   Payload hash saklama
-   Duplicate webhook engelleme
-   Monitoring & alert

------------------------------------------------------------------------

## 21. Sonuç

Bu mimari:

✅ TCMB uyumlu\
✅ Lisans gerektirmez\
✅ Enterprise seviyede\
✅ Fintech dönüşümüne hazır

------------------------------------------------------------------------

**Versiyon:** 3.0\
**Durum:** Tam Teknik + Yasal Revizyon Tamamlandı
