# UMT Studio — Architecture

## Overview

A white-label WordPress CMS for cultural heritage archive clients. Built as a base plugin + base theme pair, extended per client via child plugin + child theme. The system manages Works, Agents, and Events as interrelated archival entities aligned with FRBR data model principles.

---

## Repository Structure

One repository per deliverable:

| Repo | Type | Purpose |
|---|---|---|
| `umt-studio` | Base plugin | CPTs, taxonomies, ACF fields, schema.org, agent logic |
| `umt-design` | Base theme | Semantic HTML templates, BEM CSS, no client branding |
| `umt-studio-{client}` | Child plugin | Client work type whitelist, client-specific ACF overrides |
| `umt-design-{client}` | Child theme | Client typography, colour, layout, branding |

New client = new child plugin + new child theme. Base repos are never modified for client work.

---

## Naming Conventions

| Context | Convention | Example |
|---|---|---|
| PHP function prefix | `umtd_` | `umtd_register_post_types()` |
| PHP constant prefix | `UMTD_` | `UMTD_VERSION`, `UMTD_PATH` |
| Plugin text domain | `umtd` | `__( 'Works', 'umtd' )` |
| Theme text domain | `umt-design` | `__( 'All', 'umt-design' )` |
| CSS methodology | BEM | `.work-card__title`, `.archive-grid` |
| Post type prefix | `umtd_` | `umtd_works`, `umtd_agents`, `umtd_events` |
| Taxonomy prefix | `umtd_` | `umtd_work_type`, `umtd_agent_role` |
| ACF field names | snake_case, no prefix | `agent`, `start_date`, `name_last` |

---

## Plugin Architecture

### File Structure

```
umt-studio/
├── umt-studio.php          — bootstrap, constants, require chain
├── config/
│   ├── post-types.php      — CPT definitions (data only, no logic)
│   ├── taxonomies.php      — taxonomy definitions
│   └── terms.php           — full AAT-aligned controlled vocabulary
├── includes/
│   ├── agents.php          — agent name sync hook, admin enqueue
│   └── schema.php          — schema.org JSON-LD output
├── assets/
│   └── js/
│       └── agent-name.js   — display name autopopulation in admin
└── acf-json/               — ACF field group JSON (base plugin fields only)

umt-studio-piroir/
├── umt-studio-piroir.php   — bootstrap, activation hook, ACF JSON path
└── config/
    └── terms.php           — Piroir whitelist: subset of base terms by name
```

### Bootstrap Sequence

On WordPress `init`, `umt-studio.php`:
1. Defines `UMTD_PATH`
2. Calls `umtd_register_post_types()` — reads `config/post-types.php`, fires `umtd_post_types` filter, registers CPTs
3. Calls `umtd_register_taxonomies()` — reads `config/taxonomies.php`, fires `umtd_taxonomies` filter, registers taxonomies
4. Loads `includes/schema.php` and `includes/agents.php` via `require_once`
5. Registers `acf-json/` as ACF load and save path

On activation, `umt-studio.php` additionally calls `umtd_seed_terms()` — reads `config/terms.php`, inserts all base vocabulary terms into their taxonomies with `aat_id` stored as term meta.

On activation, `umt-studio-piroir.php`:
1. Reads its own `config/terms.php` (whitelist of term names)
2. Reads base plugin `config/terms.php` (full vocabulary) via `UMTD_PATH`
3. For each term in the full vocabulary: inserts it if whitelisted, deletes it if not
4. Registers its `acf-json/` directory as an ACF load path (no save path — ACF Pro required)

**Dependency:** `umt-studio-piroir.php` activation requires `UMTD_PATH` to be defined, meaning `umt-studio` must be active first.

### Base vs Child Boundary

The base plugin owns:
- CPT and taxonomy registration
- ACF field group definitions (all three CPTs)
- Schema.org output
- Agent name logic
- All filter hooks

The child plugin owns:
- Work type whitelist (`config/terms.php`)
- Client-specific ACF field overrides registered via PHP (not JSON)
- Any client-specific hooks

**Rule:** child plugins communicate with the base exclusively through filter hooks. Child plugins never call base plugin functions directly. Base plugin never `require`s anything from child.

### Filter Hook Contracts

```php
// Override or extend CPT definitions
apply_filters( 'umtd_post_types', $post_types )

// Override or extend taxonomy definitions
apply_filters( 'umtd_taxonomies', $taxonomies )

// Override or extend controlled vocabulary terms
apply_filters( 'umtd_terms', $terms )

// Override or extend schema config (planned)
apply_filters( 'umtd_schema_config', $config )
```

Each filter receives the full config array. Child plugin adds to or modifies it. Order of operations: base plugin builds config → filter runs → child plugin modifies → registration proceeds.

### CPT Registration Pattern

CPTs are defined in `config/post-types.php` as a plain data array. `umtd_register_post_types()` reads this array, applies the `umtd_post_types` filter, then registers each enabled type. Registration defaults are set in the function; per-CPT overrides are passed through via `$passthrough_keys`:

```php
$passthrough_keys = array(
    'has_archive',
    'hierarchical',
    'capability_type',
    'map_meta_cap',
    'publicly_queryable',
    'exclude_from_search',
);
```

Merge order: `array_merge( $defaults, $labels, $computed, $overrides )` — overrides always win. To override a default for a specific CPT, add the key to that CPT's config array entry.

### Taxonomy Registration Pattern

Taxonomies are defined in `config/taxonomies.php` as a plain data array. `umtd_register_taxonomies()` reads this array, applies the `umtd_taxonomies` filter, then registers each enabled taxonomy. All registration args are derived directly from the config — there are no passthrough keys; the config is the complete definition.

Config keys per taxonomy:

| Key | Type | Notes |
|---|---|---|
| `enabled` | bool | Skip registration if false |
| `plural` | string | Used in admin labels |
| `singular` | string | Used in admin labels |
| `slug` | string | URL rewrite slug |
| `hierarchical` | bool | true = category-like, false = tag-like |
| `post_types` | array | CPTs this taxonomy attaches to |

### Controlled Vocabulary — Terms

`config/terms.php` in the base plugin defines the full AAT-aligned vocabulary:

```php
return array(
    'umtd_work_type' => array(
        '300041273' => 'Print',
        '300028051' => 'Artist Book',
        // ...
    ),
);
```

Keys are AAT numeric IDs. Values are display names. `umtd_seed_terms()` inserts each term by name and stores the AAT ID as `aat_id` term meta. Term identity is the **name**, not the AAT key — renaming an AAT ID in the config does not break existing term assignments.

`config/terms.php` in the child plugin defines the client whitelist:

```php
return array(
    'umtd_work_type' => array(
        '300041273' => 'Print',
        '300028051' => 'Artist Book',
        // subset only
    ),
);
```

On child plugin activation, terms present in the base vocabulary but absent from the whitelist are deleted from the database. Terms in the whitelist are inserted if missing. Comparison is by **name** (array values), not AAT ID (array keys).

---

## ACF Field Groups

### Location

Base plugin ACF fields live in `acf-json/` and are loaded automatically by ACF's local JSON feature. Field group JSON files are named by ACF's generated key (`group_*.json`).

**No `acf/settings/save_json` filter is registered in the base plugin.** This is intentional. If a save path were registered, ACF would write field group changes back to the base plugin directory on whatever server the edit occurred on — meaning a client install could silently diverge from the canonical repo. Base field groups are read-only on all deployed installs.

**To modify base field groups:** edit on localhost, let ACF save to the local `acf-json/` directory, commit the updated JSON, deploy. Never edit base field groups on a staging or production server.

Child plugin fields are registered via PHP using `acf_add_local_field_group()` on `acf/init`. The child plugin registers an additional `acf-json/` load path but has no save path — ACF Pro is required for automatic JSON saving in child plugins.

### Field Groups

| File | Group | CPT |
|---|---|---|
| `group_69b042aeefe3b.json` | Agent Metadata | `umtd_agents` |
| `group_69b04222d0542.json` | Event Metadata | `umtd_events` |
| `group_69b0409962f04.json` | Work Metadata | `umtd_works` |

### Agent Metadata Fields

| Field | Key | Type | Condition | Notes |
|---|---|---|---|---|
| `agent_type` | — | radio | — | `person` / `organization`, default `person` |
| `name_first` | `field_69bb206cb458b` | text | person only | |
| `name_last` | `field_69bb209ab458d` | text | person only | drives post title and slug |
| `name_display` | `field_69bb22d2064d2` | text | — | auto-populated, all agents |
| `name_variants` | — | textarea | — | one per line |
| `gender` | — | radio | person only | male / female / non-binary / N/A |
| `birth_date` | — | date picker | person only | return format `Y-m-d`, stored `Ymd` |
| `death_date` | — | date picker | person only | return format `Y-m-d`, stored `Ymd` |
| `place_of_birth` | — | textarea | person only | |
| `place_of_death` | — | textarea | person only | |
| `country` | — | text | person only | nationality |
| `biography` | — | textarea | — | |
| `website` | — | url | — | |
| `wikidata_id` | — | text | — | store with or without `Q` prefix, schema.php handles both |
| `ulan_id` | — | text | — | numeric |
| `founding_date` | — | date picker | organization only | |
| `dissolution_date` | — | date picker | organization only | |
| `org_location` | — | text | organization only | |
| `parent_org` | — | relationship | organization only | → `umtd_agents` |

### Work Metadata Fields

| Field | Key | Type | Notes |
|---|---|---|---|
| `agent` | — | relationship | → `umtd_agents`, return format: object, stores serialized array of IDs |
| `agents_artists` | — | relationship | → `umtd_agents`, Piroir hack, pending ACF Pro Repeater |
| `agents_authors` | — | relationship | → `umtd_agents`, Piroir hack, pending ACF Pro Repeater |
| `medium` | — | text | |
| `date_display` | — | text | human-readable, e.g. `ca. 1987–89` |
| `date_earliest` | — | date picker | return format `Y-m-d`, stored `Ymd` |
| `date_latest` | — | date picker | return format `Y-m-d`, stored `Ymd` |
| `dimensions` | — | text | |
| `current_location` | — | relationship | → `umtd_agents` |
| `accession_number` | — | text | |
| `description` | — | textarea | |
| `related_works` | — | relationship | → `umtd_works` |

### Event Metadata Fields

| Field | Key | Type | Notes |
|---|---|---|---|
| `start_date` | — | date picker | return format `Y-m-d`, stored `Ymd` |
| `end_date` | — | date picker | return format `Y-m-d`, stored `Ymd` |
| `organizing_agents` | — | relationship | → `umtd_agents` |
| `participating_agents` | — | relationship | → `umtd_agents` |
| `description` | — | textarea | |
| `poster` | — | image | |
| `event_link` | — | url | |
| `event_type` | — | text | |
| `location` | — | text | |
| `related_works` | — | relationship | → `umtd_works` |

### Agent Name Logic

`umtd_sync_agent_title()` in `includes/agents.php` fires on `acf/save_post` at priority 20:
- Person: sets post title to `Last, First`, slug to `sanitize_title( $title )`
- Organization: sets post title to `name_display`

This makes the post title the sort key, not the display name. **Templates must always use `get_field( 'name_display', $id )` for agent display names, never `get_the_title()`.**

`agent-name.js` watches `name_first` and `name_last` in the admin, auto-populates `name_display` as `First Last`. Once the editor manually edits `name_display`, a `userEdited` flag stops auto-population.

### Date Fields

All ACF date pickers store internally as `Ymd` (e.g. `20221025`). Return format is set to `Y-m-d` in ACF field settings so `get_field()` returns `Y-m-d`. All `meta_query` date comparisons must use:

```php
$today = date( 'Ymd' );
// 'type' => 'CHAR' in meta_query
```

Never use `'type' => 'DATE'` — ACF date fields are stored as `VARCHAR`, not MySQL `DATE` columns.

---

## Data Model

### Entity Types

| CPT | FRBR Equivalent | Schema.org Type |
|---|---|---|
| `umtd_works` | Work | `VisualArtwork` |
| `umtd_agents` | Person/Corporate Body | `Person` / `Organization` |
| `umtd_events` | Event | `ExhibitionEvent` |

### Work–Agent Relationship

Works relate to Agents via the `agent` ACF relationship field. ACF stores this as a serialized PHP array of post ID strings in `wp_postmeta`, e.g. `a:2:{i:0;s:2:"95";i:1;s:2:"57";}`. `get_field('agent')` returns an array of `WP_Post` objects.

To query works by agent, use `meta_query` with `LIKE`:

```php
array(
    'key'     => 'agent',
    'value'   => '"' . $agent_id . '"',
    'compare' => 'LIKE',
)
```

To collect agent IDs from a set of works, loop and call `get_field('agent', $work_id)`, then collect `->ID` from each returned object.

Role (Artist, Author, Printmaker etc.) is currently encoded in parallel fields `agents_artists` / `agents_authors` — a Piroir-specific hack pending ACF Pro Repeater migration.

Correct model (post-migration): a single `agents` repeater with sub-fields `agent` (relationship) + `role` (select from `umtd_agent_role` taxonomy).

Known limitation: `meta_query` with `LIKE` on serialized data does not scale beyond ~1000 records. Acceptable at Piroir scale. Future target: junction table `work_id | agent_id | role_id`.

### Work Type Taxonomy

Works are classified via the `umtd_work_type` taxonomy. Terms are stored by **name** (e.g. `Print`, `Artist Book`), slugified by WordPress (e.g. `print`, `artist-book`). AAT numeric IDs are stored as `aat_id` term meta — they are reference metadata, not identifiers.

To query works by type in templates, use `tax_query` with `'field' => 'slug'`:

```php
array(
    'taxonomy' => 'umtd_work_type',
    'field'    => 'slug',
    'terms'    => 'print',
)
```

To find agents associated with works of a given type: query works by `tax_query`, collect agent IDs via `get_field('agent', $work_id)`, then query agents by `post__in`.

---

## Theme Architecture

### Base Theme Structure

```
umt-design/
├── style.css               — theme header only, no styles
├── functions.php           — enqueue, nav registration, theme support
├── header.php              — opens <html>, <head>, <body>, <header>, <main>
├── footer.php              — closes <main>, <footer>, </body>, </html>
├── index.php               — fallback
├── page.php                — static pages
├── front-page.php          — homepage, current/upcoming events
├── archive.php             — generic fallback archive
├── single.php              — generic fallback single
├── archive-umtd_works.php
├── archive-umtd_agents.php
├── archive-umtd_events.php
├── single-umtd_works.php
├── single-umtd_agents.php
├── single-umtd_events.php
├── parts/
│   ├── card-work.php       — <li> card for works
│   ├── card-agent.php      — <li> card for agents
│   └── card-event.php      — <li> card for events
├── templates/
│   ├── events-archive.php  — Events page template
│   ├── prints-archive.php  — Prints page template
│   ├── books-archive.php   — Books page template
│   └── artists-archive.php — Artists page template
└── assets/
    └── css/
        └── main.css
```

### Page Templates vs CPT Archives

WordPress CPT archives (`archive-umtd_*.php`) are available at their registered slug URLs but are not used for primary navigation. Client nav pages are WordPress pages with custom page templates (`templates/*.php`) that run their own `WP_Query` internally. This decouples URL structure from CPT registration and allows editorial copy alongside archive grids.

`umtd_events` has `'has_archive' => false` in `config/post-types.php` to prevent a URL conflict between the CPT archive and the `/events/` WordPress page.

### Semantic HTML Conventions

- Archive grids: `<ul class="archive-grid">`, cards are `<li>`
- Card titles: `<h3>` (archive `<h1>` is page title, `<h2>` reserved for section headings)
- Metadata on singles: `<dl>/<dt>/<dd>` — `<dt>` labels present in markup, hidden by CSS
- Agent/participant lists: `<ul>/<li>`
- Images: always wrapped in `<figure>`
- Dates: always `<time datetime="Y-m-d">`
- Nav filters: `<nav>` with `<ul>/<li>/<a>`, `aria-current="page"` on active item
- `<main>` opens in `header.php`, closes in `footer.php` — no outer wrapper divs in templates

### URL Architecture

| URL | Template | Query |
|---|---|---|
| `/` | `front-page.php` | Current/upcoming events |
| `/events/` | `templates/events-archive.php` | All events grouped by year; ticker of current + upcoming |
| `/prints/` | `templates/prints-archive.php` | Agents with works of type `print`, split persons/orgs |
| `/books/` | `templates/books-archive.php` | Agents with works of type `artist-book`, split authors/artists |
| `/artists/` | `templates/artists-archive.php` | All agents, split persons/orgs, alphabetical |
| `/studio/` | `page.php` | Static editorial page |
| `/works/{slug}/` | `single-umtd_works.php` | Single work |
| `/events/{slug}/` | `single-umtd_events.php` | Single event |
| `/agents/{slug}/` | `single-umtd_agents.php` | Single agent |

---

## Versioning

Version is defined once in the plugin header and as a constant:

```php
// Plugin header
// Version: 0.1.0

// Bootstrap
define( 'UMTD_VERSION', '0.1.0' );
```

Semantic versioning: `MAJOR.MINOR.PATCH`
- `MAJOR` — breaking change to filter hook signatures or ACF field key renames
- `MINOR` — new feature, backwards compatible
- `PATCH` — bugfix

Stay at `0.x` until the base plugin API (filter hooks, field keys) is stable. Tag releases with `git tag v0.1.0`. Child plugins should document which base plugin version they were built against.

---

## Deployment (Planned)

- **Dev:** localhost, Gentoo Linux, OpenRC, nginx, PHP 8.5, MariaDB
- **Staging:** EC2 instance, mirrors production (not yet configured)
- **Production:** EC2 instance, AWS SSM access

CI/CD target: GitHub Actions → SSH → EC2 `git pull`. No build step required. Staging environment required before production launch.

---

## Known Technical Debt

See `DEFERRED.md` for full register. Primary items:

- Agent role model — `agents_artists`/`agents_authors` hack, pending ACF Pro Repeater
- Work–Agent junction table normalization
- Auto-populate agent fields from ULAN/Wikidata at data entry
- Config-driven schema.org engine for all CPTs
- Schema output for `umtd_events` and `umtd_agents` not written
- Uninstall hook leaves orphaned data
- `wp i18n make-pot` not generated

