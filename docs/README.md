# Logistics Project – Documentation

Technical documentation for the Logistics ERP + CRM + Fleet Management system.
**Stack:** PHP 8.2 / Laravel 12 / MSSQL / Bootstrap 5 + Tailwind CSS v4

---

## Cursor AI Autopilot

**`docs/ai/AI_AUTOPILOT.md`** — Cursor IDE'nin projeyi otomatik anlamasını sağlar.

**Kullanım:** `README.md` (root) veya `docs/ai/AI_AUTOPILOT.md` dosyasını chat'e sürükle → `/session` yaz.

Detaylı workflow rehberi: [AI Workflow & Cursor Kullanım](ai/README.md)

---

## Project Status

| Document | Contents |
|----------|----------|
| [ROADMAP](ROADMAP.md) | Tamamlanan görevler, production checklist, sonraki aşamalar |

---

## AI Workflow Brain (.ai/)

The `.ai/` folder is the AI workflow system. Load these before starting any coding session:

| File | Purpose |
|---|---|
| [`.ai/session.md`](../.ai/session.md) | Current dev status, active modules, pending work |
| [`.ai/project-map.md`](../.ai/project-map.md) | Architecture reference — models, controllers, services, routes |
| [`.ai/rules/`](../.ai/rules/) | Behavioral constraints (core, MSSQL, context) |
| [`.ai/skills/`](../.ai/skills/) | Task-specific guides (refactor, migrations, Blade, queries) |
| [`.ai/subagents/`](../.ai/subagents/) | Domain agent definitions (backend, database, UI, docs, performance) |
| [`.ai/workflows/auto-run.md`](../.ai/workflows/auto-run.md) | When to execute vs confirm |
| [`.ai/decisions/architecture.md`](../.ai/decisions/architecture.md) | Architecture Decision Log (7 ADRs) |

---

## Architecture

| Document | Contents |
|---|---|
| [Project Overview](architecture/01-project-overview.md) | Tech stack, module structure, roles, roadmap |
| [Database Schema](architecture/02-database-schema.md) | All tables, columns, indexes, MSSQL notes |
| [Company Settings & Switch](architecture/06-company-settings-and-switch.md) | Multi-tenant DB schema, UI flow, Laravel implementation |

---

## Workflows & Guides

| Document | Contents |
|---|---|
| [Development Guide](workflows/03-development-guide.md) | Module conventions, service pattern, queue, AI module, TODO list |
| [UX Page Flow](workflows/05-ux-page-flow.md) | Role-based page flows, UX principles |

---

## Modules

| Document | Contents |
|---|---|
| [Modules Documentation](modules/04-modules-documentation.md) | All 15+ modules: Warehouse, FuelPrice, Delivery, Shift, Mobile, Vehicle, WorkOrder, Location, Attendance, Notification, AI, Excel |

---

## Legal

| Document | Contents |
|---|---|
| [Service Agreement](legal/07-service-agreement.md) | SaaS service agreement, customer rights, pricing, KVKK / privacy |

---

## Sessions

`docs/sessions/` — archived session summaries, created by docs-agent after completing significant features.

---

## Reference

| Document | Contents |
|---|---|
| [Delivery Report Pivot & Invoice Lines](reference/delivery-report-pivot-and-invoice-lines.md) | Pivot table design, invoice line mapping, implementation roadmap |
| [Cemiloglu Pivot Reference](reference/cemiloglu-pivot-reference.md) | Source pivot logic for BOŞ-DOLU/DOLU-DOLU calculations |
| [Customer Portal Access](reference/customer-portal-access.md) | How to set up customer portal users |
| [SAP Integration Guide](reference/sap-integration/sap_logistics_integration_guide.md) | SAP S/4HANA entegrasyon mimarisi, SD eşleşmesi |
| [SAP Integration Guide (Value Edition)](reference/sap-integration/sap_logistics_integration_guide2.md) | SAP entegrasyonu + Dashboard + Investor presentation |

---

## Dosya Konumları (Hangi Dosya Nerede?)

| Dosya | Konum | Açıklama |
|-------|-------|----------|
| **README.md** | `README.md` (root) | Ana proje girişi — GitHub/Git varsayılan |
| **ROADMAP.md** | `docs/ROADMAP.md` | Yol haritası, tamamlanan görevler |
| **AI_AUTOPILOT.md** | `docs/ai/AI_AUTOPILOT.md` | Cursor AI autopilot kuralları |
| **AGENTS.md** | `AGENTS.md` (root) | Cursor agent kuralları (otomatik yüklenir) |

Root'taki `AI_AUTOPILOT.md` ve `ROADMAP.md` sadece yönlendirme stub'ıdır.

---

**Last updated:** 2026-02-21
