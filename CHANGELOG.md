# Changelog

All notable changes to this project are documented here.
Format: [Conventional Commits](https://www.conventionalcommits.org/). Versions follow [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### BUSINESS.md

- feat(business): establish GPL as licensing model for umt-studio and umt-design
- feat(business): collapse service lines — remove standalone sysadmin and IA; absorb into onboarding fee
- feat(business): define onboarding fee — public floor \$5,000 CAD, confidential minimum \$3,500 CAD, assessed quote ceiling, rationale documented
- feat(business): define data import pricing — technical rate \$150/hr, archival research rate \$250/hr, floor \$1,500 CAD, T&M with client-approved ceiling, scoping assessment requirement
- feat(business): define monthly subscription tiers — Standard \$250/50GB, Plus \$350/150GB, Pro \$500/500GB; scope identical across tiers; rationale documented
- feat(business): define add-on structure — newsletter and ecommerce available on any tier, pricing TBD pending operational experience
- feat(business): document infrastructure cost basis — EC2 ~\$15 CAD/month, S3 ~\$0.03/GB; monthly fee priced on time not infrastructure
- feat(business): add MAP grant context — Collections Management component, 75% coverage up to \$400,000, November 1 deadline, April–March fiscal year
- feat(business): add competitive benchmarks — LibraryHost Omeka Standard ~\$88 CAD/month as reference ceiling for monthly subscription positioning
- feat(business): add contract clause requirements — support scope limitation, limitation of liability, data portability, import completeness language, T&M ceiling approval
- feat(business): update §9.1 IP — GPL decision recorded, proprietary methodology distinction documented
- feat(business): update §8.3 cost structure — AWS line items populated with real numbers
- chore(business): update revision date to 2026-03-26
### umt-studio-piroir

### umt-studio

- docs: add three-year platform roadmap and expand business plan competitive positioning
- feat(business): fill in §8.3 fixed costs — remove E&O line, add Cloudflare (\$0), Claude (~\$20), domains (~\$2), total ~\$37/month
- feat(business): fill in §8.4 revenue projections — conservative and realistic Y1/Y2/Y3 scenarios with assumptions documented
- feat(business): fill in §8.5 break-even analysis — target draw ~\$4,400/month gross, ~30 billable hours at \$150/hr
- feat(business): fill in §8.2 web design rate — \$200 CAD/hr
- feat(business): remove subcontracting row from variable costs
- feat(business): update §8.2 GST/QST status — not yet registered, threshold monitoring noted
- feat(business): update §12.2 GST/QST — consistent with §8.2
- feat(business): update §12.3 E&O — deferred, limitation of liability clause is operative substitute
- docs(workflow): remove hack language from agents_artists/agents_authors warning, point to ROADMAP.md; remove ACF Pro complaint from field group editing section; remove "free or Pro" from prerequisites
- docs(infrastructure): remove Docker warning, fix stale deploy-* directory listing, fix wrong DB name in backup command, replace "no automated backup" with forward reference to ROADMAP.md pre-contract checklist
- docs(architecture): replace negative framing with forward references to SCHEMA.md and ROADMAP.md — remove unsourced scale claim, remove "hack" language from agents_artists/agents_authors and Work–Agent section, update related_works and location field notes, update translation deferral note
- feat(schema): add SCHEMA.md — custom DB schema v0.3.0-planned; core entity tables, junction tables, vocabulary tables, translation table, child plugin configurability via umtd_schema_tables filter
- feat(roadmap): add ROADMAP.md — replaces DEFERRED.md; active items week-sequenced against Piroir timeline, backlog items grouped by domain
- docs(roadmap): pin pre-contract prerequisites — staging environment, EBS snapshots, contract template, limitation of liability
- docs(roadmap): pin platform development items to client phases — schema implementation in Weeks 1–2, import tooling in Weeks 3–7, schema.org and i18n in Weeks 5–11
- chore(deferred): DEFERRED.md retired — content migrated to ROADMAP.md
- feat(terms): add umtd_event_type and umtd_medium whitelists to config/terms.php
- docs: revise ARCHITECTURE.md — remove inline vocabulary listings, rewrite rule code block, translation model roadmap (moved to DEFERRED.md), metabox suppression code block; consolidate URL table to Theme Architecture section with FR/EN examples; collapse taxonomy query subsections; 491 → 398 lines
- fix(templates): single-umtd_works.php — variable shadowing bug in artists role loop (\$agent->ID → \$artist->ID)
- fix(templates): front-page.php — meta_query type DATE → CHAR for start_date and end_date
- fix(templates): events-archive.php — meta_query type DATE → CHAR for end_date clause
- docs: update ARCHITECTURE.md — revise Work, Event, Image Metadata field tables; add native taxonomy metabox suppression pattern; add umtd_event_type and umtd_medium taxonomy documentation
- docs: update DEFERRED.md — add agent_type as taxonomy, attachment modal field control, alt text auto-generation, medium conditional display, event title sync hook, language switcher and menu URL rewriting, related works field label
- feat(taxonomies): register umtd_event_type taxonomy on umtd_events
- feat(taxonomies): register umtd_medium taxonomy on umtd_works
- feat(terms): add umtd_event_type vocabulary — Exhibition, Opening, Workshop, Performance, Premiere, Fair, Market (AAT-aligned)
- feat(terms): add umtd_medium vocabulary — Intaglio, Relief, Planographic (AAT-aligned)
- feat(i18n): add slug translations for umtd_event_type and umtd_medium
- fix(admin): remove_post_type_support attachment title — moved to includes/admin.php
- fix(admin): remove native taxonomy sidebar metaboxes for umtd_medium and umtd_event_type
- feat(admin): attachment_fields_to_edit — remove caption and description fields, make alt text read-only on full edit screen
- fix(acf): Event Metadata — change event_type from text to taxonomy field (umtd_event_type), change location from text to relationship → umtd_agents, remove poster field (redundant with featured image)
- fix(acf): Work Metadata — remove medium text field, add medium taxonomy field (umtd_medium), move current_location and accession_number to bottom
- feat(acf): Image Metadata — add photographer relationship → umtd_agents, add event relationship → umtd_events, remove rights and credit_line fields, rename image_source → source (VRA Core alignment), set view_type allow_null, set license default to All Rights Reserved
- fix(acf): Agent Metadata — exported with current field definitions
- fix(acf): Piroir Roles — exported with current field definitions
- chore(admin): remove debug error_log from add_meta_boxes hook in includes/admin.php
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
