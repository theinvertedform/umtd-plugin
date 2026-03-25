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

## Translation Model

**Status:** Deferred to custom DB schema milestone. URL routing is language-aware (see ARCHITECTURE.md — Internationalisation). Content is not yet differentiated by language.

**Decision:** Polylang was evaluated and rejected. Its duplicate-post model is incompatible with the FRBR target architecture. Polylang Pro licensing (€99/site) does not scale for a multi-client product. TranslatePress and WPML were also evaluated — all third-party solutions have the same fundamental mismatch with this stack.

**Target model:** A `umtd_translations` table — `post_id | lang | field_name | value`. Translatable fields (title, description) stored per language. Language-agnostic fields (dates, relationships, dimensions) stored once and shared. `umtd_get_field( $field, $post_id, $lang )` checks translations table first, falls back to postmeta.

**FRBR framing:** A translation is a new Expression of the same Work — not a copy of the Work record. The relationship is encoded in the data model, not in duplicate posts.

**Dependency:** Must be implemented as part of the custom DB schema. Installing a third-party translation plugin now would create a migration problem when the schema lands. Data import for any client should wait until the schema is in place.

---

## Custom DB Schema

**Status:** Post-contract. Currently all data lives in WordPress postmeta (ACF serialized arrays).

**Problem:** `meta_query` with `LIKE` on serialized postmeta does not scale. Work–Agent relationships stored as serialized post ID arrays cannot be queried efficiently. Translation model cannot be implemented cleanly on top of postmeta.

**Target:** Custom tables replacing postmeta for all CPT data. Junction table for Work–Agent–Role relationships. Translation table per above. `umtd_get_field()` abstraction layer so templates are insulated from storage changes.

**Dependency:** Must be designed before data import for any client. Importing into postmeta now means doing the import twice.

---

## Agent Role Model

ACF Pro Repeater required. Current `agents_artists` / `agents_authors` parallel fields are a Piroir-specific hack.

Target: single `agents` repeater with sub-fields `agent` (relationship) + `role` (select from `umtd_agent_role` taxonomy).

---

## Work–Agent Junction Table

FRBR-correct normalization target: `work_id | agent_id | role_id`. Deferred until after ACF Pro Repeater migration and scale justifies it. Superseded by Custom DB Schema above.

---

## Auto-populate Agent Fields from ULAN / Wikidata

Admin JS + PHP AJAX + Wikidata and Getty ULAN APIs. On agent edit screen, user searches by name across: existing agents (duplicate check), Getty ULAN, Wikidata, Wikipedia. Selecting a result auto-populates available fields. Estimated 4–6 hours.

---

## Config-driven Schema.org Engine

One engine reads a config array, outputs JSON-LD for all CPTs. Currently only `umtd_works` has schema output (`includes/schema.php`). Schema for `umtd_events` and `umtd_agents` not written. Sitewide Organization schema not written.

---

## Page Generation on Child Plugin Activation

Child plugin activation could call `wp_insert_post()` to create required navigation pages with correct slugs and page templates assigned via `_wp_page_template` postmeta. Currently pages are created manually in WP admin. Config-driven: define required pages in a child plugin config array, generate on activation.

---

## Helper Function — Agent Display Name

`umtd_get_agent_display_name( $id )` wrapping `get_field('name_display', $id)` with a `get_the_title()` fallback. Centralizes the pattern used across `card-work.php`, `card-agent.php`, `single-umtd_works.php`, `single-umtd_agents.php`.

---

## Child Theme — umt-design-piroir

Not started. Client typography, colour, layout, branding.

---

## Nav Menus — Piroir

Nav menus registered in `functions.php`. Menus need to be created and assigned in Appearance → Menus on the production site.

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

---

## WP Admin Menu Placement

`show_in_menu` on CPTs controls sidebar placement in the WP admin. Currently unset — CPTs appear at the top level. Requires deciding on a menu structure and slug convention before implementing.

---

## PHP/JS Style Guide

No formal style guide defined. Current conventions in use:
- Indentation: tabs
- PHP arrays: `array()` not `[]` (schema.php uses `[]` — inconsistent, should be normalized)
- ACF field access: `get_field()` never `get_the_title()` for agents
- Strict comparison: `===` throughout, `in_array( $val, $arr, true )`

Recommend adopting WordPress Coding Standards formally:
https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/

Consider adding a `.editorconfig` and optionally `phpcs.xml` with `WordPress-Core` ruleset for automated enforcement.

---

## PHP/MariaDB Version Pinning

PHP 8.5 local vs PHP 8.4 production. MariaDB 12.0.2 local vs production. Address before production launch.

---

## Backup Automation

No automated backups configured. Run manual backup before major changes. See `INFRASTRUCTURE.md` — Maintenance for manual backup commands.

## Agent Type as WordPress Taxonomy

`agent_type` is currently an ACF radio field stored as postmeta (`person` / `organization`).
ACF relationship fields can only filter by registered WordPress taxonomies — not postmeta values.
This means the `location` field on Events cannot be filtered to show only organization agents in
the ACF UI. Registering `umtd_agent_type` as a proper taxonomy would enable this filtering and
also make agent type queryable via `tax_query` rather than `meta_query`.

Dependency: requires migrating existing `agent_type` postmeta values to taxonomy terms on all
existing agent records. Do before production data import.

---

## Attachment Modal Field Control

`attachment_fields_to_edit` filter only applies to the full attachment edit screen — not the
WordPress media modal. Caption and description fields are still editable in the modal. Alt text
is also editable in the modal despite being read-only on the full edit screen.

Suppressing fields in the modal requires overriding core WordPress JS templates (`wp.media` views).
Deferred — acceptable for now since primary metadata entry happens on the full edit screen via
ACF fields.

---

## Alt Text Auto-generation

Hook on `acf/save_post` for attachments to auto-generate `_wp_attachment_image_alt` from
structured metadata. Proposed formula:

- Work image: `{agent name_display}, {work title}, {date_display}, {view_type}`
- Installation/exhibition view: `Installation view, {event title}, {location}, {date}`

Reads `related_work`, `photographer`, `view_type`, `image_date` from ACF fields on the
attachment, constructs the string, writes to `_wp_attachment_image_alt` via `update_post_meta`.

Not yet written. Related: `attachment_fields_to_edit` makes the field read-only on the full
edit screen once this is implemented.

---

## Medium Taxonomy Conditional Display

`umtd_medium` terms are process categories (Intaglio, Relief, Planographic) independent of work
type. A future refinement would show only relevant medium terms based on the selected
`umtd_work_type` — e.g. Intaglio terms only when Print is selected. Requires JS on the work
edit screen. Deferred — acceptable at current vocabulary size (3 terms).

---

## Event Title Sync Hook

No `acf/save_post` hook exists for `umtd_events` equivalent to `umtd_sync_agent_title()` for
agents. Event titles are free text set directly as the WordPress post title. Editorial pattern
established: `Artist(s) — Event Title` for solo shows, `Exhibition Title` for group shows.
A sync hook enforcing this format is deferred — editorial discipline is the current control.

---

## Language Switcher and Menu URL Rewriting

Inbound URL routing is complete — `/fr/artistes/slug/` and `/en/artists/slug/` both resolve,
`lang` query var is set. Outbound URL generation is not implemented:

- `umtd_localize_url( $url, $lang )` — rewrites any URL to a target language. Not written.
- `wp_nav_menu_objects` filter — rewrites menu item URLs through `umtd_localize_url()`. Not written.
- `umtd_language_switcher()` — generates `[lang => url]` map for current page. Not written.
- `pages` map in `config/i18n.php` — slug translation table for WordPress pages. Not added.

Until implemented, nav menu links always point to the default language URL regardless of current
language context, and there is no UI for switching languages.

---

## Related Works Field Label

`related_works` on both Work Metadata and Event Metadata groups uses a generic label. The
intended meaning was never decided:
- On Works: editions, series variants, or related objects?
- On Events: works exhibited, works documented?

Label and semantics to be decided with client input before production data entry.
