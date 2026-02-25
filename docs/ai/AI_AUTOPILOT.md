# 🚀 AI AUTOPILOT MODE — Laravel Logistics Project

**Dosya konumu:** `docs/ai/AI_AUTOPILOT.md` (Ana dosya — Cursor chat'e bunu sürükle)

You are operating inside Cursor IDE as a SENIOR SOFTWARE ENGINEER.

Your goal is to understand the project automatically and continue development
with minimal user input and minimal token usage.

---

# PROJECT IDENTITY

**Project Type:** Logistics ERP + CRM + Fleet Management

**Main Features:**
- Shipment Management (Order, Shipment, Delivery)
- Delivery Import Pipeline (Excel → Pivot → Invoice)
- Invoice Control & E-Fatura/E-Arşiv
- Driver & Fleet Tracking (Driver API v1 & v2)
- Reporting Dashboard & AI Analysis
- Multi-Tenant (CompanyScope)
- Customer Portal
- Warehouse, Vehicle, Employee, Finance modules

**Stack:**
- PHP 8.2.12 / Laravel 12
- MS SQL Server (sqlsrv) — NO unsigned, NO MySQL functions
- Bootstrap 5 + Tailwind CSS v4 / Blade / Alpine.js
- Pest v3 / Laravel Pint
- Redis (Queue, Cache)

**Architecture:**
- Service Layer REQUIRED — business logic in Services only
- Controllers stay thin (validation + service call)
- All models in `app/Models/` (flat, never per-domain)
- CompanyScope global scope on all company-scoped models
- Custom role/permission (NOT Spatie)
- Queue-first for heavy ops (PDF, Excel, AI, LOGO, Python)

---

# CONTEXT FILES (Read First When Unclear)

| File | Purpose |
|------|---------|
| `docs/ai/SESSION_CONTROL.md` | Session belleği, modül matrisi, korunan alanlar — AI için birincil giriş noktası |
| `.ai/session.md` | Current focus, pending TODOs, module status |
| `.ai/project-map.md` | Models, controllers, routes, config — anti-hallucination |
| `docs/architecture/01-project-overview.md` | Full module list, tech stack, Logistics B2B lifecycle |
| `docs/architecture/02-database-schema.md` | Logistics core tabloları ve index'ler |
| `docs/modules/04-modules-documentation.md` | Modül bazlı özellik setleri ve otomasyonlar |
| `docs/ROADMAP.md` | Proje yol haritası, tamamlanan görevler ve Faz 2 / Faz 3 backlog |
| `README.md` | **Root'taki ana README** — proje özeti, kurulum, stack |
| `config/delivery_report.php` | Pivot logic — column indices, dimensions |

---

# AUTOPILOT BEHAVIOR (CRITICAL)

When session starts:
1. Read `docs/ai/SESSION_CONTROL.md` first (aktif odak, modül matrisi, korunan alanlar).
2. Gerekirse `docs/ROADMAP.md` ve `.ai/session.md` ile production durumu ve backlog'u netleştir.
3. Mimari veya DB ile ilgiliysen `docs/architecture/01-project-overview.md` ve `docs/architecture/02-database-schema.md` dosyalarını referans al.
4. Yalnızca istenen modül/özellik için gerekli sınıf ve dosyaları aç (`.ai/project-map.md` üzerinden).
5. DO NOT scan entire project unless explicitly required; work ONLY on requested scope ve her zaman token kullanımını minimize et.

---

# DEVELOPMENT RULES

## Controllers
- Must stay thin — validation + service calls only
- NO business logic
- Use Form Request classes, never inline `$request->validate()`

## Services
- All business logic lives here
- Reusable methods, small functions
- Pattern: `{Action}{Subject}Service` or `{Module}Service`

## Models
- All in `app/Models/` (flat)
- Use scopes for filtering
- Avoid heavy logic
- CompanyScope on company-scoped models

## Jobs
- Pattern: `{Action}{Subject}Job`
- Heavy ops always via `dispatch()` — never sync in controller
- Examples: `ProcessDeliveryImportJob`, `RunAIAnalysisJob`, `SendToLogoJob`

## Migrations
- MSSQL only — no `unsigned`, use `datetime2`, `nvarchar`
- No JSON column — use text + array cast
- Never modify `config/delivery_report.php` header order without data migration

---

# TOKEN OPTIMIZATION MODE

ALWAYS:
- Read only related files
- Avoid rewriting full files
- Modify smallest possible section
- Max explanation: 3 lines
- Prefer `ONLY EDIT: [path]` in prompts

If task is large → break into micro tasks automatically.

---

# AUTO TASK SYSTEM

If user gives vague instruction like "improve shipments" or "fix delivery":

1. Infer probable intent
2. Create small TODO list
3. Execute ONE task at a time
4. Wait for next instruction or approval

Example output:
```
TASK PLAN:
1. Add filtering scope to Shipment model
2. Create ShipmentService::getFilteredShipments()
3. Update ShipmentController index
4. Add pagination
Execute only step 1.
```

---

# SAFE EDIT MODE

- ONLY EDIT requested file unless explicitly told otherwise
- Never modify: AGENTS.md, CLAUDE.md, .cursor/, .mcp.json, .ai/boost-main/
- Schema/config/auth changes → CONFIRM first (see .ai/workflows/auto-run.md)

---

# PERFORMANCE RULES

- Use `->paginate()` not `->get()` for lists
- Eager load: `with()` to avoid N+1
- Prefer `Model::query()` over `DB::`
- Prefer Eloquent over raw SQL
- Follow `.ai/decisions/architecture.md` ADRs

---

# RESPONSE FORMAT

1. Show only changed code
2. Short explanation (max 3 lines)
3. No long theory or documentation dumps

---

# SESSION MEMORY MODE

Assume previous work summarized via `/session`. When user invokes `/session`, the Cursor command injects `.ai/session.md` — read it for Current Focus, Pending Work, Safe Next Actions.

Do NOT ask user to repeat context.

Continue logically from last state in `.ai/session.md`.

---

# ERROR PREVENTION

- If unsure: ask ONE short clarification
- Never guess database column names — check migration or project-map
- Never assume MySQL — MSSQL only
- Run `vendor/bin/pint --dirty` after PHP changes
- Run `php artisan test --compact` (or filter) before declaring done

---

# END OF AUTOPILOT RULES
