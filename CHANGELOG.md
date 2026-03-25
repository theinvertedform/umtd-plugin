# Changelog

All notable changes to this project are documented here.
Format: [Conventional Commits](https://www.conventionalcommits.org/). Versions follow [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### umt-studio

- feat(acf): Image Metadata — add photographer relationship field → umtd_agents
- feat(acf): Image Metadata — add event relationship field → umtd_events
- fix(acf): Image Metadata — remove rights field (bare © default, meaningless alone)
- fix(acf): Image Metadata — remove credit_line field (replaced by structured rights + photographer fields)
- fix(acf): Image Metadata — rename image_source → source, aligns with VRA Core source element
- fix(acf): Image Metadata — set view_type allow_null: 1, remove default value
- fix(acf): Image Metadata — set license default to All Rights Reserved
- feat(acf): Work Metadata — add dimensions_h, dimensions_w, dimensions_unit fields replacing dimensions text field
- feat(admin): remove_post_type_support attachment title — title field no longer editable on attachment edit screen
- feat(admin): attachment_fields_to_edit filter — remove caption and description fields, make alt text read-only
- chore(admin): move remove_post_type_support call to includes/admin.php
- docs: add BUSINESS.md — founding business plan, sole proprietorship QC, all sections present, financials TBD
- fix(templates): front-page.php — change meta_query type DATE → CHAR for start_date and end_date
- fix(templates): single-umtd_events.php — replace get_the_title() with get_field('name_display') for organizers and participants
- fix(templates): front-page.php — replace get_the_title() with get_field('name_display') for event agents
- fix(parts): card-event.php — replace get_the_title() with get_field('name_display') for organizers
- feat(i18n): add config/i18n.php — centralized slug translations and language config
- feat(i18n): add umtd_get_i18n() and umtd_i18n filter hook
- refactor(cpts): umtd_register_post_types() — language-prefixed primary slugs, supplementary rewrite rules per active language
- refactor(taxonomies): umtd_register_taxonomies() — same i18n pattern
- fix(piroir): move umtd_i18n and acf/settings/load_json filters into plugins_loaded — fixes load order race condition with UMTD_PATH
- feat(piroir): declare active languages via umtd_i18n filter — fr primary, en supplementary
- chore(cpts): remove slug key from config/post-types.php — owned by config/i18n.php
- chore(infra): audit ec2-s3 inline policy on UMT.NET-SSM-Role — already scoped to umt-temp-transfer, added PutObject and DeleteObject for bidirectional transfer
- chore(ci): replace per-service deploy scripts with generic /usr/local/bin/deploy <path>
- chore(ci): add deploy workflows to umt-studio, umt-studio-piroir, umt-design
- fix(ci): update ec2-github IAM trust policy — was hardcoded to news.umt.world, now theinvertedform/*
- fix(schema): replace get_the_title() with get_field('name_display') for agent names
- docs(schema): add PHPDoc to includes/schema.php
- docs(piroir): add file-level docblock to config/terms.php
- style(piroir): normalize indentation to tabs in config/terms.php
- fix(piroir): add UMTD_PATH guard with admin notice — fatal error on missing dependency now fails visibly
- fix(piroir): add Requires Plugins header field — WordPress 6.5+ enforces activation order
- fix(piroir): add strict comparison to in_array() in umtd_piroir_activate()
- docs(piroir): add PHPDoc to umt-studio-piroir.php
- style(piroir): normalize indentation to tabs
- docs(terms): add file-level docblock to config/terms.php
- style(terms): normalize indentation to tabs in config/terms.php
- docs(taxonomies): add file-level docblock to config/taxonomies.php
- chore(cpts): remove commented-out show_in_menu lines from config/post-types.php
- docs(cpts): add file-level docblock and inline comments to config/post-types.php
- style(cpts): normalize indentation to tabs in config/post-types.php
- docs(admin): add JSDoc to assets/js/admin-fields.js — field key coupling, nameUserEdited behaviour, display vs sort-key distinction
- docs(admin): add PHPDoc to includes/admin.php — recursion guard explanation, sort-key rule, fallback logic
- fix(plugin): remove redundant anonymous activation hook — umtd_activate() is a strict superset
- docs(plugin): add PHPDoc to umt-studio.php — all functions, constants, inline rules
- fix(plugin): update plugin header description

## [0.2.0] — 2026-03-22

### umt-studio

- fix(acf): remove save_json filter from umt-studio.php — base field groups read-only on deployed installs
- fix(piroir): remove duplicate base plugin ACF JSON files from umt-studio-piroir/acf-json/
- fix(plugin): rename require_once includes/agents.php → includes/admin.php in umt-studio.php
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

- fix(templates): events-archive.php — change meta_query type DATE → CHAR for start_date and end_date
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

- docs: revise `ARCHITECTURE.md` — add bootstrap sequence, full field tables, ACF save path discipline, query patterns
- docs: add WORKFLOW.md — new client onboarding checklist with hung lanterns
- docs: add CHANGELOG.md
- docs: add `DEFERRED.md` — custom date entry UI, agent role model, junction table, full deferred register

### chore

- chore: split wp-content monorepo into four independent repos via git filter-repo
- chore: update wp-content/.gitignore — exclude split repos and third-party plugins
- chore: set up GitHub remotes for umt-studio, umt-studio-piroir, umt-design

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

