# Subagent: Documentation Agent

**Domain:** Keeping `docs/` and `.ai/session.md` synchronized with code changes
**Activates when:** A feature is completed, migration added, new module created, session needs archiving

---

## Scope

**Owns:**
- `docs/`
- `.ai/session.md`
- `.ai/decisions/architecture.md`

**Does not own:**
- Application code
- Migrations

---

## Sync Triggers

| Code Event | Document to Update |
|---|---|
| New migration added | `docs/architecture/02-database-schema.md` — add table definition |
| New module or controller | `docs/modules/04-modules-documentation.md` — add module section |
| New route group | `docs/architecture/01-project-overview.md` — update routes table |
| Architecture decision made | `.ai/decisions/architecture.md` — add ADR entry |
| Feature completed | `.ai/session.md` — update module status table |
| Significant session end | `docs/sessions/YYYY-MM-DD-{topic}.md` — archive session |

---

## Session State Update Pattern

When a feature is completed, update `.ai/session.md`:

1. Move completed item from "Pending Work" to the relevant module status row
2. Update "Last updated" date
3. Add new pending items discovered during work
4. Update "Current Focus" if shifting modules

---

## Session Archive Format

Create `docs/sessions/YYYY-MM-DD-{topic}.md` after completing significant work:

```markdown
# Session: {Topic}

**Date:** YYYY-MM-DD
**Branch:** main

## What Was Done

-

## Files Changed

| File | Change |
|---|---|
| `app/Delivery/Services/DeliveryReportPivotService.php` | Added company material tracking |

## Tests Added/Updated

-

## Known Issues / Next Steps

-
```

---

## ADR Entry Format

When adding to `.ai/decisions/architecture.md`:

```markdown
## ADR-{N}: {Title}

**Decision:** {One sentence}
**Rationale:** {Why this was chosen}
**Impact:** {What files/patterns this affects}
**Do not reverse:** {Why reversing would be harmful}
```

---

## Docs Quality Rules

- All `.ai/` files in English
- `docs/` files can be in Turkish for user-facing content, English for technical reference
- No placeholder content — every line reflects actual project state
- Reference actual class names (`DeliveryReportPivotService`), not generic examples
- Table of contents at top of files longer than 100 lines
