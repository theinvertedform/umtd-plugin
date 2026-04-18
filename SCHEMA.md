# UMT Studio — Database Schema

Version: 0.2.x
Last revised: 2026-04-18

This document defines the custom database schema that replaces WordPress postmeta as the primary data store for all umt.studio CPT data. Reference for `umtd_register_tables()`, `umtd_get_field()`, and all `acf/save_post` intercept hooks.

All eleven tables are implemented and active as of v0.2.x. Scalar fields for Works, Agents, and Events are intercepted on `acf/save_post` and written to entity tables. Agent name fields (`name_first`, `name_last`, `name_display`) are written to `umtd_agents`. Film/Video extension fields are intercepted at priority 25 and written to `umtd_works_film`. Print edition fields (`edition_size`, `printer_copies`) are written to `umtd_works`. Agent–Work relationships are written to `umtd_work_agents` via the Agents meta box (`includes/metabox.php`). Event–Agent and Event–Work relationships are written to their junction tables via `acf/save_post` intercepts at priority 30. The `umtd_translations` table is created but the write/read layer is not yet implemented — bilingual content entry is deferred. Bibliographic and listing per-type extension fields remain in ACF postmeta until extension tables are implemented.

---

## Design Principles

**WordPress as scaffold, not data store.** WordPress posts remain the canonical URL and permission anchors. All content data lives in custom tables keyed to `post_id`. `WP_Post` is never the source of truth for field values — `umtd_get_field()` is.

**Normalized, indexed, queryable.** Every relationship is a foreign key in a junction table. No serialized arrays. No `LIKE` queries on text columns.

**Configurable per client.** The base plugin registers the full schema. Child plugins declare which tables are active via the `umtd_schema_tables` filter. Tables not declared by a child plugin are not created on that install. This means a client whose archive has no Events does not get event tables.

**Multilingual by design.** Translatable fields (title, description, biography) are stored in `umtd_translations`. Language-agnostic fields (dates, dimensions, identifiers) are stored once on the entity row. `umtd_get_field( $field, $post_id, $lang )` resolves the correct value.

**ACF for input, custom tables for storage.** ACF field groups continue to drive the admin edit UI. `acf/save_post` intercept hooks redirect writes to custom tables. `umtd_get_field()` reads from custom tables. ACF's own postmeta writes are suppressed for intercepted fields.

---

## Access Pattern

**High-level — entity data arrays for templates:**

```php
umtd_get_work( int $post_id ) : array   // all scalar work fields, per-type via get_field() fallback
umtd_get_agent( int $post_id ) : array  // all scalar agent fields; works list separate
umtd_get_event( int $post_id ) : array  // all scalar event fields; relational fields separate
```

Templates call these once at the top and pass the result to parts via `$args`. No `get_field()` or `umtd_get_field()` calls in template or part files directly.

**Low-level — single field reads:**

```php
umtd_get_field( string $field, int $post_id, string $lang = null ) : mixed
```

- Checks `umtd_translations` first for translatable fields when `$lang` is set.
- Checks the custom entity table for scalar fields covered by the write intercepts.
- Falls back to `get_field()` for any field not yet covered by a custom table.
- Returns null if the field does not exist in either location.
**Relational field helpers:**

```php
umtd_get_work_agents( int $work_post_id, string $lang = 'en' ) : array  // agent+role rows for a work
umtd_get_agent_works( int $agent_post_id ) : int[]                       // work post IDs for an agent
umtd_get_event_works( int $event_post_id ) : array                       // work post IDs for an event
umtd_get_agents_by_work_type( string $type_slug, string $role_slug = null ) : int[]  // agent IDs by work type and role
```

**FK lookup helpers** (used internally by save intercepts and meta box):

```php
umtd_get_agent_id( int $post_id ) : int|null   // wp_post ID → umtd_agents.id
umtd_get_work_id( int $post_id ) : int|null    // wp_post ID → umtd_works.id
umtd_get_event_id( int $post_id ) : int|null   // wp_post ID → umtd_events.id
```

Agent display names must use `get_field( 'name_display', $id )` — never `get_the_title()`.

---

## Core Entity Tables

### `umtd_works`

One row per Work (canonical edition record). Does not record individual exemplars — see Deferred: Item-level inventory.

```sql
CREATE TABLE umtd_works (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id             BIGINT UNSIGNED NOT NULL,
    accession_number    VARCHAR(100)    DEFAULT NULL,
    date_earliest       VARCHAR(8)      DEFAULT NULL,  -- Ymd, 0101 = year only
    date_latest         VARCHAR(8)      DEFAULT NULL,
    date_display        VARCHAR(255)    DEFAULT NULL,  -- human-readable, e.g. "ca. 1987–89"
    dimensions_h        DECIMAL(10,2)   DEFAULT NULL,
    dimensions_w        DECIMAL(10,2)   DEFAULT NULL,
    dimensions_unit     VARCHAR(20)     DEFAULT NULL,  -- cm | mm | in
    edition_size        INT UNSIGNED    DEFAULT NULL,  -- total edition count
    printer_copies      INT UNSIGNED    DEFAULT NULL,  -- AP, HC, etc.
    PRIMARY KEY (id),
    UNIQUE KEY post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Translatable fields stored in `umtd_translations`: `title`, `description`.

`medium` and `work_type` are WordPress taxonomies (`umtd_medium`, `umtd_work_type`) — not columns. Series membership is the `umtd_series` taxonomy.

### `umtd_works_film`

Extension table for Film and Video work types. One-to-one with `umtd_works` via `work_id`. Written by the `acf/save_post` intercept at priority 25, which fires only when the work has a `umtd_work_type` term of `film` or `video`. Read via LEFT JOIN in `umtd_get_work()`.

```sql
CREATE TABLE umtd_works_film (
    work_id             BIGINT UNSIGNED NOT NULL,
    runtime             INT UNSIGNED    DEFAULT NULL,  -- minutes
    film_format         VARCHAR(100)    DEFAULT NULL,  -- 35mm, DCP, etc.
    language            VARCHAR(10)     DEFAULT NULL,  -- ISO 639-1
    isan                VARCHAR(100)    DEFAULT NULL,
    country_of_origin   VARCHAR(100)    DEFAULT NULL,
    PRIMARY KEY (work_id),
    UNIQUE KEY work_id (work_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `umtd_agents`

Persons, organizations, and venues. Venues are agents with `agent_type = 'venue'` — excluded from public archive listings by default.

```sql
CREATE TABLE umtd_agents (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id             BIGINT UNSIGNED NOT NULL,
    agent_type          ENUM('person','organization','venue') NOT NULL DEFAULT 'person',
    name_first          VARCHAR(255)    DEFAULT NULL,
    name_last           VARCHAR(255)    DEFAULT NULL,
    name_display        VARCHAR(255)    DEFAULT NULL,
    birth_date          VARCHAR(8)      DEFAULT NULL,
    death_date          VARCHAR(8)      DEFAULT NULL,
    founding_date       VARCHAR(8)      DEFAULT NULL,
    dissolution_date    VARCHAR(8)      DEFAULT NULL,
    ulan_id             VARCHAR(50)     DEFAULT NULL,
    wikidata_id         VARCHAR(50)     DEFAULT NULL,
    website             VARCHAR(2083)   DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY post_id (post_id),
    KEY agent_type (agent_type),
    KEY name_last (name_last)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Translatable fields: `biography`, `name_display` (for organizations and venues where FR/EN names differ).

`country` (nationality) and `gender` remain ACF postmeta fields — low query priority, deferred.

### `umtd_events`

```sql
CREATE TABLE umtd_events (
    id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id                 BIGINT UNSIGNED NOT NULL,
    start_date              VARCHAR(8)      DEFAULT NULL,
    end_date                VARCHAR(8)      DEFAULT NULL,
    organizing_agent_id     BIGINT UNSIGNED DEFAULT NULL,  -- FK → umtd_agents.id
    venue_id                BIGINT UNSIGNED DEFAULT NULL,  -- FK → umtd_agents.id, agent_type = 'venue'
    event_link              VARCHAR(2083)   DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY post_id (post_id),
    KEY start_date (start_date),
    KEY end_date (end_date),
    KEY organizing_agent_id (organizing_agent_id),
    KEY venue_id (venue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Note — partial implementation:** the `acf/save_post` intercept currently writes `start_date`, `end_date`, and `event_link`. `organizing_agent_id` and `venue_id` require resolving the ACF relationship field value to `umtd_agents.id` — this lookup is deferred alongside the event agent meta box. These columns exist in the table but are always null until the intercept is extended.

Translatable fields: `title`, `description`.

`event_type` is the `umtd_event_type` taxonomy — not a column.

---

## Junction Tables

### `umtd_work_agents`

Work–Agent relationship with role. Written by `includes/metabox.php` on `acf/save_post` at priority 30. Replaces the retired `agents_artists` / `agents_authors` ACF parallel field pattern.

```sql
CREATE TABLE umtd_work_agents (
    work_id     BIGINT UNSIGNED NOT NULL,  -- FK → umtd_works.id
    agent_id    BIGINT UNSIGNED NOT NULL,  -- FK → umtd_agents.id
    role_id     BIGINT UNSIGNED NOT NULL,  -- FK → umtd_roles.id
    sort_order  INT UNSIGNED    NOT NULL DEFAULT 0,
    PRIMARY KEY (work_id, agent_id, role_id),
    KEY agent_id (agent_id),
    KEY role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Read via `umtd_get_work_agents( $work_post_id )` in `includes/db.php`.

**Hung lantern — native post authorship:** `umtd_work_agents` is keyed to `umtd_works.id`. Native WordPress `post` multi-authorship (magazine/editorial) will require either a parallel `umtd_post_agents` table or an `entity_type` discriminator column. Resolution deferred to v0.3.0 schema design.

### `umtd_event_agents`

Written by `acf/save_post` intercept at priority 30. Role is inferred from field: `organizing_agents` → `curator`, `participating_agents` → `artist`. This is a blunt mapping — a proper event agent meta box (same pattern as Works) is deferred.

```sql
CREATE TABLE umtd_event_agents (
    event_id    BIGINT UNSIGNED NOT NULL,
    agent_id    BIGINT UNSIGNED NOT NULL,
    role_id     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (event_id, agent_id, role_id),
    KEY agent_id (agent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `umtd_event_works`

Works exhibited or documented at an Event. Written by `acf/save_post` intercept at priority 30 from the `related_works` ACF field on events.

```sql
CREATE TABLE umtd_event_works (
    event_id    BIGINT UNSIGNED NOT NULL,
    work_id     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (event_id, work_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `umtd_work_media`

Many-to-many: one Work → many attachments; one attachment → many Works.

```sql
CREATE TABLE umtd_work_media (
    work_id         BIGINT UNSIGNED NOT NULL,
    attachment_id   BIGINT UNSIGNED NOT NULL,  -- WP attachment post ID
    view_type_id    BIGINT UNSIGNED DEFAULT NULL,  -- FK → umtd_view_types.id
    sort_order      INT UNSIGNED    NOT NULL DEFAULT 0,
    PRIMARY KEY (work_id, attachment_id),
    KEY attachment_id (attachment_id),
    KEY view_type_id (view_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Vocabulary Tables

Vocabulary tables replace ACF select/radio fields for any value that requires a label in more than one language or that may be extended by a child plugin.

### `umtd_roles`

Agent roles within Works and Events. Seeded on activation via `umtd_seed_roles()` from `config/roles.php`. Child plugins extend via `umtd_roles` filter.

```sql
CREATE TABLE umtd_roles (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(100)    NOT NULL,
    label_en    VARCHAR(255)    NOT NULL,
    label_fr    VARCHAR(255)    DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Current seed vocabulary: `artist`, `photographer`, `publisher`, `curator`, `director`, `cinematographer`, `editor`, `cast`, `producer`, `screenwriter`, `composer`, `printer`, `author`, `translator`, `illustrator`, `distributor`, `production-company`. Adding a new role: add to `config/roles.php`, deploy, reactivate.

### `umtd_view_types`

View types for Work media. Seeded on activation via `umtd_seed_view_types()` from `config/view-types.php`. Child plugins extend via `umtd_view_types` filter.

```sql
CREATE TABLE umtd_view_types (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(100)    NOT NULL,
    label_en    VARCHAR(255)    NOT NULL,
    label_fr    VARCHAR(255)    DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Current seed vocabulary: `recto`, `verso`, `detail`, `installation-view`, `exhibition-view`, `before-treatment`, `after-treatment`.

---

## Translation Table

Table exists but write/read layer is not yet implemented. `umtd_get_field()` checks this table first for translatable fields when `$lang` is set — currently returns null for all fields since no rows exist. Bilingual content entry is deferred to the bilingual platform phase (see ROADMAP.md).

```sql
CREATE TABLE umtd_translations (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    entity_type     VARCHAR(50)     NOT NULL,  -- 'work' | 'agent' | 'event'
    entity_id       BIGINT UNSIGNED NOT NULL,  -- FK → umtd_{entity_type}s.id
    field_name      VARCHAR(100)    NOT NULL,
    lang            VARCHAR(10)     NOT NULL,  -- 'fr' | 'en'
    value           LONGTEXT        DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY entity_field_lang (entity_type, entity_id, field_name, lang),
    KEY entity_type_id (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## WordPress Taxonomies (not DB tables)

These remain as WordPress taxonomies registered in `config/taxonomies.php`. They are controlled vocabularies with no per-record relational data — a taxonomy term is the correct structure.

| Taxonomy | CPT | Notes |
|---|---|---|
| `umtd_work_type` | `umtd_works` | Print, Artist Book, Painting, Sculpture, Film, Drawing, Installation, Performance, Video, Photograph, Books, Monographs, Articles, Listing. AAT-aligned where applicable; `local` key for Listing. |
| `umtd_medium` | `umtd_works` | Intaglio, Relief, Planographic, 35mm, Oil, Acrylic. AAT-aligned. See hung lantern in ARCHITECTURE.md — scope is inadequate for multi-type clients. |
| `umtd_series` | `umtd_works` | Semantic grouping declared by artist. No AAT alignment required. |
| `umtd_event_type` | `umtd_events` | Exhibition, Opening, Workshop, Performance, Premiere, Fair, Market, Retrospective. |

---

## Child Plugin Configuration

Child plugins modify the active table set via the `umtd_schema_tables` filter. The filter receives the full definitions array from `config/tables.php` — child plugins unset entries for tables their client does not need:

```php
add_filter( 'umtd_schema_tables', function( $tables ) {
    // Client has no Events — remove event tables.
    unset( $tables['umtd_events'] );
    unset( $tables['umtd_event_agents'] );
    unset( $tables['umtd_event_works'] );
    return $tables;
} );
```

Base plugin default: all tables active. Tables removed by a child plugin are not created on activation — they will not exist in the database for that client install.

---

## Deferred — Item-level Inventory

A future `umtd_items` table will record individual physical exemplars of a Work (copy 3/20, an atelier proof, a damaged return). This is the FRBR Item level. It is intentionally out of scope for the current build — the public archive operates at the Work (edition) level. Individual copy tracking is an inventory management function addressed in a future release.

```sql
-- Planned, not implemented
CREATE TABLE umtd_items (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    work_id         BIGINT UNSIGNED NOT NULL,
    copy_number     INT UNSIGNED    DEFAULT NULL,
    item_type       VARCHAR(50)     DEFAULT NULL,  -- edition | AP | HC | proof | damaged
    accession_number VARCHAR(100)   DEFAULT NULL,
    condition_notes TEXT            DEFAULT NULL,
    location_agent_id BIGINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    KEY work_id (work_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Deferred — Work Relations

A `umtd_work_relations` junction table for explicit object-to-object relationships (isDerivedFrom, isDocumentedBy) is deferred until a client archive requires it. Series membership is currently handled by the `umtd_series` taxonomy, which is sufficient for Piroir.

---

## Deferred — Per-type Extension Tables

Bibliographic and listing work type fields are stored in ACF postmeta until v0.3.0. Film/video fields are implemented in `umtd_works_film`. At v0.3.0, typed extension tables are preferred over sparse columns on `umtd_works`:

- `umtd_works_bibliographic` — isbn, issn, doi, place_of_publication, edition_number, page_count, journal_title, volume, issue, page_range
- `umtd_works_listing` — address, tenure_type, listing_status, floor_area, floor_area_unit, rooms, bathrooms
Each extension table carries a `work_id` FK → `umtd_works.id` and a one-to-one relationship enforced by a `UNIQUE KEY work_id`. Visual object fields (support, dimensions_d, inscription, style_period, catalogue_raisonne) and print fields (print_state) follow the same pattern. The base `umtd_works` table remains narrow — only fields universal to all work types.
