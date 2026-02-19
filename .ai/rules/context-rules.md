# Context and Token Optimization Rules

Rules for managing context window efficiently during AI-assisted development.

---

## Session Startup Protocol

At the start of each session, load in this order:
1. `.ai/session.md` — current state, what was last worked on, pending items
2. `.ai/project-map.md` — architecture reference (models, controllers, routes)
3. The specific module files being worked on (controller + service + model + view + test)

Do **not** load the entire codebase. Load targeted files only.

---

## File Loading Priority

### Always load when relevant:
- The specific controller being modified
- The model(s) it touches
- The service(s) it calls
- The Blade view it renders
- The related test file

### Load only if needed:
- `config/delivery_report.php` — only for Delivery module work
- `routes/admin.php` — only when adding/modifying routes
- `bootstrap/app.php` — only for middleware changes
- `database/migrations/` — only when reviewing schema history

### Never load speculatively:
- All 47 models simultaneously
- All 70 migrations simultaneously
- `node_modules/`, `vendor/`
- `.ai/boost-main/` (Laravel Boost internals)

---

## Context Compression When Summarizing

When writing session notes or passing context to another agent:
- Use module table format (Module | File | Status) not full code blocks
- Reference class/method names, not full implementations
- Reference config keys by name (`material_pivot.date_index`) not by value
- Reference ADR numbers instead of re-explaining decisions

---

## Module Isolation Rule

When working on a module:
- Stay within that module's files
- Cross-module communication goes through Models or Services — never direct controller-to-controller
- If a change requires touching more than 3 modules: pause, check `.ai/decisions/architecture.md`

---

## Docs Sync Trigger

Update documentation when:

| Event | Document to update |
|---|---|
| New migration added | `docs/architecture/02-database-schema.md` |
| New module or controller | `docs/modules/04-modules-documentation.md` |
| New route group | `docs/architecture/01-project-overview.md` |
| Architecture decision made | `.ai/decisions/architecture.md` |
| Feature completed | `.ai/session.md` module status table |
| Significant session end | Create `docs/sessions/YYYY-MM-DD-{topic}.md` |

---

## Search Before Assuming

Before creating any new class or file:
1. Grep for existing similar files: `grep -r "ServiceName" app/ --include="*.php" -l`
2. Check `.ai/project-map.md` module table for existing controllers/services
3. Check `config/delivery_report.php` before adding delivery logic

Before writing any query:
1. Check if an Eloquent scope already exists on the model
2. Check if the service already has a similar method

---

## Minimal Context Pattern

When asked "how does X work?":
1. Read the specific controller method
2. Read the service method it calls
3. Read only the relevant model relationships
4. Do NOT read the entire controller or all model files

When asked "add feature to module X":
1. Load controller → find insertion point
2. Load/create service method
3. Load model for relationship info
4. Load test file for test addition
5. That is sufficient context — do not load unrelated modules
