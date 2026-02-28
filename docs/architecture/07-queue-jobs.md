# Queue ve Job Yapılandırması

**Dosya konumu:** `docs/architecture/07-queue-jobs.md`

Tüm kuyruklu job'lar `$queue` ve `$tries` ile standartlaştırılmıştır. Aynı Redis connection üzerinde queue isimleri ile öncelik ayrımı yapılır.

## Queue isimleri ve öncelik

| Queue       | Amaç                                                                 | Örnek kullanım                          |
|------------|----------------------------------------------------------------------|-----------------------------------------|
| `critical` | Ödeme, fatura, ERP/SAP senkronu — başarısız olursa muhasebe/finans etkilenir | Logo, E-Fatura, SAP event işleme        |
| `default`  | Günlük analiz, import, Python POC — ertesi tetiklemede tekrar denebilir     | AI analiz, teslimat import, Python push |
| `low`      | Raporlama, Excel toplu işlem — gecikmesi kabul edilebilir                   | Haftalık yakıt raporu, Excel işleme     |

## Job listesi

| Job                        | Queue      | Tries | Açıklama                    |
|----------------------------|------------|-------|-----------------------------|
| `SendToLogoJob`            | critical   | 3     | Logo ERP fatura gönderimi   |
| `SendEInvoiceJob`         | critical   | 3     | E-Fatura/E-Arşiv GIB gönderimi |
| `ProcessSapEventJob`      | critical   | 3     | SAP event işleme            |
| `RunAIAnalysisJob`         | default    | 2     | Günlük AI analizi           |
| `ProcessDeliveryImportJob` | default    | 2     | Teslimat raporu import      |
| `SendToPythonJob`          | default    | 2     | Python analitik POC         |
| `GenerateWeeklyReportJob`  | low        | 2     | Haftalık motorin raporu     |
| `ProcessExcelJob`          | low        | 2     | Excel toplu işleme          |

## Worker çalıştırma

Tek worker tüm kuyrukları dinleyebilir (öncelik sırasına göre):

```bash
php artisan queue:work redis --queue=critical,default,low
```

Kritik işler için ayrı worker (opsiyonel):

```bash
php artisan queue:work redis --queue=critical
```
