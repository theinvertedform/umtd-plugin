# UMT Studio — Deferred Items

Items are grouped by dependency tier. Items within a tier are roughly independent of each other but depend on the tier above being resolved first.

---

## Tier 0 — Pre-Data-Import Blockers

These must be resolved before production data entry begins for any client. Importing into the current postmeta structure means doing the import twice.

### Agent Type as WordPress Taxonomy

`agent_type` is currently an ACF radio field stored as postmeta. ACF relationship fields can only filter by registered WordPress taxonomies — not postmeta values. This means the `location` field on Events cannot be filtered to show only organization agents in the ACF UI, and agent type is only queryable via `meta_query` rather than `tax_query`.

Registering `umtd_agent_type` as a proper taxonomy would fix both problems. Requires migrating existing `agent_type` postmeta values to taxonomy terms on all existing agent records — do before production data import.

### Custom DB Schema

**Status:** Post-contract. Currently all data lives in WordPress postmeta (ACF serialized arrays).

`meta_query` with `LIKE` on serialized postmeta does not scale. Work–Agent relationships cannot be queried efficiently. The translation model cannot be implemented cleanly on postmeta.

**Target:** Custom tables replacing postmeta for all CPT data, including a Work–Agent–Role junction table (`work_id | agent_id | role_id`) and a translations table (see Translation Model below). A `umtd_get_field()` abstraction layer insulates templates from storage changes.

This item supersedes the Work–Agent Junction Table (previously listed separately) — the junction table is part of the custom schema, not a standalone migration.

### Translation Model

**Status:** Deferred to custom DB schema milestone. URL routing is language-aware; content is not yet differentiated by language.

Polylang, TranslatePress, and WPML were all evaluated and rejected — their duplicate-post models are incompatible with the FRBR architecture, and per-site licensing does not scale for a multi-client product.

**Target:** A `umtd_translations` table — `post_id | lang | field_name | value`. Translatable fields (title, description) stored per language; language-agnostic fields (dates, relationships, dimensions) stored once. `umtd_get_field( $field, $post_id, $lang )` checks translations table first, falls back to postmeta. FRBR framing: a translation is a new Expression of the same Work, not a copy.

Dependency: custom DB schema must land first. Do not install a third-party translation plugin — it would create a migration problem.

---

## Tier 1 — Core Architecture

Unblocked, but high-impact enough to warrant doing before the platform matures.

### Agent Role Model

Current `agents_artists` / `agents_authors` parallel fields are a Piroir-specific hack encoding role as field name. Two resolution paths:

- **ACF Pro Repeater** (pre-custom-schema): single `agents` repeater with sub-fields `agent` (relationship) + `role` (select from `umtd_agent_role` taxonomy).
- **Custom DB schema** (preferred): role encoded in the junction table; no repeater needed.

If custom schema lands before a contract requiring this, skip the ACF Pro path.

### Custom Date Entry UI

**Status:** Deferred post-presentation. ACF date pickers remain as placeholder.

ACF date pickers are slow for data entry and cannot represent partial dates or qualifiers. The stored format `Ymd` with `0101` as a year-only convention is a workaround, not a data model.

**Intended UI (works edit screen):** Year (required) / Month (optional) / Day (optional, requires Month) / Qualifier (controlled vocabulary: `ca.`, `before`, `after`, `probably`, `attributed to` — full list TBD per EDTF and VRA Core 4.0) / Range toggle revealing a second date set for `date_latest`. `date_display` derived on save; not shown to user.

**Storage:** `date_earliest` and `date_latest` remain `Ymd` VARCHAR. Year-only: `YYYY0101`. Year+month: `YYYYMM01`.

**Implementation path:** custom metabox or ACF Pro custom field type. ACF free cannot handle conditional field logic (Day requires Month) without JS hacks. `assets/js/admin-fields.js` has a stub comment marking where the save logic will live.

**Reference:** EDTF / ISO 8601-2, VRA Core 4.0 date element, CDWA date/time of creation.

### Config-driven Schema.org Engine

Currently only `umtd_works` has schema output (`includes/schema.php`). Schema for `umtd_events` and `umtd_agents` not written. Sitewide Organization schema not written. Target: one engine reads a config array and outputs JSON-LD for all CPTs.

---

## Tier 2 — Admin UX

These improve the data entry experience. Unblocked except where noted.

### Auto-populate Agent Fields from ULAN / Wikidata

On the agent edit screen, user searches by name across existing agents (duplicate check), Getty ULAN, Wikidata, and Wikipedia. Selecting a result auto-populates available fields. Implementation: admin JS + PHP AJAX + Wikidata and Getty ULAN APIs. Estimated 4–6 hours.

### Alt Text Auto-generation

Hook on `acf/save_post` for attachments to auto-generate `_wp_attachment_image_alt` from structured metadata:

- Work image: `{agent name_display}, {work title}, {date_display}, {view_type}`
- Installation/exhibition view: `Installation view, {event title}, {location}, {date}`

Reads `related_work`, `photographer`, `view_type`, `image_date` from ACF fields, writes to `_wp_attachment_image_alt` via `update_post_meta`. The `attachment_fields_to_edit` filter already makes alt text read-only on the full edit screen — this hook would populate it.

### Attachment Modal Field Control

`attachment_fields_to_edit` applies to the full attachment edit screen only. Caption, description, and alt text remain editable in the WordPress media modal, which uses a separate JS rendering path (`wp.media` views). Suppression in the modal requires overriding core JS templates — deferred; primary metadata entry is on the full edit screen.

### Medium Taxonomy Conditional Display

`umtd_medium` terms are process categories independent of work type. A future refinement would show only relevant medium terms based on the selected `umtd_work_type` — e.g. Intaglio terms only when Print is selected. Requires JS on the work edit screen. Acceptable at current vocabulary size (3 terms).

### Event Title Sync Hook

No `acf/save_post` hook enforces event title format. Editorial pattern established: `Artist(s) — Event Title` for solo shows, `Exhibition Title` for group shows. Editorial discipline is the current control.

### Page Generation on Child Plugin Activation

Child plugin activation could call `wp_insert_post()` to create required navigation pages with correct slugs and page templates assigned via `_wp_page_template` postmeta. Currently done manually in WP admin. Config-driven: define required pages in a child plugin config array, generate on activation.

---

## Tier 3 — Front-end Features

### Language Switcher and Menu URL Rewriting

Inbound URL routing is complete. Outbound URL generation is not implemented:

- `umtd_localize_url( $url, $lang )` — rewrites any URL to a target language
- `wp_nav_menu_objects` filter — rewrites menu item URLs via `umtd_localize_url()`
- `umtd_language_switcher()` — generates `[lang => url]` map for current page
- `pages` map in `config/i18n.php` — slug translation table for WordPress pages (not yet added)

Until implemented, nav menu links always point to the default language URL. Dependency: Translation Model (content must be differentiated by language before a switcher is meaningful).

### Helper Function — Agent Display Name

`umtd_get_agent_display_name( $id )` wrapping `get_field('name_display', $id)` with a `get_the_title()` fallback. Centralizes the pattern used across `card-work.php`, `card-agent.php`, `single-umtd_works.php`, `single-umtd_agents.php`. Unblocked and trivial.

### Related Works Field Label

`related_works` on both Work Metadata and Event Metadata uses a generic label. Semantics were never decided:
- On Works: editions, series variants, or related objects?
- On Events: works exhibited, works documented?

Decide with client input before production data entry.

### Search / Filtering UI

Not started.

### REST API

Not started.

---

## Tier 4 — Infrastructure and Housekeeping

### Staging Environment

EC2 instance mirroring production. Required before production launch.

### Backup Automation

No automated backups or EBS snapshots. Run manual backup before major changes. See `INFRASTRUCTURE.md` — Maintenance.

### PHP/MariaDB Version Pinning

PHP 8.5 local vs PHP 8.4 production. MariaDB 12.0.2 local vs production version. Resolve before production launch.

### Uninstall Hook

Plugin leaves orphaned CPT data and taxonomy terms on deactivation/removal.

### `wp i18n make-pot`

Not generated. Required before any translation work begins. Dependency: Translation Model.

### Lazy-load JS

`data-lazy` attribute stubbed on archive grids. No JS written.

### Caching

Irrelevant at Piroir scale. Revisit if traffic warrants.

### WP Admin Menu Placement

`show_in_menu` on CPTs is unset — CPTs appear at the top level of the admin sidebar. Requires deciding on a menu structure before implementing.

### Class-based Plugin Architecture

Current procedural structure is acceptable at this scale.

### PHP/JS Style Guide

No formal style guide defined. Current conventions: tabs, `array()` not `[]` (schema.php uses `[]` — inconsistent), strict comparison throughout. Recommend adopting WordPress Coding Standards formally and adding `.editorconfig` and optionally `phpcs.xml` with `WordPress-Core` ruleset.

---

## Piroir-specific

### Child Theme — umt-design-piroir

Not started. Client typography, colour, layout, branding.

### Nav Menus — Piroir

Nav menus are registered in `functions.php`. Menus need to be created and assigned in Appearance → Menus on the production site.
