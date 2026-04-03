# UMT Studio — Database Schema

Version: 0.3.0-planned
Last revised: 2026-04-03

This document defines the custom database schema that replaces WordPress postmeta as the primary data store for all umt.studio CPT data. It is the reference for implementation of `umtd_register_tables()`, `umtd_get_field()`, and all `acf/save_post` intercept hooks.

The schema is implemented in `v0.3.0` of the base plugin. All Piroir data is entered against this schema — there is no postmeta migration path for Piroir.

---

## Design Principles

**WordPress as scaffold, not data store.** WordPress posts remain the canonical URL and permission anchors. All content data lives in custom tables keyed to `post_id`. `WP_Post` is never the source of truth for field values — `umtd_get_field()` is.

**Normalized, indexed, queryable.** Every relationship is a foreign key in a junction table. No serialized arrays. No `LIKE` queries on text columns.

**Configurable per client.** The base plugin registers the full schema. Child plugins declare which tables are active via the `umtd_schema_tables` filter. Tables not declared by a child plugin are not created on that install. This means a client whose archive has no Events does not get event tables.

**Multilingual by design.** Translatable fields (title, description, biography) are stored in `umtd_translations`. Language-agnostic fields (dates, dimensions, identifiers) are stored once on the entity row. `umtd_get_field( $field, $post_id, $lang )` resolves the correct value.

**ACF for input, custom tables for storage.** ACF field groups continue to drive the admin edit UI. `acf/save_post` intercept hooks redirect writes to custom tables. `umtd_get_field()` reads from custom tables. ACF's own postmeta writes are suppressed for intercepted fields.

---

## Access Pattern

All field reads in templates use `umtd_get_field()`:

```php
umtd_get_field( string $field, int $post_id, string $lang = null ) : mixed
```

- Checks custom tables first.
- `$lang` defaults to the current active language if null.
- Falls back to `get_field()` for any field not yet covered by a custom table — this is a developer safety net during active development, not a data migration path.
- Returns null if the field does not exist in either location.

Templates must never call `get_field()` or `get_the_title()` directly. All agent display names use `umtd_get_field( 'name_display', $id )`.

---

## Core Entity Tables

### `umtd_works`

One row per Work (canonical edition record). Does not record individual exemplars — see Backlog: Item-level inventory.

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

**Hung lantern — per-type columns:** the current `umtd_works` table captures fields common to visual works (dimensions, edition size). Fields added in the v0.2.x ACF field groups for film (runtime, format, ISAN), bibliographic works (ISBN, ISSN, DOI, page count, journal metadata), and listings (address, tenure type, floor area) are stored in ACF postmeta until v0.3.0. At v0.3.0, these fields need either additional columns on `umtd_works` or typed extension tables (`umtd_works_film`, `umtd_works_bibliographic`, `umtd_works_listing`). The extension table pattern is preferred — it keeps `umtd_works` narrow and avoids sparse columns. Resolution deferred to v0.3.0 schema design.

### `umtd_agents`

Persons, organizations, and venues. Venues are agents with `agent_type = 'venue'` — they are excluded from public archive listings by default. Child plugin config controls which subtypes are publicly listed.

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

Translatable fields: `biography`, `name_display` (for organizations and venues where FR/EN names differ), `place_of_birth`, `place_of_death`, `org_location`.

`country` (nationality) and `gender` remain ACF postmeta fields for now — low query priority, deferred to schema v0.4.

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

Translatable fields: `title`, `description`.

`event_type` is the `umtd_event_type` taxonomy — not a column.

---

## Junction Tables

### `umtd_work_agents`

Work–Agent relationship with role. Replaces the `agents_artists` / `agents_authors` parallel field pattern.

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

**Hung lantern — native post authorship:** `umtd_work_agents` is keyed to `umtd_works.id` and covers only `umtd_works` CPT records. The platform now supports editorial/magazine content via the native WordPress `post` CPT with multi-author via agent relationship field. At v0.3.0, native post authorship requires one of: (a) a parallel `umtd_post_agents` table with identical structure but `post_id` FK instead of `work_id`; or (b) an `entity_type` discriminator column on `umtd_work_agents` (`entity_type ENUM('work','post')`, with `work_id` renamed to `entity_id`). Option (b) is more general but requires a migration. Resolution deferred to v0.3.0 schema design.

### `umtd_event_agents`

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

Works exhibited or documented at an Event.

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

Agent roles within Works and Events.

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

Seeded on activation via `umtd_seed_roles()`. Child plugins extend via `umtd_roles` filter, same pattern as `umtd_terms`.

**Hung lantern — roles seed data expansion:** the current seed data covers print and visual art roles (artist, printer, publisher, photographer, curator). The expanded work type vocabulary added in v0.2.x requires additional roles: `director`, `cinematographer`, `cast`, `producer`, `screenwriter` (Film/Video); `author`, `editor`, `translator`, `illustrator` (Bibliographic); `distributor` (Film). These must be added to `umtd_seed_roles()` before any film or bibliographic data entry on any client install. Not yet implemented.

### `umtd_view_types`

View types for Work media (recto, verso, detail, installation view, exhibition view, before treatment, after treatment).

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

---

## Translation Table

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

`umtd_get_field()` checks this table first when `$lang` is set or when the current active language is not the site default.

---

## WordPress Taxonomies (not DB tables)

These remain as WordPress taxonomies registered in `config/taxonomies.php`. They are controlled vocabularies with no per-record relational data — a taxonomy term is the correct structure.

| Taxonomy | CPT | Notes |
|---|---|---|
| `umtd_work_type` | `umtd_works` | Print, Artist Book, Painting, Sculpture, Film, Drawing, Installation, Performance, Video, Photograph, Books, Monographs, Articles, Listing. AAT-aligned where applicable; `local` key for Listing. |
| `umtd_medium` | `umtd_works` | Intaglio, Relief, Planographic, 35mm, Oil, Acrylic. AAT-aligned. See hung lantern in ARCHITECTURE.md — scope is inadequate for multi-type clients. |
| `umtd_series` | `umtd_works` | Semantic grouping declared by artist. No AAT alignment required. |
| `umtd_event_type` | `umtd_events` | Exhibition, Opening, Workshop, Performance, Premiere, Fair, Market. |

---

## Child Plugin Configuration

Child plugins declare active tables via the `umtd_schema_tables` filter:

```php
add_filter( 'umtd_schema_tables', function( $tables ) {
    // Return only the tables this client needs.
    // Tables not listed are not created on activation.
    return array(
        'umtd_works',
        'umtd_agents',
        'umtd_events',
        'umtd_work_agents',
        'umtd_event_agents',
        'umtd_event_works',
        'umtd_work_media',
        'umtd_roles',
        'umtd_view_types',
        'umtd_translations',
    );
} );
```

Base plugin default: all tables active. Child plugin may remove tables irrelevant to the client's archive type.

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

Fields specific to film, bibliographic, and listing work types are stored in ACF postmeta until v0.3.0. At that point, typed extension tables are preferred over sparse columns on `umtd_works`:

- `umtd_works_film` — runtime, format, language, ISAN, country_of_origin
- `umtd_works_bibliographic` — isbn, issn, doi, place_of_publication, edition_number, page_count, journal_title, volume, issue, page_range
- `umtd_works_listing` — address, tenure_type, listing_status, floor_area, floor_area_unit, rooms, bathrooms

Each extension table carries a `work_id` FK → `umtd_works.id` and a one-to-one relationship enforced by a `UNIQUE KEY work_id`. Visual object fields (support, dimensions_d, inscription, style_period, catalogue_raisonne) and print fields (print_state) follow the same pattern. The base `umtd_works` table remains narrow — only fields universal to all work types.

