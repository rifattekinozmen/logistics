# Core Rules

These rules apply to all agents in all contexts. Non-negotiable.

---

## Identity

This is a **Laravel 12 / PHP 8.2 / MSSQL** logistics ERP.
Domain-oriented architecture. Blade + Bootstrap 5 + Tailwind CSS v4 frontend. Pest v3 tests.
Multi-tenant (CompanyScope). Custom role/permission system.

---

## Protected Files — Never Touch

```
AGENTS.md
CLAUDE.md
.cursor/
.mcp.json
boost.json
.ai/boost/
.ai/boost-main/
```

---

## Non-Negotiable Constraints

1. Every code change requires a test update or new test. Run `php artisan test --compact` before declaring done.
2. Run `vendor/bin/pint --dirty` before finalizing any PHP change.
3. Never use `->unsigned()` or `->unsignedBigInteger()` in migrations — MSSQL does not support unsigned.
4. Never use `env()` outside `config/*.php` files — use `config('key.path')`.
5. Never create `app/Http/Kernel.php` or `app/Console/Kernel.php` — Laravel 12 uses `bootstrap/app.php`.
6. All new models go in `app/Models/` (flat, never per-domain).
7. All migrations must be MSSQL-compatible.
8. Never call heavy operations (PDF, email, AI, Excel) synchronously in controllers — always `dispatch()`.
9. Always use `php artisan make:` commands to scaffold new files.
10. Never inline `$request->validate()` in controllers — always use Form Request classes.

---

## Code Style

- PHP 8 constructor property promotion: `public function __construct(public OrderService $orderService) {}`
- Explicit return type on all methods: `public function index(): View`
- PHPDoc blocks preferred over inline comments
- `Model::query()` preferred over `DB::` raw queries
- Eager loading (`with()`) mandatory — no lazy loading in loops
- Named routes in Blade: `route('admin.orders.index')` not `/admin/orders`

---

## Naming Conventions

| Type | Pattern | Example |
|---|---|---|
| Controller | `{Module}Controller` | `DeliveryImportController` |
| Service | `{Module}Service` or `{Action}{Subject}Service` | `DeliveryReportPivotService` |
| Job | `{Action}{Subject}Job` | `ProcessDeliveryImportJob` |
| Form Request | `{Action}{Subject}Request` | `StoreOrderRequest` |
| Migration | `create_{table}_table` / `add_{col}_to_{table}_table` | `add_invoice_status_to_delivery_import_batches_table` |
| Enum keys | TitleCase | `InvoiceStatus::Pending`, `InvoiceStatus::Created` |
| Variables/Methods | Descriptive | `$isInvoiceCreated`, `$hasActiveShipments` |

---

## Architecture Boundaries

- Controllers: receive request → call service → return view/response. No business logic.
- Services: all business logic lives here.
- Models: relationships, casts, scopes only. No business logic.
- Jobs: wrap service calls for async execution.
- Policies: authorization checks only.

---

## Dependency Management

- Do not add new Composer packages without user approval.
- Do not add new npm packages without user approval.
- Check `composer.json` before assuming a package is available.
