# Skill: Safe Migration Editing

Use whenever adding or modifying database migrations for this MSSQL project.

---

## Create Commands

```bash
# New table
php artisan make:migration --no-interaction create_{table_name}_table

# Add columns to existing table
php artisan make:migration --no-interaction add_{column}_to_{table}_table

# Modify existing column
php artisan make:migration --no-interaction change_{column}_on_{table}_table
```

---

## MSSQL Migration Template (new table)

```php
public function up(): void
{
    Schema::create('example_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained('companies');
        $table->foreignId('created_by')->nullable()->constrained('users');
        $table->string('name', 255);                   // nvarchar(255)
        $table->text('description')->nullable();        // nvarchar(max)
        $table->string('status', 20)->default('active');
        $table->decimal('amount', 12, 2)->nullable();
        $table->timestamps();                           // datetime2 created_at/updated_at
        $table->softDeletes();                          // datetime2 deleted_at (nullable)
    });
}

public function down(): void
{
    Schema::dropIfExists('example_items');
}
```

---

## Add Column to Existing Table

```php
public function up(): void
{
    Schema::table('delivery_import_batches', function (Blueprint $table) {
        // Always: nullable or default for existing rows
        $table->string('new_field', 50)->nullable()->after('invoice_status');
    });
}

public function down(): void
{
    Schema::table('delivery_import_batches', function (Blueprint $table) {
        $table->dropColumn('new_field');
    });
}
```

---

## Modify Existing Column — RE-DECLARE ALL ATTRIBUTES

```php
public function up(): void
{
    Schema::table('delivery_import_batches', function (Blueprint $table) {
        // Check the ORIGINAL migration first for all attributes
        // Then re-declare every single one:
        $table->string('invoice_status', 20)->nullable()->default('pending')->change();
        //                                    ↑ from original   ↑ from original
    });
}
```

---

## After Creating a Migration

1. Add new column to model `$fillable` array
2. Add cast to model `casts()` if needed (e.g., `'array'` for JSON fields)
3. Confirm with user before running: `php artisan migrate --no-interaction`
4. Update factory if model has one: add new field to factory definition
5. Update `docs/architecture/02-database-schema.md` table definition

---

## Column Addition Safety Rules

| Situation | Rule |
|---|---|
| Adding to populated table | Always `->nullable()` or `->default($value)` |
| Adding `->unique()` | First verify no duplicates exist in production data |
| Changing column type | Re-declare ALL attributes; consider data migration |
| Adding index | Use `->index()` in migration or separate `Schema::table` call |
| `row_data` column | NEVER change type or cast — breaks existing indexed positions |

---

## Index Migration Pattern

```php
// Add index on frequently filtered column
Schema::table('delivery_report_rows', function (Blueprint $table) {
    $table->index('delivery_import_batch_id', 'drr_batch_id_index');
});
```
