# Docs — 5N1K Index

Tek referans: Tüm dokümantasyonun **Ne, Neden, Nerede, Ne zaman, Nasıl, Kim** cevapları.

**Son güncelleme:** 2026-02-25

---

## Dokümantasyon Prosedürü

Aşağıdaki kurallar tüm doküman ekleme ve güncellemelerinde geçerlidir:

- **Yerleşim:** Tüm proje dokümantasyonu (MD) `docs/` altında tutulur. Root’ta yalnızca `README.md`, `ROADMAP.md` (stub), `AI_AUTOPILOT.md` (stub), `AGENTS.md`, `CLAUDE.md`, `LICENSE.md` bulunur.
- **Kategoriler:** `ai/`, `architecture/`, `workflows/`, `modules/`, `legal/`, `reference/`, `compliance/`, `sessions/`. Yeni doküman ilgili kategori klasörüne eklenir.
- **Dosya adı:** Küçük harf, kelimeler tire ile ayrılır (kebab-case). Mimari için numara öneki kullanılır: `01-`, `02-`, `06-` (yeni dosyalar `07-`, `08-` …). Session özetleri: `YYYY-MM-DD-kısa-başlık.md`.
- **Güncelleme:** Yeni doküman veya kategori eklendiğinde bu index (`INDEX-5N1K.md`) ve `docs/README.md` güncellenir.

---

## 5N1K Özet Tablosu

| Soru | Cevap |
|------|--------|
| **Ne** | Proje dokümantasyonu: mimari, geliştirme rehberi, AI/Cursor kuralları, yol haritası, referans ve hukuki metinler. |
| **Neden** | Geliştirici ve AI’ın tek kaynaktan doğru karar alması; proje durumunun ve eksiklerin net görülmesi. |
| **Nerede** | Tüm MD içerik canonical olarak `docs/` altında; root’ta sadece README, stub’lar ve IDE kuralları (AGENTS, CLAUDE, LICENSE). |
| **Ne zaman** | Her önemli özellik/sprint sonrası ROADMAP ve session belleği; mimari değişince architecture docs; yeni modül/referans eklenince bu index güncellenir. |
| **Nasıl** | Cursor’da `docs/ai/AI_AUTOPILOT.md` veya `README.md` sürükleyip `/session`; belirli konu için bu index’ten ilgili dosyaya gidilir. |
| **Kim** | Geliştirici, AI agent, proje yöneticisi; hukuki metinler için hukuk/üst yönetim. |

---

## Doküman Envanteri

### Giriş ve proje durumu

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Ana dokümantasyon girişi | [docs/README.md](README.md) | Tüm kategorilere link, .ai/ referansı, dosya konumları tablosu. |
| 5N1K index (bu dosya) | [docs/INDEX-5N1K.md](INDEX-5N1K.md) | Doküman envanteri ve 5N1K cevapları. |
| Yol haritası | [docs/ROADMAP.md](ROADMAP.md) | Tamamlanan görevler, production checklist, backup/DR, sonraki aşamalar. |

### AI / Cursor

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Cursor autopilot kuralları | [docs/ai/AI_AUTOPILOT.md](ai/AI_AUTOPILOT.md) | Proje kimliği, context dosyaları, token optimizasyonu, session memory. |
| AI workflow rehberi | [docs/ai/README.md](ai/README.md) | Cursor kullanımı, .ai/ klasörü açıklaması. |
| Session kontrol / modül matrisi | [docs/ai/SESSION_CONTROL.md](ai/SESSION_CONTROL.md) | Session belleği, modül olgunluk matrisi, git commit kuralları, korunan alanlar. |

### Mimari

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Proje genel bakış | [docs/architecture/01-project-overview.md](architecture/01-project-overview.md) | Tech stack, modül yapısı, B2B order lifecycle, roller. |
| Veritabanı şeması | [docs/architecture/02-database-schema.md](architecture/02-database-schema.md) | Tablolar, kolonlar, indexler, MSSQL notları. |
| Şirket ayarları ve geçiş | [docs/architecture/06-company-settings-and-switch.md](architecture/06-company-settings-and-switch.md) | Multi-tenant şema, UI akışı, Laravel uygulaması. |

### İş akışları

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Geliştirme rehberi | [docs/workflows/03-development-guide.md](workflows/03-development-guide.md) | Modül yapısı, service pattern, queue, cron, order–payment–shipment flow, TODO listesi. |
| UX / sayfa akışı | [docs/workflows/05-ux-page-flow.md](workflows/05-ux-page-flow.md) | Rol bazlı sayfa akışları, B2B UX flow. |

### Modüller

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Modül dokümantasyonu | [docs/modules/04-modules-documentation.md](modules/04-modules-documentation.md) | 15+ modül: Warehouse, FuelPrice, Delivery, Vehicle, AI, Excel vb. |

### Hukuki

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Hizmet sözleşmesi | [docs/legal/07-service-agreement.md](legal/07-service-agreement.md) | SaaS sözleşmesi, müşteri hakları, fiyatlandırma, KVKK. |

### Referans

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Delivery report pivot & fatura satırları | [docs/reference/delivery-report-pivot-and-invoice-lines.md](reference/delivery-report-pivot-and-invoice-lines.md) | Pivot tasarımı, fatura satırı eşleme, uygulama yol haritası. |
| Cemiloglu pivot referansı | [docs/reference/cemiloglu-pivot-reference.md](reference/cemiloglu-pivot-reference.md) | BOŞ-DOLU / DOLU-DOLU hesaplama mantığı. |
| Müşteri portalı erişimi | [docs/reference/customer-portal-access.md](reference/customer-portal-access.md) | Müşteri portalı kullanıcı kurulumu. |
| SAP entegrasyon rehberi | [docs/reference/sap-integration/sap_logistics_integration_guide.md](reference/sap-integration/sap_logistics_integration_guide.md) | SAP S/4HANA entegrasyon mimarisi, SD eşleşmesi. |
| SAP entegrasyon (Value Edition) | [docs/reference/sap-integration/sap_logistics_integration_guide2.md](reference/sap-integration/sap_logistics_integration_guide2.md) | SAP entegrasyonu, dashboard, yatırımcı sunumu. |

### Uyumluluk (Compliance)

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| TCMB B2B ödeme mimarisi v2 | [docs/compliance/tcmb_pay/Laravel_B2B_Odeme_Mimarisi_TCMB_Uyumlu_v2.md](compliance/tcmb_pay/Laravel_B2B_Odeme_Mimarisi_TCMB_Uyumlu_v2.md) | TCMB uyumlu yasal ve teknik rehber (v2). |
| TCMB B2B ödeme mimarisi v3 | [docs/compliance/tcmb_pay/Laravel_B2B_Odeme_Mimarisi_TCMB_Uyumlu_v3.md](compliance/tcmb_pay/Laravel_B2B_Odeme_Mimarisi_TCMB_Uyumlu_v3.md) | TCMB uyumlu yasal ve enterprise teknik rehber (v3). |
| TCMB ödeme rehberi | [docs/compliance/tcmb_pay/laravel_b2b_odeme_mimarisi_tcmb_rehber.md](compliance/tcmb_pay/laravel_b2b_odeme_mimarisi_tcmb_rehber.md) | TCMB ödeme hizmetleri, lisanssız model açıklaması. |

### Oturum arşivi (Sessions)

| Dosya | Konum | Tek cümle özeti |
|-------|--------|------------------|
| Vite entegrasyonu & proje boşlukları | [docs/sessions/2026-02-21-vite-integration-and-project-gaps.md](sessions/2026-02-21-vite-integration-and-project-gaps.md) | Vite layout, README, session arşiv ilk dosya. |
| Docs & backlog alignment | [docs/sessions/2026-02-25-docs-and-backlog-alignment.md](sessions/2026-02-25-docs-and-backlog-alignment.md) | Dokümantasyon hizalaması ve Faz 2/Faz 3 backlog özet oturumu. |

**Prosedür:** Yeni oturum özetleri `docs/sessions/` altına eklenir; dosya adı `YYYY-MM-DD-kısa-başlık.md` (örn. `2026-02-25-analytics-dashboard.md`).

---

## Eksikler ve Kurallar

Genel isimlendirme ve yerleştirme kuralları yukarıdaki **Dokümantasyon Prosedürü** bölümünde tanımlıdır.

| Konu | Açıklama |
|------|----------|
| **Mimari numaralama** | `architecture/` içinde yalnızca 01, 02, 06 kullanılıyor (03–05 atlanmış). Yeni dosyalar 07, 08 … ile eklenir. |
| **Session arşivi** | Prosedüre göre: `docs/sessions/`, dosya adı `YYYY-MM-DD-kısa-başlık.md`. |
| **Root’taki MD’ler** | Prosedüre göre: README, stub’lar (ROADMAP, AI_AUTOPILOT), AGENTS, CLAUDE, LICENSE. Asıl içerik `docs/` içinde. |

---

## Kategori Bazlı 5N1K

| Kategori | Ne | Neden | Nerede | Ne zaman güncellenir | Nasıl erişilir | Kim |
|----------|-----|-------|--------|----------------------|----------------|-----|
| **Giriş / Index** | README, INDEX-5N1K, ROADMAP | Tek giriş noktası, neyin nerede olduğunu göstermek | `docs/README.md`, `docs/INDEX-5N1K.md`, `docs/ROADMAP.md` | ROADMAP: milestone sonrası; INDEX: yeni kategori/dosya eklenince | README’den linklerle | Hepsi |
| **AI / Cursor** | Autopilot kuralları, session kontrol, workflow | Cursor’ın projeyi anlaması, token tasarrufu, session devamı | `docs/ai/` | Session belleği her önemli özellik sonrası | Chat’e AI_AUTOPILOT veya SESSION_CONTROL sürükle, `/session` | AI agent, geliştirici |
| **Mimari** | Proje bakış, DB şeması, multi-tenant | Mimari karar ve veri modeli referansı | `docs/architecture/` | Şema veya mimari değişince | Doğrudan dosya linki | Geliştirici, AI |
| **Workflows** | Geliştirme rehberi, UX akışı | Günlük geliştirme ve UX kararları | `docs/workflows/` | Yeni modül/akış eklenince | Doğrudan dosya linki | Geliştirici |
| **Modüller** | Modül listesi ve açıklamaları | Hangi modül ne yapıyor | `docs/modules/` | Yeni modül eklenince | Doğrudan dosya linki | Geliştirici, PM |
| **Legal** | Hizmet sözleşmesi | Yasal uyum, müşteri ilişkisi | `docs/legal/` | Sözleşme değişikliğinde | Doğrudan dosya linki | Hukuk, üst yönetim |
| **Reference** | Pivot, SAP, portal rehberleri | Teknik detay, entegrasyon referansı | `docs/reference/` | Entegrasyon değişince | Doğrudan dosya linki | Geliştirici |
| **Compliance** | TCMB ödeme rehberleri | Ödeme mevzuatı uyumu | `docs/compliance/tcmb_pay/` | Mevzuat/rehber güncellenince | Doğrudan dosya linki | Geliştirici, hukuk |
| **Sessions** | Oturum özetleri | Yapılanların arşivi, sonraki adımlar | `docs/sessions/` | Önemli özellik tamamlandığında | Dosya adı: `YYYY-MM-DD-başlık.md` | Geliştirici, docs-agent |
