# Skill: Performance Check

Use before finalizing any feature involving database queries or list views.

---

## N+1 Detection via Tinker

```php
// Enable query logging
\DB::enableQueryLog();

// Simulate the controller action
$batches = \App\Models\DeliveryImportBatch::query()
    ->with(['importer'])
    ->paginate(20);

// Simulate Blade view iteration
$batches->each(fn ($b) => $b->importer?->name);

$log = \DB::getQueryLog();
// Acceptable: 2-3 queries (paginate + eager loads)
// Bad: 20+ queries (N+1)
return count($log);
```

---

## Eager Loading Checklist

For each controller returning a collection, verify:

- [ ] All relationship calls in Blade are eager-loaded with `with()`
- [ ] Counts use `withCount()` instead of `->count()` in loops
- [ ] No `->load()` calls inside foreach loops

### Required Eager Loads per Module

| Controller | Required |
|---|---|
| `DeliveryImportController@index` | `->with(['importer'])->withCount('reportRows')` |
| `DeliveryImportController@show` | `->with(['reportRows'])` |
| `OrderController@index` | `->with(['customer', 'shipments'])` |
| `VehicleController@index` | `->with(['inspections'])` |
| `EmployeeController@index` | `->with(['department', 'position'])` |
| `PaymentController@index` | `->with(['relatable'])` (polymorphic) |
| `ShipmentController@index` | `->with(['order', 'vehicle', 'driver'])` |

---

## Pagination Standard

| Context | Page size |
|---|---|
| Admin list views | 20 |
| Delivery import list | 10 or 20 (user-selectable, already implemented) |
| API endpoints | 15 |

**Never use `->get()` on a list controller** — always `->paginate(N)`.

---

## Cache Opportunities

| Data | Cache Strategy |
|---|---|
| Company settings | `Cache::remember("company_settings_{$companyId}", 3600, ...)` |
| Permission checks | Already cached in `PermissionMiddleware` (Redis) |
| Location data (countries/cities) | Long-lived cache, clear on seeder re-run |
| Report config types | `config()` helper is cached after `php artisan config:cache` |

---

## Queue Threshold

Is this operation likely > 200ms or involves external I/O?

| Operation | Async? |
|---|---|
| Excel file processing | YES → `ProcessExcelJob::dispatch($file)` |
| AI analysis generation | YES → `RunAIAnalysisJob::dispatch($params)` |
| LOGO ERP export | YES → `SendToLogoJob::dispatch($batch)` |
| Python bridge call | YES → `SendToPythonJob::dispatch($data)` |
| Email notifications | YES → queue |
| Simple DB read | NO → synchronous |
| CRUD operations | NO → synchronous |

---

## delivery_report_rows Performance Pattern

Table can contain millions of rows. Always:

```php
// CORRECT: filter by batch first (uses index)
DeliveryReportRow::query()
    ->where('delivery_import_batch_id', $batch->id)  // index filter first
    ->select(['id', 'row_data'])                       // only needed columns
    ->get()
    ->groupBy(fn ($r) => $r->row_data[$dateIndex] ?? '');

// WRONG: no batch filter = full table scan
DeliveryReportRow::query()->get()->filter(...);
```

---

## MSSQL Index Check

Verify an index exists before using a column in `WHERE` frequently:

```php
// Via tinker (read-only check)
\DB::select("
    SELECT i.name, c.name as column_name
    FROM sys.indexes i
    JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
    JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
    WHERE i.object_id = OBJECT_ID('delivery_report_rows')
");
```
