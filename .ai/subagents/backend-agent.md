# Subagent: Backend Agent

**Domain:** Laravel business logic — Controllers, Services, Jobs, Models, Form Requests, Policies
**Activates when:** Adding/modifying PHP backend code, creating new features, fixing logic bugs

---

## Scope

**Owns:**
- `app/{Module}/Controllers/`
- `app/{Module}/Services/`
- `app/{Module}/Jobs/`
- `app/Models/`
- `app/{Module}/Requests/`
- `app/{Module}/Policies/`
- `routes/*.php`

**Does not own:**
- Database schema → database-agent
- Blade views → ui-agent
- Documentation → docs-agent

---

## Operating Rules

1. Load `.ai/project-map.md` module table before starting — identify exact module being touched
2. Load `.ai/rules/core-rules.md` — apply all non-negotiable constraints
3. Load `.ai/skills/laravel-refactor.md` for any refactor work
4. Every new service method needs a test in `tests/Feature/`
5. Controllers receive requests, call services, return views/responses — no business logic in controllers
6. All Form Requests must include custom error messages in Turkish
7. Job dispatch: `ProcessDeliveryImportJob::dispatch($batch)` — not `new ProcessDeliveryImportJob($batch)`

---

## Make Commands

```bash
# Controller (place in correct module path)
php artisan make:controller --no-interaction "Delivery/Controllers/Web/DeliveryImportController"

# Form Request
php artisan make:request --no-interaction "Delivery/StoreDeliveryImportRequest"

# Job
php artisan make:job --no-interaction "Delivery/ProcessDeliveryImportJob"

# Policy
php artisan make:policy --no-interaction --model=Order "Order/OrderPolicy"

# Service (generic class)
php artisan make:class --no-interaction "Delivery/Services/DeliveryReportImportService"
```

---

## Module Entry Points

| Goal | Start at |
|---|---|
| Delivery import logic | `DeliveryImportController::store()` → `ProcessDeliveryImportJob` → `DeliveryReportImportService` |
| Pivot report generation | `DeliveryImportController::show()` → `DeliveryReportPivotService::buildMaterialPivot()` |
| Order creation | `OrderController::store()` → `OrderService::create()` |
| Company switch | `CompanyController::switch()` → session update → `CompanyScope` auto-applies |
| AI analysis | `AIService` → `AIOperationsService` / `AIFinanceService` |
| LOGO integration | `SendToLogoJob` → `LogoIntegrationService` |
| Permission check | `PermissionMiddleware` → custom_permissions + custom_role_user tables |

---

## Standard Controller Structure

```php
class DeliveryImportController extends Controller
{
    public function __construct(
        private readonly DeliveryReportImportService $importService,
        private readonly DeliveryReportPivotService $pivotService,
    ) {}

    public function index(Request $request): View
    {
        $batches = DeliveryImportBatch::query()
            ->with(['importer'])
            ->withCount('reportRows')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.delivery-imports.index', compact('batches'));
    }
}
```
