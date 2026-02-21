# AI Workflow & Cursor Kullanım Rehberi

Bu bölüm Cursor IDE ile proje geliştirirken kullanılan AI workflow'unu ve best practice'leri dokümante eder.

---

## AI_AUTOPILOT.md

**Konum:** `docs/ai/AI_AUTOPILOT.md` (Ana dosya)

Cursor'un projeyi otomatik anlamasını ve token tüketimini minimize etmesini sağlar.

### Kullanım

1. Cursor chat'i aç
2. `README.md` (root) veya `docs/ai/AI_AUTOPILOT.md` dosyasını chat'e sürükle
3. Chat'e `/session` yaz

Böylece Cursor:
- Projeyi otomatik yorumlar
- Büyük işi küçük task'lara böler
- Sadece gerekli dosyayı açar
- Gereksiz token harcamaz

---

## .ai/ Klasörü (Operasyonel Beyin)

AI workflow için kullanılan operasyonel dosyalar `.ai/` klasöründe bulunur:

| Dosya | Amaç |
|-------|------|
| `.ai/session.md` | Aktif geliştirme durumu, pending TODO'lar |
| `.ai/project-map.md` | Model, controller, route referansı — anti-hallucination |
| `.ai/rules/` | Core, MSSQL, context kuralları |
| `.ai/skills/` | Refactor, migration, Blade task rehberleri |
| `.ai/workflows/auto-run.md` | Ne zaman onay isteneceği |
| `.ai/decisions/architecture.md` | Architecture Decision Log (7 ADR) |

---

## Cursor + ChatGPT Pro Workflow

| Araç | Görevi |
|------|--------|
| ChatGPT | Mimar / Proje yöneticisi — mimari öneri, task breakdown, prompt optimizasyonu |
| Cursor IDE | Kod yazan developer — `/chat`, `/edit`, `/session` |

**Token tasarrufu:** Cursor'a sadece küçük, net görevler verin. Örnek:

```
ONLY EDIT: app/Services/ShipmentService.php
Add: getFilteredShipments() with date filter and pagination
Do not modify controller.
```

---

## Son Güncelleme

2026-02-21
