# MSSQL Safety Rules

All database work targets MS SQL Server (sqlsrv driver). These rules prevent silent failures and data corruption.

---

## Migration Rules

| Rule | Correct | Wrong |
|---|---|---|
| No unsigned integers | `$table->bigInteger('user_id')` | `$table->unsignedBigInteger('user_id')` |
| No unsigned modifier | `$table->integer('count')` | `$table->integer('count')->unsigned()` |
| Unicode text (Turkish chars) | `$table->string('name')` (= nvarchar) | `$table->char('name')` (may be varchar) |
| Long text | `$table->text('notes')` (= nvarchar(max)) | `$table->longText()` (MySQL specific) |
| Timestamps | `$table->timestamps()` (= datetime2) | Manual TIMESTAMP columns |
| JSON data | `$table->text('row_data')` + array cast | `$table->json('row_data')` |
| Soft deletes | `$table->softDeletes()` on all main tables | Missing deleted_at |

---

## Column Modification Rule — CRITICAL

When modifying an existing column, you must re-specify **ALL** previously defined attributes. MSSQL drops unspecified attributes silently.

```php
// WRONG — loses nullable, default
$table->string('invoice_status', 20)->change();

// CORRECT — preserves all attributes
$table->string('invoice_status', 20)->nullable()->default('pending')->change();
```

**Before modifying a column:**
1. Read the original migration to find all attributes
2. Include every attribute in the `->change()` call

---

## Query Rules

### Do not use MySQL-specific functions:
- `GROUP_CONCAT()` → collect in PHP: `$collection->pluck('field')->implode(', ')`
- `FIND_IN_SET()` → use `whereIn()` with an array
- `FIELD()` for ordering → use `orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$id])`
- `JSON_CONTAINS()` → filter in PHP collection (MSSQL JSON support is limited)

### MSSQL equivalents:
- String aggregation: `STRING_AGG(column, ',')` (MSSQL 2017+) — but prefer PHP collection
- Top N rows: Laravel `->take(N)` or `->limit(N)` (generates `TOP` or `OFFSET FETCH`)
- Pagination: `->paginate(N)` — Laravel handles `OFFSET/FETCH NEXT` for MSSQL automatically

### Safe `LIKE` search:
```php
->where('company_name', 'like', '%' . $term . '%')  // works in MSSQL
```

---

## Turkish Character Handling

- All user-visible string columns use `nvarchar` (Laravel's `->string()` defaults to this with MSSQL)
- `config/delivery_report.php` headers contain Turkish characters (ş, ğ, ü, ö, ı, ç) — never convert to ASCII
- When comparing Excel headers: normalize whitespace but preserve UTF-8 encoding

---

## DeliveryReportRow row_data — Special Warning

```
delivery_report_rows.row_data (nvarchar(max), cast to array)
```

Positions in `row_data` array are integer indices that **directly map to** the `headers` array in `config/delivery_report.php` for each report type.

**Example mapping (endustriyel_hammadde):**
- `row_data[0]` = headers[0] (e.g., tarih / date)
- `row_data[4]` = headers[4] (e.g., malzeme kodu / material code)
- etc.

**Rule:** Changing the order of headers in `config/delivery_report.php` invalidates all existing stored rows for that report type. Never reorder without a data migration.

---

## Model JSON Cast Pattern

```php
// In model casts() method:
protected function casts(): array
{
    return [
        'row_data'       => 'array',   // nvarchar(max) stored as JSON string
        'import_errors'  => 'array',   // nvarchar(max) stored as JSON string
    ];
}
```

---

## New Column on Populated Table

When adding a column to a table that already has data in production:
- Always add `->nullable()` OR `->default($value)`
- Never add `->unique()` without first verifying no duplicates exist

```php
// Safe pattern for adding to existing table:
$table->string('new_field', 50)->nullable()->after('existing_field');
```

---

## Foreign Keys

MSSQL supports foreign key constraints. Prefer using them:
```php
$table->foreignId('company_id')->constrained('companies');
// Note: foreignId() creates a bigInteger (BIGINT), no unsigned
```

For nullable foreign keys:
```php
$table->foreignId('user_id')->nullable()->constrained('users');
```

---

## Index Recommendations

Tables with large row counts that need indexes:
- `delivery_report_rows`: index on `delivery_import_batch_id` (HIGH PRIORITY — large table)
- `audit_logs`: index on `subject_type`, `subject_id`, `created_at`
- `orders`: index on `company_id`, `status`, `created_at`
- `payments`: index on `company_id`, `due_date`, `status`
