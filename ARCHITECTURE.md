# UMT Studio — Architecture

## Overview

White-label WordPress CMS for cultural heritage and commercial archive clients. Base plugin + base theme pair, extended per client via child plugin + child theme. Manages Works, Agents, Images, and Events as interrelated archival entities aligned with FRBR.

---

## Repository Structure

| Repo | Type | Purpose |
|---|---|---|
| `umtd-plugin` | Base plugin | CPTs, taxonomies, ACF fields, schema.org, agent logic, i18n routing |
| `umtd-theme` | Base theme | Semantic HTML templates, BEM CSS, no client branding |
| `umtd-plugin-{client}` | Child plugin | Work type whitelist, active languages, client-specific ACF overrides |
| `umtd-theme-{client}` | Child theme | Client typography, colour, layout, branding |

New client = new child plugin + new child theme. Base repos are never modified for client work.

---

## Naming Conventions

| Context | Convention | Example |
|---|---|---|
| PHP function prefix | `umtd_` | `umtd_register_post_types()` |
| PHP constant prefix | `UMTD_` | `UMTD_VERSION`, `UMTD_PATH` |
| Plugin text domain | `umtd` | `__( 'Works', 'umtd' )` |
| Theme text domain | `umtd-theme` | `__( 'All', 'umtd-theme' )` |
| CSS methodology | BEM | `.work-card__title`, `.archive-grid` |
| Post type prefix | `umtd_` | `umtd_works`, `umtd_agents`, `umtd_events` |
| Taxonomy prefix | `umtd_` | `umtd_work_type`, `umtd_agent_role` |
| ACF field names | snake_case, no prefix | `agent`, `start_date`, `name_last` |

---

## Plugin Architecture

### File Structure

```
umtd-plugin/
├── umtd-plugin.php         — bootstrap, constants, require chain
├── config/
│   ├── post-types.php      — CPT definitions (data only, no slugs)
│   ├── taxonomies.php      — taxonomy definitions (no slugs)
│   ├── terms.php           — full AAT-aligned controlled vocabulary
│   ├── roles.php           — agent roles vocabulary (slug → en/fr labels)
│   ├── view-types.php      — image view types vocabulary (slug → en/fr labels)
│   ├── tables.php          — custom table SQL definitions for dbDelta()
│   └── i18n.php            — slug translations and language config
├── includes/
│   ├── admin.php           — agent name sync, admin enqueue, attachment UI control
│   ├── schema.php          — schema.org JSON-LD output
│   ├── db.php              — custom table read layer: umtd_get_field(), umtd_get_work_agents(), umtd_format_date(), FK lookup helpers
│   ├── save.php            — acf/save_post intercepts: writes scalar fields to entity tables, junction rows to work_agents/event_agents/event_works
│   └── metabox.php         — agent+role meta box on umtd_works; AJAX agent search; writes to umtd_work_agents
├── assets/
│   └── js/
│       └── admin-fields.js — display name autopopulation in admin
└── acf-json/               — ACF field group JSON (base plugin only)

umtd-plugin-{client}/
├── umtd-plugin-{client}.php — bootstrap, activation hook, language filter
└── config/
    └── terms.php            — client whitelist: subset of base terms by name, for all three taxonomies
```

### Bootstrap

`UMTD_PATH` and `UMTD_VERSION` are defined at file load time in `umtd-plugin.php`.

On `plugins_loaded`, child plugins register their `umtd_i18n` filter. This hook is used rather than top-level registration because WordPress plugin load order is not guaranteed — `plugins_loaded` fires after all plugins have loaded regardless of order.

On `init`, `umtd-plugin.php` registers CPTs (via `umtd_post_types` filter), registers taxonomies (via `umtd_taxonomies` filter), loads `schema.php` and `admin.php`, and registers `acf-json/` as the ACF load path. By the time `init` fires, `plugins_loaded` has already run and all child plugin filters are registered.

On activation, `umtd-plugin.php` calls `umtd_register_tables()` — creates all custom tables via `dbDelta()`. Then calls `umtd_seed_terms()` — inserts all base vocabulary terms with `aat_id` as term meta. Then calls `umtd_seed_roles()` and `umtd_seed_view_types()` — populates the `umtd_roles` and `umtd_view_types` vocabulary tables from `config/roles.php` and `config/view-types.php`.

`umtd_register_tables()` is also hooked to `plugins_loaded` — `dbDelta()` is idempotent and handles column additions on upgrade without data loss, so running it on every request ensures tables exist after deploys without requiring reactivation.

On activation, child plugin reads its whitelist and the base vocabulary via `UMTD_PATH`, inserts whitelisted terms, and deletes non-whitelisted ones. **Requires `umtd-plugin` active first per `Requires Plugins` header.**

### Base vs Child Boundary

Child plugins communicate with the base exclusively through filter hooks — no direct function calls in either direction.

### Filter Hook Contracts

```php
apply_filters( 'umtd_post_types', $post_types )      // CPT definitions
apply_filters( 'umtd_taxonomies', $taxonomies )      // taxonomy definitions
apply_filters( 'umtd_terms', $terms )                // controlled vocabulary
apply_filters( 'umtd_i18n', $i18n )                  // language config and slug translations
apply_filters( 'umtd_schema_config', $config )       // schema.org output config
apply_filters( 'umtd_schema_tables', $definitions )  // custom table definitions array — child plugins unset tables not needed for their client
apply_filters( 'umtd_roles', $roles )                // agent roles vocabulary — child plugins extend
apply_filters( 'umtd_view_types', $view_types )      // image view types vocabulary — child plugins extend
```

### CPT Registration

Defined in `config/post-types.php` as a data array. Slugs are not defined in this file — they are owned by `config/i18n.php`. Per-CPT overrides accepted via `$passthrough_keys` (`has_archive`, `hierarchical`, `capability_type`, `map_meta_cap`, `publicly_queryable`, `exclude_from_search`). Merge order: `array_merge( $defaults, $labels, $computed, $overrides )` — overrides always win.

`has_archive` is handled separately from passthrough — if `true` or unset, it is replaced with the language-prefixed slug string so the archive URL matches the single URL pattern. If explicitly `false` (e.g. `umtd_events`), it is preserved.

### Taxonomy Config Keys

| Key | Type | Notes |
|---|---|---|
| `enabled` | bool | Skip registration if false |
| `plural` | string | Admin labels |
| `singular` | string | Admin labels |
| `hierarchical` | bool | true = category-like |
| `post_types` | array | CPTs this taxonomy attaches to |
| `capabilities` | array | Optional. WordPress capability map — restrict term creation/editing to specific roles. If absent, WordPress defaults apply. Standard pattern: `manage_terms` and `edit_terms` → `manage_options` (admin only); `assign_terms` → `edit_posts`. |

Note: `slug` key is absent — slugs are owned by `config/i18n.php`.

### Registered Taxonomies

| Taxonomy | CPT | Hierarchical | Notes |
|---|---|---|---|
| `umtd_work_type` | `umtd_works` | true | AAT-aligned work type vocabulary |
| `umtd_event_type` | `umtd_events` | true | AAT-aligned event type vocabulary |
| `umtd_medium` | `umtd_works` | false | Process/material categories — see hung lantern below |

Canonical vocabulary for all three taxonomies is in `config/terms.php` (AAT IDs as keys, display names as values). Term identity is the **name** — renaming breaks existing assignments. AAT IDs are reference metadata only.

Native WordPress sidebar metaboxes for `umtd_medium` and `umtd_event_type` are suppressed in `includes/admin.php` via `admin_menu` — these taxonomies are managed through ACF fields in the main edit form. Non-hierarchical taxonomies use the `tagsdiv-{taxonomy}` metabox ID; hierarchical use `{taxonomy}div`.

**Hung lantern — `umtd_medium` scope:** the current vocabulary (Intaglio, Relief, Planographic, 35mm, Oil, Acrylic) is adequate for print and painting work types but does not generalise across all work types. For film, medium means stock format; for books and articles, medium is not meaningful; for mixed-media sculpture, medium is multi-value with hierarchical specificity. A redesign splitting `umtd_medium` into separate `umtd_material` and `umtd_technique` taxonomies, or replacing it with per-type ACF select fields, is required before any multi-discipline client beyond xyla.zone is onboarded.

---

## Internationalisation

### Overview

URL routing is language-prefixed on multilingual installs. When `languages` contains more than one entry, all CPT single and archive URLs include a language code prefix — e.g. `/fr/oeuvres/{slug}/`, `/en/works/{slug}/`. On monolingual installs (single entry in `languages`), no prefix is generated — URLs are bare slugs: `/{works-slug}/{slug}/`. Bare slugs on a multilingual install 404 by design.

Internal links generated by WordPress (`get_permalink()`, archive links) always reflect the registered slug, which includes the prefix only when multiple languages are active.

URL pattern (multilingual): `/{lang}/{translated-slug}/{post-name}/` — e.g. `/fr/artistes/grubisic-katia/`, `/en/artists/grubisic-katia/`. Translated slugs are used because the slug itself communicates language. See URL Architecture table in Theme Architecture.

The `lang` query var is set in rewrite rules and readable via `get_query_var( 'lang' )`. Content differentiation by language and outbound URL rewriting are implemented as part of the custom database schema, which introduces a dedicated translations table and a `umtd_get_field()` access layer supporting per-language field resolution. See `SCHEMA.md` and `ROADMAP.md` for the delivery sequence.

### config/i18n.php

All slug translations and language config live here. This is the only place slugs are defined — `post-types.php` and `taxonomies.php` have no slug keys.

```php
return array(
    'default_lang' => 'en',
    'languages'    => array( 'en' ), // child plugin overrides
    'slugs'        => array(
        'umtd_works'      => array( 'en' => 'works',      'fr' => 'oeuvres',        ... ),
        'umtd_agents'     => array( 'en' => 'artists',    'fr' => 'artistes',       ... ),
        'umtd_events'     => array( 'en' => 'events',     'fr' => 'evenements',     ... ),
        'umtd_work_type'  => array( 'en' => 'work-type',  'fr' => 'type-oeuvre',    ... ),
        'umtd_event_type' => array( 'en' => 'event-type', 'fr' => 'type-evenement', ... ),
        'umtd_medium'     => array( 'en' => 'medium',     'fr' => 'technique',      ... ),
    ),
);
```

Adding a new CPT or taxonomy: add its slug translations here. Adding a new language to the platform: add a column here and add translation strings for all CPTs and taxonomies. Child plugins never modify this file.

### Child Plugin Language Declaration

Child plugin declares active languages via `umtd_i18n` filter, inside a `plugins_loaded` callback:

```php
// Bilingual install — FR primary, EN supplementary
add_filter( 'umtd_i18n', function( $i18n ) {
    $i18n['default_lang'] = 'fr';
    $i18n['languages']    = array( 'fr', 'en' );
    return $i18n;
} );

// Monolingual install — EN only, no URL prefix
add_filter( 'umtd_i18n', function( $i18n ) {
    $i18n['default_lang'] = 'en';
    $i18n['languages']    = array( 'en' );
    return $i18n;
} );
```

`default_lang` sets the primary rewrite slug. On a bilingual install, `fr` produces `/fr/oeuvres/{slug}/` as the canonical URL. On a monolingual install with a single entry in `languages`, no prefix is prepended — URLs are bare slugs. Each additional language in `languages` gets supplementary rewrite rules. Languages absent from the array generate no routes.

`umtd` generates the correct filter values from `config.yml` during provisioning — the child plugin `umtd_i18n` filter should never be edited manually.

---

## ACF Field Groups

Base plugin fields load from `acf-json/`. **No save path is registered in the base plugin** — base field groups are read-only on all deployed installs. To modify: temporarily add a save path to `umtd-plugin.php`, edit on localhost, save, remove the save path, commit updated JSON, deploy.

Child plugin fields load from `umtd-plugin-{client}/acf-json/` via a load path filter registered in the `plugins_loaded` callback.

### Field Groups

| File | Group | Scope | Notes |
|---|---|---|---|
| `group_69b042aeefe3b.json` | Agent Metadata | `umtd_agents` | All agent types |
| `group_69b04222d0542.json` | Event Metadata | `umtd_events` | All event types |
| `group_69b0409962f04.json` | Work Metadata | `umtd_works` | Universal fields — all work types |
| `group_69b9919e17cbb.json` | Image Metadata | attachments | All attachments |
| `group_work_visual_object.json` | Work: Visual Object | `umtd_works` | Painting, Drawing, Sculpture, Photograph, Installation |
| `group_work_print.json` | Work: Print | `umtd_works` | Print, Photograph |
| `group_work_film.json` | Work: Film | `umtd_works` | Film, Video |
| `group_work_bibliographic.json` | Work: Bibliographic | `umtd_works` | Books, Monographs, Articles, Artist Book |
| `group_work_listing.json` | Work: Listing | `umtd_works` | Listing |

Per-type field groups use ACF location rules `post_type == umtd_works` AND `post_taxonomy == umtd_work_type:{term-slug}`. The work type must be set and the post saved before type-specific field groups appear. A planned admin UX improvement (programmatic auto-draft creation with pre-assigned term, triggered from per-type admin menu items) eliminates the two-save requirement — see ROADMAP.md.

**Field access:** `includes/db.php` provides `umtd_get_field()` as the target read interface. It reads from custom tables first, falls back to `get_field()` for any field not yet intercepted on save. Current templates still call `get_field()` directly — migration to `umtd_get_field()` throughout is part of the template restructure (see ROADMAP.md). Field tables below reflect current ACF field names; the access layer is transparent to field name.

### Agent Metadata Fields

| Field | Key | Type | Condition | Notes |
|---|---|---|---|---|
| `agent_type` | — | radio | — | `person` / `organization`, default `person`; drives all conditional logic |
| `name_first` | `field_69bb206cb458b` | text | person | ACF field key required — referenced by `admin-fields.js` |
| `name_last` | `field_69bb209ab458d` | text | person | ACF field key required — referenced by `admin-fields.js`; drives post title/slug |
| `name_display` | `field_69bb22d2064d2` | text | — | ACF field key required — referenced by `admin-fields.js`; use for all front-end output |
| `name_variants` | — | textarea | — | one per line |
| `gender` | — | radio | person | male / female / non-binary / N/A |
| `birth_date` | — | date picker | person | stored `Ymd`, returns `Ymd` |
| `death_date` | — | date picker | person | stored `Ymd`, returns `Ymd` |
| `place_of_birth` | — | textarea | person | |
| `place_of_death` | — | textarea | person | |
| `country` | — | text | person | nationality |
| `biography` | — | textarea | — | |
| `website` | — | url | — | |
| `wikidata_id` | — | text | — | stored with or without `Q` prefix; `schema.php` normalises both |
| `ulan_id` | — | text | — | numeric |
| `founding_date` | — | date picker | organization | stored `Ymd`, returns `Ymd` |
| `dissolution_date` | — | date picker | organization | stored `Ymd`, returns `Ymd` |
| `org_location` | — | text | organization | |
| `parent_org` | — | relationship | organization | → `umtd_agents` |

### Work Metadata Fields — Universal (all work types)

| Field | Key | Type | Notes |
|---|---|---|---|
| `date_display` | — | text | human-readable, e.g. `ca. 1987–89` |
| `date_earliest` | — | date picker | stored `Ymd`, returns `Ymd` |
| `date_latest` | — | date picker | stored `Ymd`, returns `Ymd` |
| `description` | — | textarea | |
| `related_works` | — | relationship | → `umtd_works`; explicit object-to-object relations planned via `umtd_work_relations`. See `SCHEMA.md`. |

**Agent assignment** is handled by the Agents meta box (`includes/metabox.php`), not an ACF relationship field. The meta box renders an AJAX-powered search field paired with a role select (populated from `umtd_roles`) for each agent. On save, rows are written directly to `umtd_work_agents` — no ACF postmeta involvement. The interim `agents_artists` / `agents_authors` ACF fields are removed. Read via `umtd_get_work_agents( $post_id )`.

**Hung lantern — agent field filtering:** the meta box currently shows all published agents in search regardless of work type. A planned filter will restrict visible agents by role, keyed to a work-type → permitted-roles mapping in the base plugin config. Deferred.

### Work Metadata Fields — Visual Object (Painting, Drawing, Sculpture, Photograph, Installation)

VRA Core elements: `material`, `measurements`, `inscription`, `stylePeriod`, `textref`, `location`.

| Field | Type | Notes |
|---|---|---|
| `medium` | taxonomy | `umtd_medium` — VRA Core `material` |
| `support` | text | VRA Core `material` (type=support) — e.g. canvas, paper, wood panel |
| `dimensions_h` | text | VRA Core `measurements` (type=height) |
| `dimensions_w` | text | VRA Core `measurements` (type=width) |
| `dimensions_d` | text | VRA Core `measurements` (type=depth) — sculpture and 3D works |
| `dimensions_unit` | select | cm (default) / mm / in |
| `current_location` | relationship | → `umtd_agents`; VRA Core `location` (type=repository) |
| `accession_number` | text | VRA Core `location` refid (type=accession) |
| `inscription` | textarea | VRA Core `inscription` — transcription of text on the object |
| `style_period` | text | VRA Core `stylePeriod` — AAT-aligned |
| `catalogue_raisonne` | text | VRA Core `textref` — catalogue raisonné reference number |

### Work Metadata Fields — Print (Print, Photograph)

VRA Core element: `stateEdition`.

| Field | Type | Notes |
|---|---|---|
| `edition_size` | number | VRA Core `stateEdition` — total impressions in edition |
| `printer_copies` | number | VRA Core `stateEdition` — proofs outside numbered edition (AP, HC, etc.) |
| `print_state` | text | VRA Core `stateEdition` — state designation if plate reworked between impressions |

### Work Metadata Fields — Film (Film, Video)

VRA Core elements: `measurements` (type=duration), `material`, `culturalContext`, `textref`.

| Field | Type | Notes |
|---|---|---|
| `runtime` | number | VRA Core `measurements` (type=duration) — minutes |
| `film_format` | select | VRA Core `material` — 35mm / 16mm / 8mm / Digital / Video / Other |
| `language` | text | ISO 639-1 language code(s) of original dialogue |
| `isan` | text | VRA Core `textref` — International Standard Audiovisual Number |
| `country_of_origin` | text | VRA Core `culturalContext` — ISO 3166-1 alpha-2 |

### Work Metadata Fields — Bibliographic (Books, Monographs, Articles, Artist Book)

Standards: ISBD, Dublin Core, VRA Core `stateEdition`, `textref`.

| Field | Type | Notes |
|---|---|---|
| `isbn` | text | ISBD — ISBN-13 preferred. Books and monographs. |
| `issn` | text | ISBD — identifies the journal. Articles. |
| `doi` | text | Dublin Core `identifier` — articles and digital publications |
| `place_of_publication` | text | ISBD |
| `edition_number` | text | VRA Core `stateEdition` — edition statement as published |
| `page_count` | number | ISBD `extent` |
| `journal_title` | text | ISBD — journal or periodical. Articles only. |
| `volume` | text | ISBD — journal volume. Articles only. |
| `issue` | text | ISBD — journal issue. Articles only. |
| `page_range` | text | ISBD — article page range, e.g. `pp. 45–67`. Articles only. |

### Work Metadata Fields — Listing

Local vocabulary. No external standard covers transactional real estate listing fields.

| Field | Type | Notes |
|---|---|---|
| `listing_address` | textarea | Full civic address |
| `tenure_type` | select | Freehold / Leasehold / Rental / Cooperative / Other |
| `listing_status` | select | Active / Under Offer / Sold / Rented / Withdrawn |
| `floor_area` | number | Total interior floor area |
| `floor_area_unit` | select | sq ft / m² |
| `rooms` | number | Total rooms excluding bathrooms |
| `bathrooms` | number | |

### Event Metadata Fields

| Field | Key | Type | Notes |
|---|---|---|---|
| `start_date` | — | date picker | stored `Ymd`, returns `Ymd` |
| `end_date` | — | date picker | stored `Ymd`, returns `Ymd` |
| `event_type` | — | taxonomy | `umtd_event_type` — Exhibition / Opening / Workshop / Performance / Premiere / Fair / Market / Retrospective; returns `WP_Term` object — use `->name` for display |
| `location` | — | relationship | → `umtd_agents`; venue agents (subtype `venue`) are the intended target |
| `organizing_agents` | — | relationship | → `umtd_agents`; `return_format: object` |
| `participating_agents` | — | relationship | → `umtd_agents`; `return_format: object`; no conditional logic |
| `description` | — | textarea | |
| `event_link` | — | url | |
| `related_works` | — | relationship | → `umtd_works`; written to `umtd_event_works` on save via `acf/save_post` intercept at priority 30 |

### Image Metadata Fields

Attaches to all WordPress attachments (`attachment == all`) — not a CPT. Fields accessible via `get_field( 'field_name', $attachment_id )`.

WordPress attachment title is suppressed via `remove_post_type_support( 'attachment', 'title' )`. Caption and description are removed from the full edit screen via `attachment_fields_to_edit` filter. Alt text auto-generation is planned — see `ROADMAP.md`. Field suppression does not apply to the media modal, which uses a separate JS rendering path.

| Field | Key | Type | Notes |
|---|---|---|---|
| `view_type` | — | select | recto / verso / detail / installation view / exhibition view / before treatment / after treatment; default null |
| `rights_holder` | — | text | VRA Core `rightsHolder` |
| `license` | — | select | All Rights Reserved (default) / CC BY / CC BY-SA / CC BY-NC / Public Domain |
| `source` | — | text | VRA Core `source` — origin of the image file |
| `image_date` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `related_work` | — | relationship | → `umtd_works` |
| `photographer` | — | relationship | → `umtd_agents` |
| `event` | — | relationship | → `umtd_events` |

### Agent Name Logic

`umtd_sync_agent_title()` fires on `acf/save_post` at priority 20. Person: post title → `Last, First`, slug → `sanitize_title( $title )`. Organization: post title → `name_display`. Post title is the sort key — never the display value.

**Templates must always use `umtd_get_field( 'name_display', $id )` for agent display names, never `get_the_title()`. `get_field()` is acceptable during the transition period while template restructure is in progress.**

`admin-fields.js` auto-populates `name_display` as `First Last` from `name_first`/`name_last`. A `userEdited` flag stops auto-population once the editor manually edits `name_display`.

### Date Fields

ACF date pickers store as `Ymd` VARCHAR. `return_format` is set to `Ymd` in all field group JSON files — `get_field()` returns the raw stored value (`20250815`), not a formatted string. All `meta_query` date comparisons must use `'type' => 'CHAR'` — never `'type' => 'DATE'`.

`umtd_format_date( $ymd, $format )` in `includes/db.php` converts `Ymd` to a display string. Default format `j F Y` produces `15 August 2025`. Pass-through on values that don't parse as valid `Ymd` — plain year strings like `2015` render as-is. Use this function in all templates that display dates.

---

## Data Model

### Entity Types

| CPT | FRBR Equivalent | Schema.org Type | Notes |
|---|---|---|---|
| `umtd_works` | Work | `CreativeWork` / `VisualArtwork` | Schema.org type varies by `umtd_work_type` — see `schema.php` |
| `umtd_agents` | Person/Corporate Body | `Person` / `Organization` | |
| `umtd_events` | Event | `ExhibitionEvent` | |
| `post` (native) | Work | `Article` | Editorial / magazine posts. Multi-author via agent relationship field. Kept separate from `umtd_works` in admin UI. |

**Note on schema.org alignment:** schema.org's `CreativeWork` is a flat unified model; FRBR's WEMI hierarchy is structurally incompatible with it. Our schema.org JSON-LD output is a deliberate lossy projection of the FRBR-aligned internal model — a `umtd_works` record may represent a Work, Expression, or Manifestation depending on context. This is intentional and consistent with how schema.org is used across the web. The internal data model is FRBR-aligned; the serialization for search engines and linked data consumers is schema.org. These are separate concerns.

### Work–Agent Relationship

Works relate to Agents via the `umtd_work_agents` junction table (`work_id | agent_id | role_id | sort_order`). The ACF `agents` relationship field is removed — agent assignment is handled by the Agents meta box in `includes/metabox.php`, which writes directly to `umtd_work_agents` on save.

Query all agents for a work:

```php
$agents = umtd_get_work_agents( $post_id ); // returns array of stdClass with agent_post_id, role_slug, role_label, sort_order
```

Query all works for an agent:

```php
$work_post_ids = umtd_get_agent_works( $agent_post_id ); // returns array of post IDs
```

Query agents associated with works of a given type, optionally filtered by role:

```php
$agent_ids = umtd_get_agents_by_work_type( 'film' );            // all agents on Film works
$agent_ids = umtd_get_agents_by_work_type( 'books', 'author' ); // authors on Books works
```

**Entity data functions** — `includes/db.php` provides high-level functions that fetch all scalar fields for an entity as a keyed array. Templates call these once at the top of the template and pass the result to parts via `$args`. No `get_field()` calls in template or part files:

```php
$work  = umtd_get_work( $post_id );   // all work fields; per-type fields via get_field() fallback
$agent = umtd_get_agent( $post_id );  // all agent fields; works list separate via umtd_get_agent_works()
$event = umtd_get_event( $post_id );  // all event scalar fields; relational fields fetched separately
```

The old postmeta `LIKE` query pattern (`key => 'agents', compare => 'LIKE'`) is retired. No postmeta is written for agent relationships.

**Hung lantern — native post authorship:** `umtd_work_agents` is keyed to `umtd_works.id`. Native WordPress `post` multi-authorship (magazine/editorial) will require either a parallel `umtd_post_agents` table or an `entity_type` discriminator column on `umtd_work_agents`. Resolution deferred to v0.3.0 schema design.

### Taxonomy Queries

Query works by type:

```php
array(
    'taxonomy' => 'umtd_work_type',
    'field'    => 'slug',
    'terms'    => 'print',
)
```

To find agents associated with works of a given type, use `umtd_get_agents_by_work_type()` — one JOIN query replaces the old two-query postmeta pattern. See Work–Agent Relationship above.

`umtd_medium` and `umtd_event_type` are standard WordPress taxonomies — queryable directly via `tax_query` with no serialization issues.

---

## Theme Architecture

### File Structure

```
umtd-theme/
├── style.css               — theme header only
├── functions.php           — enqueue, nav registration, theme support
├── header.php              — opens <html>, <head>, <body>, <header>, <main>
├── footer.php              — closes <main>, <footer>, </body>, </html>
├── index.php / page.php / front-page.php / archive.php / single.php
├── archive-umtd_works.php / archive-umtd_agents.php / archive-umtd_events.php
├── single-umtd_works.php / single-umtd_agents.php / single-umtd_events.php
├── parts/
│   ├── card-work.php       — receives $args['work'], $args['agents']
│   ├── card-agent.php      — receives $args['agent']
│   ├── card-event.php      — receives $args['event'], $args['organizers']
│   └── work-type/
│       ├── film.php        — Film/Video metadata partial
│       ├── print.php       — Print metadata partial (stub)
│       ├── book.php        — Bibliographic metadata partial (stub)
│       ├── visual-object.php — Visual Object metadata partial (stub)
│       └── listing.php     — Listing metadata partial (stub)
├── templates/
│   ├── events-archive.php
│   ├── works-archive.php
│   ├── artists-archive.php
│   ├── prints-archive.php
│   └── books-archive.php
└── assets/css/main.css
```

### Page Templates vs CPT Archives

CPT archives (`archive-umtd_*.php`) exist but are not used for primary navigation. Client nav pages are WordPress pages with custom page templates (`templates/*.php`) running their own `WP_Query`. This decouples URL structure from CPT registration.

`umtd_events` has `'has_archive' => false` to prevent URL conflict with the client's events page slug.

Page templates are client-specific — the base theme ships minimal stubs. Pages must be created manually in WP admin with the correct template assigned (or via `wp_insert_post()` on child plugin activation — see `ROADMAP.md`).

### Semantic HTML Conventions

- Archive grids: `<ul class="archive-grid">`, cards are `<li>`
- Card titles: `<h3>` (`<h1>` = page title, `<h2>` = section headings)
- Single metadata: `<dl>/<dt>/<dd>` — `<dt>` labels in markup, hidden by CSS
- Images: always `<figure>`
- Dates: always `<time datetime="Y-m-d">`
- Nav filters: `<nav><ul><li><a>`, `aria-current="page"` on active item
- `<main>` opens in `header.php`, closes in `footer.php`

### URL Architecture

CPT single and archive URLs are language-prefixed on multilingual installs via the i18n rewrite system. On monolingual installs (single entry in `languages`), no prefix is generated — URLs are bare slugs. Page template URLs are WordPress pages with slugs set manually in WP admin — these are never language-prefixed. Outbound URL rewriting for nav menus is planned — see `ROADMAP.md`.

The first table illustrates a monolingual install (English only). The second illustrates a bilingual install with FR as the primary language and EN as supplementary.

**Monolingual (single language):**

| URL | Template | Notes |
|---|---|---|
| `/` | `front-page.php` | Homepage |
| `/{works-slug}/{slug}/` | `single-umtd_works.php` | Single work |
| `/{agents-slug}/{slug}/` | `single-umtd_agents.php` | Single agent |
| `/{events-slug}/{slug}/` | `single-umtd_events.php` | Single event |
| `/{client-slug}/` | `templates/events-archive.php` | Events archive page |
| `/{client-slug}/` | `templates/works-archive.php` | Works archive page |
| `/{client-slug}/` | `templates/agents-archive.php` | Agents archive page |

**Bilingual (FR primary, EN supplementary):**

| URL | Template | Notes |
|---|---|---|
| `/` | `front-page.php` | Homepage |
| `/{lang}/{works-slug}/{slug}/` | `single-umtd_works.php` | Single work |
| `/{lang}/{agents-slug}/{slug}/` | `single-umtd_agents.php` | Single agent |
| `/{lang}/{events-slug}/{slug}/` | `single-umtd_events.php` | Single event |
| `/{client-slug}/` | `templates/events-archive.php` | Events archive page |
| `/{client-slug}/` | `templates/works-archive.php` | Works archive page |
| `/{client-slug}/` | `templates/agents-archive.php` | Agents archive page |

CPT archive URLs are registered but not linked in primary nav — page template equivalents are used instead. Page slugs are client-defined.

---

## Versioning

Version defined in plugin header and as `UMTD_VERSION` constant. Semantic versioning:

- `MAJOR` — breaking change to filter hook signatures or ACF field key renames
- `MINOR` — new feature, backwards compatible
- `PATCH` — bugfix

Stay at `0.x` until filter hooks and field keys are stable. Tag releases `git tag v0.x.x`. Child plugins should document which base version they target.

