# UMT Studio — Deferred Items

---

## Custom Date Entry UI

**Status:** Deferred post-presentation. ACF date pickers remain in place as a placeholder.

**Problem:** ACF date pickers are slow for data entry, cannot represent partial dates (year-only, year+month), and do not support date qualifiers or ranges. The stored format `Ymd` with `0101` as a year-only convention is a workaround, not a data model.

**Intended UI (works edit screen):**

A custom date section replacing ACF date pickers for `date_earliest` and `date_latest`:

- **Year** (required minimum) — 4-digit integer input
- **Month** (optional) — select 1–12 or blank
- **Day** (optional, requires Month) — integer 1–31 or blank
- **Qualifier** (optional) — controlled vocabulary: `ca.`, `before`, `after`, `probably`, `attributed to` — full list TBD, reference EDTF and VRA Core 4.0
- **Range toggle** — hidden by default, reveals a second identical Year/Month/Day set for `date_latest`

`date_display` derived on save from qualifier + formatted date components. Not shown to user.

**Storage:** `date_earliest` and `date_latest` remain `Ymd` VARCHAR. Year-only: `YYYY0101`. Year+month: `YYYYMM01`. Full date: `YYYYMMDD`.

**Reference standards:**
- EDTF — Extended Date/Time Format, ISO 8601-2 (used by Wikidata, Library of Congress)
- VRA Core 4.0 date element
- CDWA date/time of creation

**Implementation path:** custom metabox or ACF Pro custom field type. ACF free cannot handle conditional field logic (Day requires Month) without JS hacks.

**Related:** `assets/js/admin-fields.js` has a stub comment marking where date sync logic will live.

---

## Agent Role Model

ACF Pro Repeater required. Current `agents_artists` / `agents_authors` parallel fields are a Piroir-specific hack.

Target: single `agents` repeater with sub-fields `agent` (relationship) + `role` (select from `umtd_agent_role` taxonomy).

---

## Work–Agent Junction Table

FRBR-correct normalization target: `work_id | agent_id | role_id`. Deferred until after ACF Pro Repeater migration and scale justifies it.

---

## Auto-populate Agent Fields from ULAN / Wikidata

Admin JS + PHP AJAX + Wikidata and Getty ULAN APIs. On agent edit screen, user searches by name across: existing agents (duplicate check), Getty ULAN, Wikidata, Wikipedia. Selecting a result auto-populates available fields. Estimated 4–6 hours.

---

## Config-driven Schema.org Engine

One engine reads a config array, outputs JSON-LD for all CPTs. Currently only `umtd_works` has schema output (`includes/schema.php`). Schema for `umtd_events` and `umtd_agents` not written. Sitewide Organization schema not written.

---

## Polylang — FR/EN

Install and configure before production data entry. Piroir requirement — bilingual Montreal gallery. Deferred past presentation.

---

## Helper Function — Agent Display Name

`umtd_get_agent_display_name( $id )` wrapping `get_field('name_display', $id)` with a `get_the_title()` fallback. Centralizes the pattern used across `card-work.php`, `card-agent.php`, `single-umtd_works.php`, `single-umtd_agents.php`.

---

## Child Theme — umt-design-piroir

Not started. Client typography, colour, layout, branding.

---

## Prints / Books / Artists Page Templates — nav_menu

Nav menus registered in `functions.php`. Menus need to be created and assigned in Appearance → Menus after deployment.

---

## REST API

Not started.

---

## Class-based Plugin Architecture

Deferred. Current procedural structure is acceptable at this scale.

---

## Search / Filtering UI

Deferred.

---

## Staging Environment

EC2 instance mirroring production. Required before production launch.

---

## CI/CD Pipeline

GitHub Actions → SSH → EC2 `git pull`. Not configured.

---

## Uninstall Hook

Plugin leaves orphaned CPT data and taxonomy terms on removal.

---

## `wp i18n make-pot`

Not generated. Required before any translation work.

---

## Lazy-load JS

`data-lazy` attribute stubbed on archive grids. No JS written.

---

## Caching

Irrelevant at Piroir scale. Revisit if traffic warrants.

## WP Admin Menu Placement

`show_in_menu` on CPTs controls sidebar placement in the WP admin. Currently unset — CPTs appear at the top level. Requires deciding on a menu structure and slug convention before implementing.

## PHP/JS Style Guide

No formal style guide defined. Current conventions in use:
- Indentation: tabs
- PHP arrays: `array()` not `[]` (schema.php uses `[]` — inconsistent, should be normalized)
- ACF field access: `get_field()` never `get_the_title()` for agents
- Strict comparison: `===` throughout, `in_array( $val, $arr, true )`

Recommend adopting WordPress Coding Standards formally:
https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/

Consider adding a `.editorconfig` and optionally `phpcs.xml` with `WordPress-Core` ruleset for automated enforcement.
