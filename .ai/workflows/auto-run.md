# Auto-Run vs Confirm Workflow

Defines when agents execute immediately vs pause for user confirmation.

---

## Decision Logic

```
Is it DROP / TRUNCATE / migrate:fresh?  → HARD STOP (never, even with permission)
Is it schema-changing (migrate, fillable, casts)? → CONFIRM FIRST
Is it config-changing (config/, bootstrap/, routes/)? → CONFIRM FIRST
Is it auth/permissions-changing?        → CONFIRM FIRST
Is it read-only or purely additive?     → AUTO-RUN
```

---

## Auto-Run (No Confirmation Needed)

These are safe to execute without asking:

**Read operations:**
- Reading any file in the project
- `php artisan route:list` — read-only
- `php artisan migrate:status` — read-only
- `php artisan test --compact --filter=X` — tests run in test DB
- `database-query` tool (SELECT only)
- Tinker read-only inspection (`Model::query()->count()`, `->first()`)

**Safe writes (additive only):**
- Creating new files in `app/`, `tests/`, `resources/views/` (new files, not modifying existing)
- Creating a new migration file (not running it)
- `vendor/bin/pint --dirty` (formatter — no logic changes)
- `search-docs` tool queries
- `php artisan make:*` scaffolding commands
- Adding new Blade components
- Adding new test files

---

## Require Confirmation Before Executing

Pause, show the proposed change, wait for explicit "yes":

**Schema operations:**
- `php artisan migrate` — modifies DB schema
- `php artisan migrate:rollback` — reverses migrations
- `php artisan db:seed` or `php artisan db:seed --class=X`

**Code modifications to critical files:**
- Any change to `app/Models/*.php` (especially `$fillable`, `casts()`, global scopes)
- Any change to `app/Core/` (CompanyScope, middleware, global services)
- Any change to `config/delivery_report.php` (pivot calculations — affects production output)
- Any change to `bootstrap/app.php` (middleware/exception registration)
- Any change to `routes/*.php`
- Modifying existing test files
- Any change to authentication/authorization logic

**Sensitive operations:**
- Deleting or renaming any existing file
- Any npm package install/removal
- Any composer package install/removal

---

## Hard Stops (Never Execute, Even With Explicit Permission)

These require the user to run them manually:

- `php artisan migrate:fresh` or `migrate:fresh --seed`
- `DROP TABLE` or `TRUNCATE TABLE` SQL commands
- Removing pivot dimensions/metrics from `config/delivery_report.php` (data loss risk)
- Changing `CompanyScope` logic without full cross-module impact analysis
- Any destructive git operations (`reset --hard`, `push --force` to main)
- Modifications to: `AGENTS.md`, `CLAUDE.md`, `.cursor/`, `.mcp.json`, `boost.json`, `.ai/boost/`, `.ai/boost-main/`

---

## Module-Specific Triggers

| Module | Auto-run safe | Requires confirm |
|---|---|---|
| Delivery | Add new pivot helper method | Change config/delivery_report.php |
| Finance | Add new payment status badge | Change payment calculation logic |
| HR | Add new attendance view | Modify payroll calculation |
| Auth | — | Any change |
| Company/Tenant | — | Any change to CompanyScope |
| Migrations | Create file | Run `php artisan migrate` |
