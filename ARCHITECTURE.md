# UMT Studio — Architecture

## Overview

White-label WordPress CMS for cultural heritage archive clients. Base plugin + base theme pair, extended per client via child plugin + child theme. Manages Works, Agents, Images, and Events as interrelated archival entities aligned with FRBR.

---

## Repository Structure

| Repo | Type | Purpose |
|---|---|---|
| `umt-studio` | Base plugin | CPTs, taxonomies, ACF fields, schema.org, agent logic, i18n routing |
| `umt-design` | Base theme | Semantic HTML templates, BEM CSS, no client branding |
| `umt-studio-{client}` | Child plugin | Work type whitelist, active languages, client-specific ACF overrides |
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
│   ├── post-types.php      — CPT definitions (data only, no slugs)
│   ├── taxonomies.php      — taxonomy definitions (no slugs)
│   ├── terms.php           — full AAT-aligned controlled vocabulary
│   └── i18n.php            — slug translations and language config
├── includes/
│   ├── admin.php           — agent name sync hook, admin enqueue
│   └── schema.php          — schema.org JSON-LD output
├── assets/
│   └── js/
│       └── admin-fields.js — display name autopopulation in admin
└── acf-json/               — ACF field group JSON (base plugin only)

umt-studio-{client}/
├── umt-studio-{client}.php — bootstrap, activation hook, language filter
└── config/
    └── terms.php           — client whitelist: subset of base terms by name
```

### Bootstrap

`UMTD_PATH` and `UMTD_VERSION` are defined at file load time in `umt-studio.php`.

On `plugins_loaded`, child plugins register their `umtd_i18n` filter. This hook is used rather than top-level registration because WordPress plugin load order is not guaranteed — `plugins_loaded` fires after all plugins have loaded regardless of order.

On `init`, `umt-studio.php` registers CPTs (via `umtd_post_types` filter), registers taxonomies (via `umtd_taxonomies` filter), loads `schema.php` and `admin.php`, and registers `acf-json/` as the ACF load path. By the time `init` fires, `plugins_loaded` has already run and all child plugin filters are registered.

On activation, `umt-studio.php` calls `umtd_seed_terms()` — inserts all base vocabulary terms with `aat_id` as term meta.

On activation, child plugin reads its whitelist and the base vocabulary via `UMTD_PATH`, inserts whitelisted terms, and deletes non-whitelisted ones. **Requires `umt-studio` active first per `Requires Plugins` header.**

### Base vs Child Boundary

Child plugins communicate with the base exclusively through filter hooks — no direct function calls in either direction.

### Filter Hook Contracts

```php
apply_filters( 'umtd_post_types', $post_types )      // CPT definitions
apply_filters( 'umtd_taxonomies', $taxonomies )      // taxonomy definitions
apply_filters( 'umtd_terms', $terms )                // controlled vocabulary
apply_filters( 'umtd_i18n', $i18n )                  // language config and slug translations
apply_filters( 'umtd_schema_config', $config )       // schema config (planned)
```

### CPT Registration

Defined in `config/post-types.php` as a data array. Slugs are not defined in this file — they are owned by `config/i18n.php`. Per-CPT overrides accepted via `$passthrough_keys` (`has_archive`, `hierarchical`, `capability_type`, `map_meta_cap`, `publicly_queryable`, `exclude_from_search`). Merge order: `array_merge( $defaults, $labels, $computed, $overrides )` — overrides always win.

`has_archive` is handled separately from passthrough — if `true` or unset it is replaced with the language-prefixed slug string so the archive URL is consistent with the single URL pattern. If explicitly `false` (e.g. `umtd_events`), it is preserved.

### Taxonomy Config Keys

| Key | Type | Notes |
|---|---|---|
| `enabled` | bool | Skip registration if false |
| `plural` | string | Admin labels |
| `singular` | string | Admin labels |
| `hierarchical` | bool | true = category-like |
| `post_types` | array | CPTs this taxonomy attaches to |

Note: `slug` key is absent — slugs are owned by `config/i18n.php`.

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

## Internationalisation

### Overview

URL routing is language-prefixed throughout. All CPT and taxonomy URLs include a language code prefix — bare slugs without a prefix are never registered and will 404. Internal links generated by WordPress (`get_permalink()`, archive links) always include the prefix because the prefix is baked into the registered rewrite slug.

URL pattern: `/{lang}/{slug}/{post-name}/` — e.g. `/fr/artistes/grubisic-katia/`, `/en/artists/grubisic-katia/`.

Translated slugs are used (e.g. `oeuvres` not `works` for French) because the slug itself communicates language. The language prefix is retained for unambiguous routing and `hreflang` clarity, not redundancy — it ensures routing is O(1) at the rewrite layer with no slug lookup required, and future-proofs for languages where slug translation is impractical.

### config/i18n.php

All slug translations and language config live here. This is the only place slugs are defined — `post-types.php` and `taxonomies.php` have no slug keys.

```php
return array(
    'default_lang' => 'en',       // base default — child overrides via umtd_i18n filter
    'languages'    => array( 'en' ), // active languages — child overrides
    'slugs'        => array(
        'umtd_works'     => array( 'en' => 'works',      'fr' => 'oeuvres',    ... ),
        'umtd_agents'    => array( 'en' => 'artists',    'fr' => 'artistes',   ... ),
        'umtd_events'    => array( 'en' => 'events',     'fr' => 'evenements', ... ),
        'umtd_work_type' => array( 'en' => 'work-type',  'fr' => 'type-oeuvre', ... ),
    ),
);
```

Adding a new CPT: add its slug translations here. Adding a new language to the platform: add a column here. Child plugins never modify this file.

### umtd_get_i18n()

```php
function umtd_get_i18n() {
    return apply_filters( 'umtd_i18n', require UMTD_PATH . 'config/i18n.php' );
}
```

Called inside `umtd_register_post_types()` and `umtd_register_taxonomies()`, both hooked to `init`. By `init`, all `plugins_loaded` callbacks have run and child plugin filters are registered.

### Child Plugin Language Declaration

Child plugin declares active languages via `umtd_i18n` filter, registered inside a `plugins_loaded` callback to guarantee `UMTD_PATH` is defined regardless of plugin load order:

```php
add_action( 'plugins_loaded', function() {
    if ( ! defined( 'UMTD_PATH' ) ) {
        // show admin notice, return
    }
    add_filter( 'umtd_i18n', function( $i18n ) {
        $i18n['default_lang'] = 'fr';
        $i18n['languages']    = array( 'fr', 'en' );
        return $i18n;
    } );
} );
```

The child plugin only declares which languages are active — slug translations are defined in the base `config/i18n.php` and apply automatically to all active languages. A language absent from the `languages` array generates no rewrite rules and produces no routes.

### Rewrite Rule Generation

`umtd_register_post_types()` registers the primary slug as `{default_lang}/{base_slug}` — e.g. `fr/artistes`. WordPress uses this for all internally generated URLs.

For each additional active language, supplementary rewrite rules are added:

```php
// Archive: /{lang}/{lang_slug}/
add_rewrite_rule(
    '^en/artists/?$',
    'index.php?post_type=umtd_agents&lang=en',
    'top'
);
// Single: /{lang}/{lang_slug}/{post-name}/
add_rewrite_rule(
    '^en/artists/([^/]+)/?$',
    'index.php?post_type=umtd_agents&name=$matches[1]&lang=en',
    'top'
);
```

The `lang` query var is set in the rewrite rule — templates read it via `get_query_var( 'lang' )`. `lang` is registered as a custom query var in `umt-studio.php`.

### Adding a Language

1. Add slug translations to `umt-studio/config/i18n.php` for all CPTs and taxonomies
2. In the child plugin filter, add the language code to the `languages` array
3. Flush rewrite rules — Settings → Permalinks → Save

No other changes required.

### Translation Model — Current State and Roadmap

Current state: URL routing is language-aware but content is not. Templates do not yet differentiate content by `lang` query var. Bilingual content entry is deferred to the custom DB schema milestone.

Polylang was evaluated and rejected. Its duplicate-post model is incompatible with the FRBR target architecture. Polylang Pro licensing (€99/site) does not scale for a multi-client product. The translation model will be implemented natively as part of the custom schema.

Target: a `umtd_translations` table — `post_id | lang | field_name | value`. Translatable fields (title, description) stored per language. Language-agnostic fields (dates, relationships, dimensions) stored once. `umtd_get_field( $field, $post_id, $lang )` checks translations table first, falls back to postmeta. FRBR framing: a translation is a new Expression of the same Work, not a copy.

---

## ACF Field Groups

Base plugin fields load from `acf-json/`. **No save path is registered in the base plugin** — base field groups are read-only on all deployed installs. To modify: edit on localhost, commit updated JSON, deploy.

Child plugin fields are registered via `acf_add_local_field_group()` on `acf/init`. Child plugin registers an `acf-json/` load path inside the `plugins_loaded` callback (alongside other dependency-sensitive registrations).

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

`admin-fields.js` auto-populates `name_display` as `First Last` from `name_first`/`name_last`. A `userEdited` flag stops auto-population once the editor manually edits `name_display`.

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

Page templates are client-specific — the base theme ships minimal stubs. Child themes override with client-specific layout and design. Pages must be created manually in WP admin with the correct template assigned (or via `wp_insert_post()` on child plugin activation — see DEFERRED.md).

### Semantic HTML Conventions

- Archive grids: `<ul class="archive-grid">`, cards are `<li>`
- Card titles: `<h3>` (`<h1>` = page title, `<h2>` = section headings)
- Single metadata: `<dl>/<dt>/<dd>` — `<dt>` labels in markup, hidden by CSS
- Images: always `<figure>`
- Dates: always `<time datetime="Y-m-d">`
- Nav filters: `<nav><ul><li><a>`, `aria-current="page"` on active item
- `<main>` opens in `header.php`, closes in `footer.php`

### URL Architecture

URLs are language-prefixed throughout. The table below shows Piroir (FR primary) as the concrete example.

| URL | Template | Query |
|---|---|---|
| `/` | `front-page.php` | Current/upcoming events |
| `/fr/evenements/{slug}/` | `single-umtd_events.php` | Single event |
| `/en/events/{slug}/` | `single-umtd_events.php` | Single event (EN) |
| `/fr/artistes/{slug}/` | `single-umtd_agents.php` | Single agent |
| `/en/artists/{slug}/` | `single-umtd_agents.php` | Single agent (EN) |
| `/fr/oeuvres/{slug}/` | `single-umtd_works.php` | Single work |
| `/en/works/{slug}/` | `single-umtd_works.php` | Single work (EN) |
| `/events/` | `templates/events-archive.php` | All events by year; current/upcoming ticker |
| `/prints/` | `templates/prints-archive.php` | Agents with works of type `print` |
| `/books/` | `templates/books-archive.php` | Agents with works of type `artist-book` |
| `/artists/` | `templates/artists-archive.php` | All agents, persons/orgs split |
| `/studio/` | `page.php` | Static editorial page |

Page template URLs (`/events/`, `/prints/`, etc.) are WordPress pages — their slugs are set in WP admin and are not language-prefixed in the current implementation. CPT single URLs are language-prefixed via the i18n rewrite system.

---

## Versioning

Version defined in plugin header and as `UMTD_VERSION` constant. Semantic versioning:

- `MAJOR` — breaking change to filter hook signatures or ACF field key renames
- `MINOR` — new feature, backwards compatible
- `PATCH` — bugfix

Stay at `0.x` until filter hooks and field keys are stable. Tag releases `git tag v0.x.x`. Child plugins should document which base version they target.

