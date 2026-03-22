# UMT Studio — Architecture

## Overview

White-label WordPress CMS for cultural heritage archive clients. Base plugin + base theme pair, extended per client via child plugin + child theme. Manages Works, Agents, Images, and Events as interrelated archival entities aligned with FRBR.

---

## Repository Structure

| Repo | Type | Purpose |
|---|---|---|
| `umt-studio` | Base plugin | CPTs, taxonomies, ACF fields, schema.org, agent logic |
| `umt-design` | Base theme | Semantic HTML templates, BEM CSS, no client branding |
| `umt-studio-{client}` | Child plugin | Work type whitelist, client-specific ACF overrides |
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
│   ├── post-types.php      — CPT definitions (data only)
│   ├── taxonomies.php      — taxonomy definitions
│   └── terms.php           — full AAT-aligned controlled vocabulary
├── includes/
│   ├── agents.php          — agent name sync hook, admin enqueue
│   └── schema.php          — schema.org JSON-LD output
├── assets/
│   └── js/
│       └── agent-name.js   — display name autopopulation in admin
└── acf-json/               — ACF field group JSON (base plugin only)

umt-studio-{client}/
├── umt-studio-{client}.php — bootstrap, activation hook, ACF JSON path
└── config/
    └── terms.php           — client whitelist: subset of base terms by name
```

### Bootstrap

On `init`, `umt-studio.php` defines `UMTD_PATH`, registers CPTs (via `umtd_post_types` filter), registers taxonomies (via `umtd_taxonomies` filter), loads `schema.php` and `agents.php`, and registers `acf-json/` as the ACF load path.

On activation, `umt-studio.php` calls `umtd_seed_terms()` — inserts all base vocabulary terms with `aat_id` as term meta.

On activation, child plugin reads its whitelist and the base vocabulary via `UMTD_PATH`, inserts whitelisted terms, and deletes non-whitelisted ones. **Requires `umt-studio` active first.**

### Base vs Child Boundary

Child plugins communicate with the base exclusively through filter hooks — no direct function calls in either direction.

### Filter Hook Contracts

```php
apply_filters( 'umtd_post_types', $post_types )      // CPT definitions
apply_filters( 'umtd_taxonomies', $taxonomies )      // taxonomy definitions
apply_filters( 'umtd_terms', $terms )                // controlled vocabulary
apply_filters( 'umtd_schema_config', $config )       // schema config (planned)
```

### CPT Registration

Defined in `config/post-types.php` as a data array. Per-CPT overrides accepted via `$passthrough_keys` (`has_archive`, `hierarchical`, `capability_type`, `map_meta_cap`, `publicly_queryable`, `exclude_from_search`). Merge order: `array_merge( $defaults, $labels, $computed, $overrides )` — overrides always win.

### Taxonomy Config Keys

| Key | Type | Notes |
|---|---|---|
| `enabled` | bool | Skip registration if false |
| `plural` | string | Admin labels |
| `singular` | string | Admin labels |
| `slug` | string | URL rewrite slug |
| `hierarchical` | bool | true = category-like |
| `post_types` | array | CPTs this taxonomy attaches to |

### Controlled Vocabulary — Terms

`config/terms.php` in the base plugin:

```php
return array(
    'umtd_work_type' => array(
        '300041273' => 'Print',
        '300028051' => 'Artist Book',
        // ...
    ),
);
```

Keys are AAT numeric IDs; values are display names. Term identity is the **name** — renaming a term in config breaks existing assignments. The child whitelist uses the same structure; comparison on activation is by name (values), not AAT ID (keys).

---

## ACF Field Groups

Base plugin fields load from `acf-json/`. **No save path is registered in the base plugin** — base field groups are read-only on all deployed installs. To modify: edit on localhost, commit updated JSON, deploy.

Child plugin fields are registered via `acf_add_local_field_group()` on `acf/init`. Child plugin registers an `acf-json/` load path but no save path (ACF Pro required for that).

### Field Groups

| File | Group | CPT |
|---|---|---|
| `group_69b042aeefe3b.json` | Agent Metadata | `umtd_agents` |
| `group_69b04222d0542.json` | Event Metadata | `umtd_events` |
| `group_69b0409962f04.json` | Work Metadata  | `umtd_works`  |
| `group_69b9919e17cbb.json` | Image Metadata | attachments   |

### Agent Metadata Fields

| Field | Key | Type | Condition | Notes |
|---|---|---|---|---|
| `agent_type` | — | radio | — | `person` / `organization`, default `person` |
| `name_first` | `field_69bb206cb458b` | text | person | |
| `name_last` | `field_69bb209ab458d` | text | person | drives post title and slug |
| `name_display` | `field_69bb22d2064d2` | text | — | auto-populated, all agents |
| `name_variants` | — | textarea | — | one per line |
| `gender` | — | radio | person | male / female / non-binary / N/A |
| `birth_date` | — | date picker | person | stored `Ymd`, returns `Y-m-d` |
| `death_date` | — | date picker | person | stored `Ymd`, returns `Y-m-d` |
| `place_of_birth` | — | textarea | person | |
| `place_of_death` | — | textarea | person | |
| `country` | — | text | person | nationality |
| `biography` | — | textarea | — | |
| `website` | — | url | — | |
| `wikidata_id` | — | text | — | with or without `Q` prefix, schema.php handles both |
| `ulan_id` | — | text | — | numeric |
| `founding_date` | — | date picker | organization | stored `Ymd`, returns `Y-m-d` |
| `dissolution_date` | — | date picker | organization | stored `Ymd`, returns `Y-m-d` |
| `org_location` | — | text | organization | |
| `parent_org` | — | relationship | organization | → `umtd_agents` |

### Work Metadata Fields

| Field | Key | Type | Notes |
|---|---|---|---|
| `agent` | — | relationship | → `umtd_agents`, returns array of `WP_Post` objects |
| `agents_artists` | — | relationship | → `umtd_agents`, Piroir hack — see DEFERRED.md |
| `agents_authors` | — | relationship | → `umtd_agents`, Piroir hack — see DEFERRED.md |
| `medium` | — | text | |
| `date_display` | — | text | human-readable, e.g. `ca. 1987–89` |
| `date_earliest` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `date_latest` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `dimensions` | — | text | |
| `current_location` | — | relationship | → `umtd_agents` |
| `accession_number` | — | text | |
| `description` | — | textarea | |
| `related_works` | — | relationship | → `umtd_works` |

### Event Metadata Fields

| Field | Key | Type | Notes |
|---|---|---|---|
| `start_date` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `end_date` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `organizing_agents` | — | relationship | → `umtd_agents` |
| `participating_agents` | — | relationship | → `umtd_agents` |
| `description` | — | textarea | |
| `poster` | — | image | |
| `event_link` | — | url | |
| `event_type` | — | text | |
| `location` | — | text | |
| `related_works` | — | relationship | → `umtd_works` |

### Image Metadata Fields

Attaches to all WordPress attachments (`attachment == all`) — not a CPT. Fields are accessible via `get_field( 'field_name', $attachment_id )`.

| Field | Key | Type | Notes |
|---|---|---|---|
| `view_type` | — | select | recto / verso / detail / installation view / exhibition view / before treatment / after treatment |
| `rights` | — | text | default `©` |
| `rights_holder` | — | text | |
| `license` | — | select | All Rights Reserved / CC BY / CC BY-SA / CC BY-NC / Public Domain |
| `credit_line` | — | text | |
| `image_source` | — | text | |
| `image_date` | — | date picker | stored `Ymd`, returns `Y-m-d` |
| `related_work` | — | relationship | → `umtd_works` |

### Agent Name Logic

`umtd_sync_agent_title()` fires on `acf/save_post` at priority 20. Person: post title → `Last, First`, slug → `sanitize_title( $title )`. Organization: post title → `name_display`. Post title is the sort key.

**Templates must always use `get_field( 'name_display', $id )` for agent display names, never `get_the_title()`.**

`agent-name.js` auto-populates `name_display` as `First Last` from `name_first`/`name_last`. A `userEdited` flag stops auto-population once the editor manually edits `name_display`.

### Date Fields

ACF date pickers store as `Ymd` VARCHAR. `get_field()` returns `Y-m-d` per ACF field settings. All `meta_query` date comparisons must use `'type' => 'CHAR'` — never `'type' => 'DATE'`.

---

## Data Model

### Entity Types

| CPT | FRBR Equivalent | Schema.org Type |
|---|---|---|
| `umtd_works` | Work | `VisualArtwork` |
| `umtd_agents` | Person/Corporate Body | `Person` / `Organization` |
| `umtd_events` | Event | `ExhibitionEvent` |

### Work–Agent Relationship

Works relate to Agents via the `agent` ACF relationship field, stored as a serialized PHP array of post IDs in `wp_postmeta`. `get_field('agent')` returns an array of `WP_Post` objects.

Query works by agent:

```php
array(
    'key'     => 'agent',
    'value'   => '"' . $agent_id . '"',
    'compare' => 'LIKE',
)
```

`meta_query` with `LIKE` on serialized data does not scale beyond ~1000 records. Future target: junction table `work_id | agent_id | role_id`.

Agent role is currently encoded in parallel fields `agents_artists` / `agents_authors` — a Piroir-specific hack pending ACF Pro Repeater migration (see `DEFERRED.md`).

### Work Type Taxonomy

Works classified via `umtd_work_type`. Terms stored by name, slugified by WordPress. AAT IDs stored as `aat_id` term meta (reference only, not identifiers).

Query works by type:

```php
array(
    'taxonomy' => 'umtd_work_type',
    'field'    => 'slug',
    'terms'    => 'print',
)
```

To find agents associated with works of a given type: `tax_query` → collect agent IDs via `get_field('agent', $work_id)` → `post__in`.

---

## Theme Architecture

### File Structure

```
umt-design/
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
│   ├── prints-archive.php
│   ├── books-archive.php
│   └── artists-archive.php
└── assets/css/main.css
```

### Page Templates vs CPT Archives

CPT archives (`archive-umtd_*.php`) exist but are not used for primary navigation. Client nav pages are WordPress pages with custom page templates (`templates/*.php`) running their own `WP_Query`. This decouples URL structure from CPT registration.

`umtd_events` has `'has_archive' => false` to prevent URL conflict with the `/events/` page.

### Semantic HTML Conventions

- Archive grids: `<ul class="archive-grid">`, cards are `<li>`
- Card titles: `<h3>` (`<h1>` = page title, `<h2>` = section headings)
- Single metadata: `<dl>/<dt>/<dd>` — `<dt>` labels in markup, hidden by CSS
- Images: always `<figure>`
- Dates: always `<time datetime="Y-m-d">`
- Nav filters: `<nav><ul><li><a>`, `aria-current="page"` on active item
- `<main>` opens in `header.php`, closes in `footer.php`

### URL Architecture

| URL | Template | Query |
|---|---|---|
| `/` | `front-page.php` | Current/upcoming events |
| `/events/` | `templates/events-archive.php` | All events by year; current/upcoming ticker |
| `/prints/` | `templates/prints-archive.php` | Agents with works of type `print`, persons/orgs split |
| `/books/` | `templates/books-archive.php` | Agents with works of type `artist-book`, authors/artists split |
| `/artists/` | `templates/artists-archive.php` | All agents, persons/orgs split, alphabetical |
| `/studio/` | `page.php` | Static editorial page |
| `/works/{slug}/` | `single-umtd_works.php` | Single work |
| `/events/{slug}/` | `single-umtd_events.php` | Single event |
| `/agents/{slug}/` | `single-umtd_agents.php` | Single agent |

---

## Versioning

Version defined in plugin header and as `UMTD_VERSION` constant. Semantic versioning:

- `MAJOR` — breaking change to filter hook signatures or ACF field key renames
- `MINOR` — new feature, backwards compatible
- `PATCH` — bugfix

Stay at `0.x` until filter hooks and field keys are stable. Tag releases `git tag v0.x.x`. Child plugins should document which base version they target.
