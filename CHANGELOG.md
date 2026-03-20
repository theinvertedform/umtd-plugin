# Changelog

All notable changes to this project are documented here.
Format: [Conventional Commits](https://www.conventionalcommits.org/). Versions follow [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### umt-studio

- feat(agents): add `name_first`, `name_last`, `name_display` fields to Agent Metadata ACF group
- feat(agents): `umtd_sync_agent_title()` — sets post title to `Last, First` for persons, `name_display` for organizations on `acf/save_post`
- refactor(admin): rename `includes/agents.php` → `includes/admin.php`
- refactor(admin): rename `assets/js/agent-name.js` → `assets/js/admin-fields.js`
- refactor(admin): modular admin script enqueue — driven by `$umtd_admin_script_post_types` config array, enqueues on both `umtd_agents` and `umtd_works` screens
- fix(admin): replace `var` with `const`/`let` throughout `admin-fields.js`
- fix(admin): strip date auto-population from `admin-fields.js` — deferred to custom date entry UI
- feat(plugin): add `UMTD_VERSION` constant to `umt-studio.php`
- refactor(cpts): config-driven `umtd_register_post_types()` — defaults in function, per-CPT overrides via `$passthrough_keys`, `array_merge` order guarantees overrides win
- fix(cpts): `has_archive => false` on `umtd_events` — prevents URL conflict with `/events/` page
- fix(meta_query): change `'type' => 'DATE'` to `'type' => 'CHAR'` throughout — ACF date fields are VARCHAR not MySQL DATE columns
- fix(piroir): rename `$active_ids` → `$whitelisted_names` in `umtd_piroir_activate()`, add `array_values()` to ensure flat array for `in_array`
- fix(db): manual postmeta fix on post 94 — `meta_key` corrected from `_agents` to `_agent`

### umt-design

- feat(theme): `functions.php` — register `primary` and `footer` nav menu locations
- feat(theme): `functions.php` — move `add_theme_support` calls to `after_setup_theme` hook
- feat(theme): `functions.php` — replace hardcoded version string with `wp_get_theme()->get('Version')`
- feat(theme): `functions.php` — add `UMTD_THEME_VERSION` constant
- refactor(templates): rewrite `header.php` — semantic, opens `<main>`
- refactor(templates): rewrite `footer.php` — closes `<main>`
- refactor(templates): rewrite `archive-umtd_works.php` — semantic, main query
- refactor(templates): rewrite `archive-umtd_agents.php`
- refactor(templates): rewrite `archive-umtd_events.php`
- refactor(templates): rewrite `single-umtd_works.php` — semantic, `name_display` for agents
- refactor(templates): rewrite `single-umtd_agents.php` — semantic, `name_display` for h1
- refactor(templates): rewrite `single-umtd_events.php` — semantic
- refactor(parts): rewrite `parts/card-work.php` — `<li>`, `name_display` for agents
- refactor(parts): rewrite `parts/card-agent.php` — `<li>`, `name_display` for h3
- refactor(parts): rewrite `parts/card-event.php` — `<li>`
- feat(templates): add `page.php`
- feat(templates): add `front-page.php` — current/upcoming events
- feat(templates): add `templates/events-archive.php` — events by year, programming ticker
- feat(templates): add `templates/artists-archive.php` — all agents, persons and organizations in separate sections, alphabetical
- feat(templates): add `templates/prints-archive.php` — agents with works of type `print`, persons and organizations in separate sections
- feat(templates): add `templates/books-archive.php` — agents with works of type `artist-book`, authors and artists in separate sections
- fix(templates): `single-umtd_works.php` — replace `get_the_title()` with `get_field('name_display')` for all agent references
- fix(templates): `single-umtd_agents.php` — replace `the_title()` with `get_field('name_display')`
- fix(parts): `card-agent.php` — replace `the_title()` with `get_field('name_display')`
- fix(parts): `card-work.php` — replace `get_the_title( $a->ID )` with `get_field('name_display', $a->ID)`

### docs

- docs: add `ARCHITECTURE.md` — full system architecture, bootstrap sequence, field tables, query patterns, term identity rules, URL architecture
- docs: add `DEFERRED.md` — custom date entry UI, agent role model, junction table, full deferred register

---

## [0.1.0] — 2026-03-20

### umt-studio

- feat: register `umtd_works`, `umtd_agents`, `umtd_events` CPTs
- feat: register `umtd_work_type` taxonomy on `umtd_works`
- feat: ACF field groups — Agent Metadata, Work Metadata, Event Metadata, Image/Attachment Metadata
- feat: `umtd_seed_terms()` — seeds AAT-aligned work type vocabulary on activation, stores `aat_id` as term meta
- feat: `includes/schema.php` — schema.org JSON-LD (`VisualArtwork`) on `umtd_works` singles
- feat: ACF local JSON load/save paths registered in `umt-studio.php`
- feat: `umt-studio-piroir` child plugin — work type whitelist, ACF JSON load path

