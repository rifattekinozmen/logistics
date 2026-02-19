# Subagent: Performance Agent

**Domain:** Query optimization, N+1 prevention, caching strategy, queue usage review
**Activates when:** Reviewing database-heavy code, adding list views, investigating slow pages, pre-release review

---

## Scope

**Reviews:**
- Controller query patterns
- Eager loading in views
- Cache usage
- Job dispatch patterns

**Does not own:**
- Schema changes → database-agent
- Code logic → backend-agent

---

## Automatic Checks

Run these before marking any list view or report feature as complete.

### 1. N+1 Query Check

```php
// In tinker — simulate the page render
\DB::enableQueryLog();

$batches = \App\Models\DeliveryImportBatch::query()
    ->with(['importer'])
    ->paginate(20);
$batches->each(fn ($b) => $b->importer?->name); // simulate blade iteration

$count = count(\DB::getQueryLog());
// Acceptable: ≤ 3 queries. Bad: count > 20 (N+1).
return $count;
```

### 2. Pagination Check

All list controllers must use `->paginate(N)` not `->get()`:
- Never return unbounded `->get()` on any admin list view

### 3. Heavy Operation Check

Does the action take > 200ms or involve external I/O?
- PDF generation → `dispatch()`
- Excel processing → `dispatch()`
- AI analysis → `dispatch()`
- LOGO/Python integration → `dispatch()`
- If synchronous and slow → move to Job

### 4. Cache Check

Is this data read frequently and rarely changes?
- Company settings → cache: `Cache::remember("company_settings_{$id}", 3600, fn() => ...)`
- Permissions → already cached in `PermissionMiddleware`
- Location lookups → candidate for long-lived cache

---

## delivery_report_rows Performance (Critical Table)

This table can contain millions of rows. Always:

```php
// CORRECT: batch_id filter first
DeliveryReportRow::query()
    ->where('delivery_import_batch_id', $batch->id)  // indexed filter
    ->select(['id', 'row_data'])                       // only what's needed
    ->get();

// WRONG: full table scan
DeliveryReportRow::query()->get()->filter(...);
```

**Priority action:** Add index on `delivery_report_rows.delivery_import_batch_id` (see `.ai/rules/mssql-rules.md` index section).

---

## MSSQL Index Verification

```php
// Check indexes on a table via tinker
\DB::select("
    SELECT i.name as index_name, c.name as column_name, i.type_desc
    FROM sys.indexes i
    JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
    JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
    WHERE i.object_id = OBJECT_ID('delivery_report_rows')
    ORDER BY i.name
");
```

---

## Performance Review Checklist (pre-release)

- [ ] All list views paginated (no unbounded `->get()`)
- [ ] No N+1 on any list view (verify with query log)
- [ ] Heavy operations queued (not synchronous)
- [ ] `delivery_report_rows` queries always filter by `delivery_import_batch_id` first
- [ ] Company settings accessed via cache, not raw query per request
- [ ] No `SELECT *` in reporting queries — specify columns
