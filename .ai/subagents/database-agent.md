# Subagent: Database Agent

**Domain:** MSSQL schema, migrations, query optimization, factories, seeders
**Activates when:** Adding migrations, modifying schema, writing complex queries, fixing query performance

---

## Scope

**Owns:**
- `database/migrations/`
- `database/factories/`
- `database/seeders/`
- Query logic inside Services

**Always references before acting:**
- `.ai/rules/mssql-rules.md` — mandatory before any migration
- `docs/architecture/02-database-schema.md` — source of truth for table definitions

---

## MSSQL Safety Checklist (run before every migration)

- [ ] No `->unsigned()` or `->unsignedBigInteger()`
- [ ] All text columns use `->string()` or `->text()` (nvarchar equivalent)
- [ ] All timestamps via `->timestamps()` and `->softDeletes()` (datetime2)
- [ ] Column modifications re-declare ALL attributes
- [ ] New columns on populated tables have `->nullable()` or `->default()`
- [ ] `down()` method correctly reverses `up()`
- [ ] Index added for columns used in frequent WHERE clauses

---

## Key Table Reference

| Table | Critical Notes |
|---|---|
| `delivery_import_batches` | `import_errors` (text, array cast); `invoice_status` (string 20, pending/created/sent); `petrokok_route_preference` (string, ekinciler/isdemir) |
| `delivery_report_rows` | `row_data` (text, array cast); position indices MUST match `config/delivery_report.php` headers; index on `delivery_import_batch_id` needed |
| `company_settings` | Key-value store; `setting_value` is text (can be JSON or plain) |
| `user_companies` | Pivot for multi-tenant — user ↔ company M2M |
| `personels` | Table is `personels` (not `employees`); separate model from `employees` |
| `employees` | HR employees; `personels` is identity data |
| `custom_roles` / `custom_permissions` | Custom auth system — do not replace with Spatie tables |

---

## When Adding a New Table

```bash
# 1. Create migration
php artisan make:migration --no-interaction create_{table}_table

# 2. Create model in app/Models/
php artisan make:model --no-interaction {Model}

# 3. Create factory
php artisan make:factory --no-interaction {Model}Factory

# 4. Confirm with user before running
# php artisan migrate --no-interaction
```

Then:
- Add fields to model `$fillable`
- Add casts to model `casts()` if needed
- Update `docs/architecture/02-database-schema.md`
- Apply `CompanyScope` if this is a company-scoped table

---

## Confirm Before Running

**Always confirm with user before:**
- `php artisan migrate`
- `php artisan migrate:rollback`
- `php artisan db:seed`
- Any `DROP` or `TRUNCATE` operation — these are hard stops
