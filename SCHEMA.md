# UMT Studio — Database Schema

Version: 0.3.0-planned
Last revised: 2026-03-26

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

Agent roles within Works and Events (artist, author, printer, publisher, photographer, curator, etc.).

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
| `umtd_work_type` | `umtd_works` | Print, Artist Book, etc. AAT-aligned. |
| `umtd_medium` | `umtd_works` | Intaglio, Relief, Planographic. AAT-aligned. |
| `umtd_series` | `umtd_works` | Semantic grouping declared by artist. No AAT alignment required. |
| `umtd_event_type` | `umtd_events` | Exhibition, Opening, Workshop, etc. |

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

