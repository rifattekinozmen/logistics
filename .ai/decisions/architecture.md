# Architecture Decision Log

Decisions made about this codebase that must not be reversed without deliberate, documented discussion.

---

## ADR-001: Single Flat Models Directory

**Decision:** All Eloquent models live in `app/Models/` (flat), not per-domain.
**Rationale:** Domain controllers and services are separated, but models are shared resources. `Order` is used by Delivery, Finance, Customer, and Driver modules simultaneously. Per-domain models would create circular dependencies.
**Impact:** When adding a new model, create it in `app/Models/`, never in `app/{Module}/Models/`.
**Do not reverse:** Moving models per-domain would require updating 22 services and 31 controllers, with high risk of import errors and cross-domain confusion.

---

## ADR-002: Custom Role/Permission System

**Decision:** Custom `CustomRole`/`CustomPermission` tables and middleware instead of `spatie/laravel-permission`.
**Rationale:** The system was built before Spatie integration was considered; the custom system is sufficient for this use case and avoids a complex migration of existing permission data.
**Tables:** `custom_roles`, `custom_permissions`, `role_user` (pivot), `permission_role` (pivot)
**Middleware:** `RoleMiddleware`, `PermissionMiddleware` in `app/Core/Middleware/`
**Permissions cached:** Redis via `PermissionMiddleware`
**Do not add:** `spatie/laravel-permission` without first migrating all existing role/permission data and updating all middleware.

---

## ADR-003: CompanyScope as Global Scope

**Decision:** `CompanyScope` (in `app/Core/Scopes/CompanyScope.php`) is applied as a global scope on all company-scoped models.
**Rationale:** Automatic multi-tenant isolation without developers needing to remember `->where('company_id', ...)` on every query. Prevents accidental cross-company data exposure.
**Pattern:** Company-scoped models call `static::addGlobalScope(new CompanyScope())` in `booted()`.
**Cross-company queries:** Must explicitly use `->withoutGlobalScope(CompanyScope::class)`.
**Do not remove:** The global scope is the primary multi-tenant security boundary. Removing it requires auditing all 200+ queries in the application for company_id filtering.

---

## ADR-004: Config-Driven Delivery Report Pivot

**Decision:** `config/delivery_report.php` drives all pivot logic — column headers, date indices, numeric indices, pivot dimensions, material formulas, invoice line mapping.
**Rationale:** Two report types exist (endustriyel_hammadde, dokme_cimento) and more may be added. Config-driven approach avoids code duplication in `DeliveryReportPivotService`.
**Critical constraint:** `delivery_report_rows.row_data` stores arrays indexed by integer position that **directly maps to** the `headers` array position in the config for each report type. Changing header order invalidates all existing stored rows.
**Do not:** Hardcode column indices in `DeliveryReportPivotService`. Always read from config.
**Do not:** Reorder headers in config without a full data migration of existing `delivery_report_rows`.

---

## ADR-005: MSSQL-Only Database Conventions

**Decision:** All migrations and queries are written exclusively for MS SQL Server. No MySQL/SQLite compatibility maintained.
**Rationale:** Production and development databases are both MSSQL. Maintaining cross-DB compatibility adds complexity with no benefit.
**Conventions:**
- No `->unsigned()` (MSSQL has no unsigned integers)
- Use `->string()` / `->text()` for Unicode text (nvarchar)
- Use `->timestamps()` / `->softDeletes()` for datetime2 columns
- No `JSON` column type — use `->text()` with `'array'` cast
- No MySQL functions: `GROUP_CONCAT`, `FIND_IN_SET`, `FIELD()`
**Do not:** Add SQLite or MySQL fallback paths in migrations.

---

## ADR-006: Queue-First for Heavy Operations

**Decision:** PDF generation, email sending, AI analysis, Excel processing, and all external integrations (LOGO, Python) are always executed via queued Jobs.
**Rationale:** These operations exceed HTTP timeout limits (typically 30s). Synchronous execution degrades UX and causes timeouts under load.
**Queue backend:** Redis
**Job pattern:** `app/{Module}/Jobs/{Action}{Subject}Job.php`
**Dispatch pattern:** `ProcessDeliveryImportJob::dispatch($batch)` in controllers
**Do not:** Call `LogoIntegrationService`, `PythonBridgeService`, AI services, or Excel processing synchronously in HTTP controllers.

---

## ADR-007: Laravel 12 Bootstrap Structure

**Decision:** Middleware registered in `bootstrap/app.php` using `Application::configure()->withMiddleware()`. Schedules in `routes/console.php`.
**Rationale:** Laravel 12 eliminated `app/Http/Kernel.php` and `app/Console/Kernel.php`. This project uses the new structure.
**Do not create:** `app/Http/Kernel.php` or `app/Console/Kernel.php` — these files no longer exist in Laravel 12.
**Middleware registration:** `bootstrap/app.php`
**Route files:** Registered in `bootstrap/app.php` `withRouting()` block
**Console schedules:** `routes/console.php` using `Schedule::call()` / `Schedule::command()`
