# Skill: Refactor Without Breaking

Use for any refactor touching more than one file, or any change to a core subsystem.

---

## Impact Analysis Protocol

Before touching any class or method:

### Step 1: Find all usages
```bash
# Find all PHP usages
grep -r "DeliveryReportPivotService" app/ --include="*.php" -l
grep -r "buildMaterialPivot" app/ --include="*.php"

# Find all Blade usages
grep -r "invoice_status" resources/views/ --include="*.blade.php"
```

### Step 2: Map the dependency chain
Example for DeliveryReportPivotService:
```
DeliveryImportController::show()
  → DeliveryReportPivotService::buildMaterialPivot($batch, $reportType)
    → config('delivery_report.report_types.{type}.material_pivot')
      → DeliveryReportRow::row_data[{index}]
        → delivery_report_rows table (nvarchar(max) JSON)
```

### Step 3: Check test coverage
```bash
php artisan test --compact --filter=Delivery
# If no tests: write one before refactoring
```

---

## Safe Refactor Steps

1. Write failing test for intended new behavior
2. Make the smallest change that makes the test pass
3. Run module tests: `php artisan test --compact --filter={Module}`
4. Run `vendor/bin/pint --dirty`
5. Run all tests: `php artisan test --compact` — verify nothing broken
6. Update `.ai/session.md`

---

## Refactor Red Lines

These require a session-level plan in `.ai/decisions/architecture.md` before touching:

| Component | Why it's dangerous |
|---|---|
| `CompanyScope` | Global scope — affects ALL scoped queries across every module |
| `config/delivery_report.php` header arrays | Integer positions map to `row_data` in DB — reordering corrupts stored data |
| `DeliveryReportPivotService::buildMaterialPivot()` | Core business logic; client-visible pivot output |
| `PermissionMiddleware` / `RoleMiddleware` | Security boundary — any bug = unauthorized access |
| `app/Models/` base model changes | Affects all 47 models |
| Any model `casts()` for JSON columns | Type change = data corruption on read |

---

## Rollback Strategy

- **Git:** Always work on a feature branch (`git checkout -b feature/refactor-x`), not directly on main
- **Migrations:** Every `up()` must have a working `down()`
- **Config:** If removing config keys, commit old values in a comment or keep the key with a `deprecated` note until all code is updated
- **No squashing mid-refactor:** Keep granular commits so each step is independently revertable

---

## Incremental Refactor Pattern

For large refactors (e.g., extracting a service from a controller):

```
Commit 1: Add new service with extracted logic (tests pass)
Commit 2: Update controller to use new service (old logic still present as fallback)
Commit 3: Remove old logic from controller (tests still pass)
Commit 4: pint + final test run
```

Never do all steps in one commit — makes debugging impossible.
