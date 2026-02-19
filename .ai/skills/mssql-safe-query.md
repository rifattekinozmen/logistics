# Skill: MSSQL Safe Query

Use when writing or reviewing database queries in this MSSQL project.

---

## Standard Paginated List (admin controller pattern)

```php
$query = DeliveryImportBatch::query()->latest();

if ($request->filled('status')) {
    $query->where('invoice_status', $request->string('status')->trim()->value());
}

if ($request->filled('search')) {
    $query->where('file_name', 'like', '%' . $request->string('search')->trim()->value() . '%');
}

$batches = $query->with(['importer'])->paginate(20)->withQueryString();
```

---

## JSON Column Query (row_data)

`row_data` is `nvarchar(max)` stored as JSON, cast to array. Cannot use `JSON_CONTAINS` (MySQL). Filter in PHP:

```php
$rows = DeliveryReportRow::query()
    ->where('delivery_import_batch_id', $batchId)
    ->select(['id', 'row_data'])
    ->get()
    ->filter(fn ($row) => ($row->row_data[$materialIndex] ?? '') === $materialCode);
// $materialIndex comes from config('delivery_report.report_types.{type}.material_pivot.material_index')
```

---

## Aggregation Without GROUP_CONCAT

```php
// Collect in PHP (preferred — avoids MySQL-specific SQL)
$materialSummary = DeliveryReportRow::query()
    ->where('delivery_import_batch_id', $batchId)
    ->get()
    ->groupBy(fn ($row) => $row->row_data[$dateIndex] ?? 'unknown')
    ->map(fn ($group) => $group->sum(fn ($row) => (float) str_replace(',', '.', str_replace('.', '', $row->row_data[$weightIndex] ?? '0'))));
```

---

## Column Modification Template

Always include ALL previously defined attributes when using `->change()`:

```php
Schema::table('delivery_import_batches', function (Blueprint $table) {
    // Declare every attribute that was set originally — MSSQL drops omitted ones
    $table->string('invoice_status', 20)->nullable()->default('pending')->change();
});
```

---

## Soft Delete Aware Queries

```php
// Normal (excludes soft-deleted automatically)
DeliveryImportBatch::query()->where('status', 'completed')->get();

// Include soft-deleted
DeliveryImportBatch::withTrashed()->where('id', $id)->first();

// Only soft-deleted
DeliveryImportBatch::onlyTrashed()->get();
```

---

## Foreign Key with Nullable

```php
// Nullable FK on existing table — add both nullable and constrained
$table->foreignId('approved_by')->nullable()->constrained('users');
```

---

## Pre-Query Safety Checklist

- [ ] Filtering by `company_id`? — CompanyScope handles this automatically; no manual where needed
- [ ] Cross-company query? — Add `->withoutGlobalScope(CompanyScope::class)`
- [ ] Touching `row_data`? — Read config indices before hardcoding positions
- [ ] Adding column to populated table? — Must add `->nullable()` or `->default()`
- [ ] Modifying column? — Must re-declare ALL original attributes
