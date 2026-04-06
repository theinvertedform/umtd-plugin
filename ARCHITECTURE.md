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
│   └── i18n.php            — slug translations and language config
├── includes/
│   ├── admin.php           — agent name sync, admin enqueue, attachment UI control
│   └── schema.php          — schema.org JSON-LD output
├── assets/
│   └── js/
│       └── admin-fields.js — display name autopopulation in admin
└── acf-json/               — ACF field group JSON (base plugin only)

umtd-plugin-{client}/
├── umtd-plugin-{client}.php — bootstrap, activation hook, language filter
└── config/
    └── terms.php            — client whitelist: subset of base terms by name
```

### Bootstrap

`UMTD_PATH` and `UMTD_VERSION` are defined at file load time in `umtd-plugin.php`.

On `plugins_loaded`, child plugins register their `umtd_i18n` filter. This hook is used rather than top-level registration because WordPress plugin load order is not guaranteed — `plugins_loaded` fires after all plugins have loaded regardless of order.

On `init`, `umtd-plugin.php` registers CPTs (via `umtd_post_types` filter), registers taxonomies (via `umtd_taxonomies` filter), loads `schema.php` and `admin.php`, and registers `acf-json/` as the ACF load path. By the time `init` fires, `plugins_loaded` has already run and all child plugin filters are registered.

On activation, `umtd-plugin.php` calls `umtd_seed_terms()` — inserts all base vocabulary terms with `aat_id` as term meta.

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

URL routing is language-prefixed throughout. All CPT single and archive URLs include a language code — bare slugs without a prefix are never registered and will 404. Internal links generated by WordPress (`get_permalink()`, archive links) always include the prefix because it is baked into the registered rewrite slug.

URL pattern: `/{lang}/{translated-slug}/{post-name}/` — e.g. `/fr/artistes/grubisic-katia/`, `/en/artists/grubisic-katia/`. Translated slugs are used because the slug itself communicates language. See URL Architecture table in Theme Architecture.

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
add_filter( 'umtd_i18n', function( $i18n ) {
    $i18n['default_lang'] = 'fr';
    $i18n['languages']    = array( 'fr', 'en' );
    return $i18n;
} );
```

`default_lang` sets the primary rewrite slug — e.g. `fr` produces `/fr/artistes/{slug}/` as the canonical URL and drives WordPress-generated links. Each additional language in `languages` gets supplementary rewrite rules. Languages absent from the array generate no routes.

`umtd_register_post_types()` and `umtd_register_taxonomies()` call `umtd_get_i18n()` on `init`, after all `plugins_loaded` callbacks have run, so child plugin language declarations are always in place before registration.

Adding a language: add translations to `config/i18n.php`, add the language code to the child plugin's `languages` array, flush rewrite rules.

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

**Hung lantern — field access:** field tables below reference `get_field()` for consistency with current implementation. At v0.3.0, all field reads move to `umtd_get_field()` via the custom schema access layer. See `SCHEMA.md`.

### Agent Metadata Fields

| Field | Key | Type | Condition | Notes |
|---|---|---|---|---|
| `agent_type` | — | radio | — | `person` / `organization`, default `person`; drives all conditional logic |
| `name_first` | `field_69bb206cb458b` | text | person | ACF field key required — referenced by `admin-fields.js` |
| `name_last` | `field_69bb209ab458d` | text | person | ACF field key required — referenced by `admin-fields.js`; drives post title/slug |
| `name_display` | `field_69bb22d2064d2` | text | — | ACF field key required — referenced by `admin-fields.js`; use for all front-end output |
| `name_variants` | — | textarea | — | one per line |
| `gender` | — | radio | person | male / female / non-binary / N/A |
| `birth_date` | — | date picker | person | stored `Ymd`, returns `Y-m-d` |
| `death_date` | — | date picker | person | stored `Ymd`, returns `Y-m-d` |
| `place_of_birth` | — | textarea | person | |
| `place_of_death` | — | textarea | person | |
| `country` | — | text | person | nationality |
| `biography` | — | textarea | — | |
| `website` | — | url | — | |
| `wikidata_id` | — | text | — | stored with or without `Q` prefix; `schema.php` normalises both |
| `ulan_id` | — | text | — | numeric |
| `founding_date` | — | date picker | organization | stored `Ymd`, returns `Y-m-d` |
| `dissolution_date` | — | date picker | organization | stored `Ymd`, returns `Y-m-d` |
| `org_location` | — | text | organization | |
| `parent_org` | — | relationship | organization | → `umtd_agents` |

### Work Metadata Fields — Universal (all work types)

| Field | Key | Type | Notes |
|---|---|---|---|
| `agents` | — | relationship | → `umtd_agents`, returns array of `WP_Post` objects |
| `date_display` | — | text | human-readable, e.g. `ca. 1987–89` |
| `date_earliest` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `date_latest` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `description` | — | textarea | |
| `related_works` | — | relationship | → `umtd_works`; explicit object-to-object relations planned via `umtd_work_relations`. See `SCHEMA.md`. |

Agent roles (artist, author, director, publisher, etc.) are differentiated via `umtd_roles` — not separate relationship fields. The interim `agents_artists` / `agents_authors` fields are removed; role differentiation is handled by the `umtd_work_agents` junction table at v0.3.0. See `SCHEMA.md`.

**Hung lantern — agent field filtering:** the `agents` relationship field currently shows all agents regardless of work type. A planned `acf/fields/relationship/query` filter will restrict visible agents by role, keyed to a work-type → permitted-roles mapping in the base plugin config. Deferred.

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
| `start_date` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `end_date` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `event_type` | — | taxonomy | `umtd_event_type` — Exhibition / Opening / Workshop / Performance / Premiere / Fair / Market |
| `location` | — | relationship | → `umtd_agents`; venue agents (subtype `venue`) are the intended target. `agent_type` filtering in the ACF UI is resolved via the custom agents table. See `SCHEMA.md`. |
| `organizing_agents` | — | relationship | → `umtd_agents` |
| `participating_agents` | — | relationship | → `umtd_agents` |
| `description` | — | textarea | |
| `event_link` | — | url | |
| `related_works` | — | relationship | → `umtd_works`; superseded by `umtd_event_works` junction table. See `SCHEMA.md`. |

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

**Templates must always use `get_field( 'name_display', $id )` for agent display names, never `get_the_title()`.**

`admin-fields.js` auto-populates `name_display` as `First Last` from `name_first`/`name_last`. A `userEdited` flag stops auto-population once the editor manually edits `name_display`.

### Date Fields

ACF date pickers store as `Ymd` VARCHAR. `get_field()` returns `Y-m-d` per ACF field settings. All `meta_query` date comparisons must use `'type' => 'CHAR'` — never `'type' => 'DATE'`.

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

Works relate to Agents via the `agents` ACF relationship field, stored as a serialized PHP array of post IDs in `wp_postmeta`. `get_field('agents')` returns an array of `WP_Post` objects.

Query works by agent:

```php
array(
    'key'     => 'agents',
    'value'   => '"' . $agent_id . '"',
    'compare' => 'LIKE',
)
```

This query pattern is appropriate for the current collection size. The custom database schema replaces it with a normalized `umtd_work_agents` junction table (`work_id | agent_id | role_id`), which supports indexed relational queries and role-differentiated agent credits. See `SCHEMA.md` and `ROADMAP.md`.

**Hung lantern — native post authorship:** `umtd_work_agents` is keyed to `umtd_works.id`. When the custom schema lands at v0.3.0, native WordPress `post` multi-authorship (magazine/editorial) will require either a parallel `umtd_post_agents` table or an `entity_type` discriminator column on `umtd_work_agents`. Resolution deferred to v0.3.0 schema design.

### Taxonomy Queries

Query works by type:

```php
array(
    'taxonomy' => 'umtd_work_type',
    'field'    => 'slug',
    'terms'    => 'print',
)
```

To find agents associated with works of a given type: `tax_query` → collect agent IDs via `get_field('agents', $work_id)` → second query with `post__in`. This two-query pattern is used with the current postmeta storage model. The custom schema replaces it with a direct JOIN on `umtd_work_agents`. See `SCHEMA.md`.

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
│   ├── card-work.php
│   ├── card-agent.php
│   └── card-event.php
├── templates/
│   ├── events-archive.php
│   ├── works-archive.php
│   └── agents-archive.php
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

CPT single and archive URLs are language-prefixed via the i18n rewrite system. Page template URLs are WordPress pages with slugs set manually in WP admin — these are not language-prefixed in the current implementation. Outbound URL rewriting for nav menus is planned — see `ROADMAP.md`.

The table below illustrates a bilingual install with FR as the primary language and EN as supplementary.

| URL | Template | Notes |
|---|---|---|
| `/` | `front-page.php` | Homepage |
| `/{lang}/{works-slug}/{slug}/` | `single-umtd_works.php` | Single work |
| `/{lang}/{agents-slug}/{slug}/` | `single-umtd_agents.php` | Single agent |
| `/{lang}/{events-slug}/{slug}/` | `single-umtd_events.php` | Single event |
| `/{client-slug}/` | `templates/events-archive.php` | Events archive page |
| `/{client-slug}/` | `templates/works-archive.php` | Works archive page |
| `/{client-slug}/` | `templates/agents-archive.php` | Agents archive page |

CPT archive URLs (`/{lang}/{works-slug}/`, `/{lang}/{agents-slug}/`) are registered but not linked in primary nav — page template equivalents are used instead. Page slugs are client-defined.

---

## Versioning

Version defined in plugin header and as `UMTD_VERSION` constant. Semantic versioning:

- `MAJOR` — breaking change to filter hook signatures or ACF field key renames
- `MINOR` — new feature, backwards compatible
- `PATCH` — bugfix

Stay at `0.x` until filter hooks and field keys are stable. Tag releases `git tag v0.x.x`. Child plugins should document which base version they target.

