# Skill: Laravel Safe Refactor

Use when refactoring existing Laravel code in this project.

---

## Pre-Refactor Checklist

1. Grep all callers of the method/class being changed
2. Check if a test exists — if not, write one first
3. Load `.ai/decisions/architecture.md` — verify no ADR is violated
4. Confirm the refactor does not unexpectedly cross module boundaries

---

## Common Refactor Patterns

### Extract Business Logic to Service

Controller has logic beyond request/response? Move to service:

```php
// Before (in controller — wrong)
$batch = DeliveryImportBatch::query()->findOrFail($id);
$batch->update(['invoice_status' => 'created']);
LogoIntegrationService::dispatch($batch);

// After (in controller — correct)
$this->deliveryReportImportService->markInvoiceCreated($batch);

// In DeliveryReportImportService (correct)
public function markInvoiceCreated(DeliveryImportBatch $batch): void
{
    $batch->update(['invoice_status' => 'created']);
    SendToLogoJob::dispatch($batch);
}
```

### Add Form Request (replace inline validation)

```bash
php artisan make:request --no-interaction Delivery/UpdateInvoiceStatusRequest
```

```php
// Form Request
public function rules(): array
{
    return [
        'invoice_status' => ['required', 'in:pending,created,sent'],
    ];
}

public function messages(): array
{
    return [
        'invoice_status.in' => 'Geçersiz fatura durumu.',
    ];
}
```

### Fix N+1 — Add Eager Loading

```php
// Find the N+1: grep for relationship calls used in loops
// grep -r "->reportRows" resources/views/ --include="*.blade.php"

// Fix at query origin in controller:
$batches = DeliveryImportBatch::query()
    ->with(['importer'])
    ->withCount('reportRows')
    ->latest()
    ->paginate(20);
```

### Replace DB:: with Eloquent

```php
// Wrong
DB::table('delivery_import_batches')->where('id', $id)->first();

// Correct
DeliveryImportBatch::query()->findOrFail($id);
```

---

## Post-Refactor Checklist

1. Run `php artisan test --compact --filter={AffectedModule}`
2. Run `vendor/bin/pint --dirty`
3. Verify eager loading with tinker: `DeliveryImportBatch::with('reportRows')->take(1)->get()`
4. Update `.ai/session.md` with what changed
