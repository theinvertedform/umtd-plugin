# Changelog

All notable changes to this project are documented here.
Format: [Conventional Commits](https://www.conventionalcommits.org/). Versions follow [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### umtd (provisioning repo — new)

- fix(scripts): add chown -R \$USER after each local repo clone — prevents git dubious ownership warnings
- feat(scripts): initialize ~/umtd as tracked private GitHub repo theinvertedform/umtd — scripts, templates, client configs, docs
- feat(ci): add GitHub Actions deploy workflow — uploads umtd-remote and templates to S3, triggers umtd-deploy on EC2 via SSM
- feat(infra): add umtd-deploy at /usr/local/bin/umtd-remote — pulls scripts and templates from S3, installs to target paths, cleans up
- feat(infra): bootstrap umtd-deploy to EC2 manually via umt-temp-transfer
- chore(iam): add s3:PutObject, s3:DeleteObject on umt-temp-transfer/deploy/* and s3:ListBucket scoped to deploy/ prefix to GitHubActionsSSMDeploy policy on ec2-github role

### umtd (local provision script — formerly provision)

- chore: rename provision → umtd, symlink ~/.local/bin/umtd → ~/umtd/scripts/umtd
- feat(scripts): extract all heredocs to template files in ~/umtd/templates/ — rendered via envsubst with explicit substitution lists
- refactor(scripts): add require_field helper — collapses repeated yq_get + empty check + error + exit pattern
- refactor(scripts): compute PLUGIN_FUNC and THEME_FUNC explicitly for use in templates
- refactor(scripts): move IAM ARN from hardcoded to defaults.yml as iam_role_arn
- refactor(scripts): replace local_wp_content in defaults.yml with local_wp_root — derive per-client LOCAL_DOMAIN from CLIENT_ID (strips TLD), LOCAL_WEBROOT from LOCAL_WP_ROOT
- refactor(scripts): replace ACTIVE_LANGS paste pipeline with yq join
- refactor(scripts): remove redundant inner gh repo view check from scaffold blocks
- refactor(scripts): replace cd ~ with subshells for git operations
- refactor(scripts): replace mysql with mariadb — retrieve root password from pass via local_db_root_pass_key in defaults.yml
- refactor(scripts): add --skip-check to wp config create — avoids DB connection before MariaDB is provisioned
- refactor(scripts): reorder sections — WP download → wp-config → MariaDB → WP install → clone repos → activate
- feat(scripts): add -y flag for non-interactive invocation
- feat(scripts): add trap ERR — prints script name and line number on failure
- feat(scripts): add sleep 2 after gh repo create — prevents push timing race
- feat(scripts): generate config/terms.php dynamically from work_type_whitelist in config.yml
- feat(scripts): add local WordPress download, wp-config.php, MariaDB DB and user, WP core install
- feat(scripts): add base repo clone check before activation
- feat(scripts): add child repo clone with chown before clone
- feat(scripts): add local nginx config generation from nginx-local.conf.tpl
- feat(scripts): add local /etc/hosts entry
- feat(scripts): pass WP_ADMIN_USER and WP_ADMIN_EMAIL to umtd-remote as args 5 and 6
- fix(scripts): update SSM call — umtd-remote replaces provision-client

### umtd-remote (formerly provision-client)

- chore: rename provision-client → umtd-remote at /usr/local/bin/umtd-remote
- feat(scripts): accept WP_ADMIN_USER and WP_ADMIN_EMAIL as args 5 and 6 — replace hardcoded values
- chore(scripts): normalize indentation to tabs

### umtd templates (new)

- feat(templates): add plugin.php.tpl — child plugin entrypoint
- feat(templates): add plugin-deploy.yml.tpl — child plugin GitHub Actions deploy workflow
- feat(templates): add theme-style.css.tpl — child theme style.css
- feat(templates): add theme-functions.php.tpl — child theme functions.php; dependency handle umtd-theme
- feat(templates): add theme-deploy.yml.tpl — child theme GitHub Actions deploy workflow
- feat(templates): add nginx-local.conf.tpl — local nginx site config; TCP fastcgi 127.0.0.1:9000, no Ubuntu snippets dependency

### umtd-theme

- refactor: rename umt-design → umtd-theme and umt_design → umtd_theme throughout — functions.php, style.css, header.php, all template and archive files

### docs

- docs(infrastructure): update last-updated date to 2026-04-03
- docs(infrastructure): add GitHubActionsSSMDeploy policy detail to IAM section
- docs(infrastructure): add Local Development Environment section — directory structure, OpenRC services, nginx fastcgi config, /etc/hosts, WP-CLI config, local secrets
- docs(infrastructure): update Provisioning section — rename scripts, expand local script description, add -y flag, update config paths to ~/umtd/
- docs(infrastructure): add Provisioning Scripts and Templates section — ~/umtd/ repo structure, template list, EC2 install paths, umtd-deploy bootstrap instructions
- docs(infrastructure): update CI/CD Repos table — add theinvertedform/umtd, remove umtd-plugin-child template entry
- docs(infrastructure): update deploy workflow template reference — umtd replaces provision
- docs(infrastructure): update File Transfer section — note deploy/ prefix reservation
- docs(infrastructure): update Database Import — mysql → mariadb
- docs(workflow): update Provisioning section — umtd replaces provision, ~/umtd/ replaces ~/studio/, add -y flag, expand local provisioning description
- docs(workflow): update After Provisioning — note ACF required on both local and remote
- docs(workflow): split Verify checklist into Remote and Local sections
- docs(workflow): update Child Plugin terms.php section — generated from work_type_whitelist in config.yml, edit in child plugin post-provisioning
- docs(workflow): replace provision references with umtd throughout

### xyla.zone

- feat(terms): update umtd-plugin-xyla whitelist — all xyla work types, event types, and medium terms
- feat(acf): deactivate Piroir Roles field group on xyla.zone
- chore(infra): provision xyla.zone on EC2 — MariaDB database and user, WordPress install, nginx server block, SSL via certbot
- chore(dns): migrate xyla.zone DNS from Netlify to Cloudflare — nameservers updated in Route 53, A record → 52.60.213.8
- docs(infra): fix client deploy process — HTTP-only nginx block first, certbot adds SSL automatically; remove premature SSL directives from step 6

### umtd-plugin-child (formerly umt-studio-piroir)

- chore(ci): comment out on: trigger in deploy workflow — template repo must not fire on push
- chore: rename repo and plugin from piroir to child — fully generic template
- chore: rename umt-studio-piroir.php → umt-studio-child.php
- chore: replace all UMTD_PIROIR_* constants with UMTD_CHILD_*
- chore: replace all umtd_piroir_* functions with umtd_child_*
- feat(ci): add deploy workflow — deploys base plugin then child on push to main

### umtd-theme (formerly umt-design)

- chore: remove deploy workflow — base theme deployed by child repo

### umtd-plugin (formerly umt-studio)

- feat(terms): add Books, Monographs, Articles, Listing to umtd_work_type vocabulary — AAT-aligned where applicable, local key for Listing
- feat(terms): add 35mm, Oil, Acrylic to umtd_medium vocabulary — AAT-aligned
- feat(acf): split Work Metadata field group — strip to universal fields only (agents, dates, description, related_works)
- feat(acf): add Work: Visual Object field group — VRA Core material, measurements, inscription, stylePeriod, textref fields; location rules: painting, drawing, sculpture, photograph, installation
- feat(acf): add Work: Print field group — VRA Core stateEdition fields (edition_size, printer_copies, print_state); location rules: print, photograph
- feat(acf): add Work: Film field group — runtime, format, language, ISAN, country_of_origin; location rules: film, video
- feat(acf): add Work: Bibliographic field group — ISBN, ISSN, DOI, place_of_publication, edition_number, page_count, journal_title, volume, issue, page_range; location rules: books, monographs, articles, artist-book
- feat(acf): add Work: Listing field group — address, tenure_type, listing_status, floor_area, floor_area_unit, rooms, bathrooms; location rule: listing
- infra: add 1G swapfile — prevents OOM cascade on memory pressure spikes
- fix(monitoring): change umt-backup-missing alarm period from 3600 to 86400 — eliminates false alarms between nightly runs
- fix(backup): replace venv aws path with /usr/local/bin/aws in umt-backup script
- fix(logging): correct rsyslog permissions on /var/log/listmonk.log
- chore: remove stale logwatch tmpdir /var/cache/logwatch/logwatch.wBkDOGe0
- chore(logging): add logrotate config for /var/log/umt-backup.log
- feat(scripts): add local provision script at ~/.local/share/bin/provision — reads config.yml and defaults.yml, generates passwords, creates GitHub repos, scaffolds child plugin and theme, triggers remote provision via SSM
- feat(scripts): add remote provision script at /usr/local/bin/provision-client — idempotent MariaDB, WordPress, nginx, SSL, WP-CLI activation via SSM SendCommand
- feat(infra): add ~/studio/scripts/defaults.yml — global provisioning defaults
- feat(infra): add ~/studio/templates/config.default.yml — documented client config template
- chore(infra): install WP-CLI at /usr/local/bin/wp
- chore(infra): install yq via apt
- chore(infra): add IAM inline policy umt-ssm-parameters to UMT.NET-SSM-Role — ssm:GetParameter on /umt/clients/*
- fix(deploy): add chown ubuntu:ubuntu before git pull in /usr/local/bin/deploy — prevents permission denied on unlink during pull
- fix(scripts): replace --allow-root with sudo -u www-data for all WP-CLI calls in provision-client — wp-config.php chmod 640 owned by www-data not readable by root
- fix(scripts): password generation idempotent — skip if already exists in pass
- fix(scripts): scaffold idempotent — skip if repo default branch already exists
- fix(scripts): clean /tmp/{repo} before scaffold to prevent git remote conflict on retry
- fix(scripts): use defaultBranchRef check instead of pushedAt for scaffold idempotency
- chore: replace all instances of umt-studio, umt-studio-child, umt-design, umt-design-child with new names in ARCHITECTURE.md, INFRASTRUCTURE.md, WORKFLOW.md, and individual plugin/theme files
- chore: rename umt-studio → umtd-plugin, umt-design → umtd-theme, umt-studio-child → umtd-plugin-child on GitHub; update remote URLs in all local clones
- chore: update SSM target paths in umtd-plugin-child deploy workflow to reflect new repo names
- fix(ci): remove invalid branches key from workflow_dispatch trigger in umtd-plugin-child
- chore: initialize ~/studio as private business directory — docs, templates, accounting, scripts, clients
- infra: create S3 bucket umt-backups — versioning on, 30-day lifecycle, public access blocked
- infra: attach umt-backups-s3 and umt-cloudwatch-metrics inline policies to UMT.NET-SSM-Role
- infra: configure DLM daily EBS snapshot policy (policy-0fe3e664fbf0c6117) — 7-day retention, vol-0faa0cace0e178afd tagged Backup=daily
- infra: add /usr/local/bin/umt-backup — dynamic mysqldump + pg_dump to S3 with pipefail and CloudWatch BackupSuccess metric
- infra: add /etc/cron.d/umt-backup — 02:00 UTC nightly, logs to /var/log/umt-backup.log
- infra: create SNS topic umt-alerts with confirmed email subscription
- infra: create CloudWatch alarms umt-ec2-system-status (recover), umt-ec2-instance-status (reboot), umt-backup-missing (dead man's switch)
- docs(infra): add IAM, Backup, and Monitoring & Alerting sections to INFRASTRUCTURE.md
- docs(roadmap): mark EBS snapshots, nightly backup, and automated alerting complete in pre-contract checklist and v0.x Infrastructure
- docs(infrastructure): fix client deploy process steps 6 and 7 — create HTTP-only nginx server block first, run certbot second; certbot adds SSL block and HTTPS redirect automatically; remove premature SSL directives from manual steps
- docs(workflow): genericize WORKFLOW.md — remove piroir references, update child plugin and CI/CD instructions
- chore(infrastructure): decommission piroir.umt.world — remove nginx config, SSL cert, MariaDB database and user, WordPress install
- docs(infrastructure): genericize all piroir references to {client} pattern
- docs(infrastructure): remove shlink from services table — internal service, not client-facing
- docs(infrastructure): remove Gotchas section
- docs(infrastructure): condense listmonk and shlink service documentation
- docs(infrastructure): update CI/CD section — document child-triggers-base deploy architecture
- docs(infrastructure): update repos table — reflect umt-studio-child as template repo
- docs(roadmap): remove piroir references — genericize to first client onboarding
- docs(roadmap): add automated alerting to v0.x infrastructure items — deferred
- docs(roadmap): add nightly mysqldump → S3 to v0.x infrastructure items
- chore: remove deploy workflow — base plugin deployed by child repo
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
- docs(architecture): remove DEFERRED.md refs, replace with ROADMAP.md and SCHEMA.md throughout
- docs(architecture): remove self-deprecating language — country field note, version pins on junction table refs
- docs(architecture): add umtd_schema_config filter note — to be documented in SCHEMA.md
- docs(workflow): remove agents_artists/agents_authors warning block — superseded by custom schema
- docs(workflow): align page slug examples to English throughout
- docs(workflow): remove ACF Pro references
- docs(roadmap): add PHP/MariaDB version pinning to pre-contract checklist
- docs(roadmap): add Related Works field label semantics to Weeks 1–2 scoping
- docs(roadmap): add lazy-load JS to v0.x Admin UX
- docs(roadmap): add search/filtering UI to v1.0
- docs(roadmap): add caching and class-based architecture to v3.0 Stabilization
- docs(roadmap): remove Backlog section — all items assigned to versioned milestones
- docs(infrastructure): update last-updated date
- docs(infrastructure): fix deploy script missing code fence
- docs(infrastructure): fix Repos table formatting — add header row
- docs(infrastructure): fix step 8 formatting in Client Deploy Process
- docs(infrastructure): generalize WordPress log path from Piroir-specific to {client}
- chore: delete DEFERRED.md — all items accounted for in ROADMAP.md or SCHEMA.md
- docs(ROADMAP): abstract active engagement track — generic, client-agnostic, week-relative; rename from "Active — Piroir Engagement" to "First Client Onboarding"
- docs(ROADMAP): add current status section — prototype stage, founding client slot, business development phase
- docs(ROADMAP): add social publishing feature block to v2.0 — Meta Graph API, post composer, scheduling queue, engagement analytics
- docs(ROADMAP): add AI-assisted campaign suggestions to v2.x
- docs(ROADMAP): reframe Artsy integration as partner API negotiation at v3.0
- docs(ROADMAP): remove defensive TBD preamble from intro and platform roadmap section header
- docs(ROADMAP): clean backlog — remove retired entries
- docs(BUSINESS): write §1 executive summary
- docs(BUSINESS): write §6.1 founder bio — Uriah Marc Todoroff
- docs(BUSINESS): replace §2.5 history & context — remove personal journaling, replace with single bridging sentence
- docs(BUSINESS): replace §4.2 market context — remove TBD table, qualitative rewrite, clean MAP note
- docs(BUSINESS): commit §4.5 positioning statement — remove draft label
- docs(BUSINESS): fill §5.5 capacity
- docs(BUSINESS): remove §6.2 advisors — no advisors at current stage
- docs(BUSINESS): fill §6.2 hiring plan — replace TBD triggers with concrete phase descriptions
- docs(BUSINESS): fill §7.2 sales process
- docs(BUSINESS): fill §7.3 proposal and contract
- docs(BUSINESS): replace §7.4 client retention TBD metric
- docs(BUSINESS): set web design project minimum — \$3,000 CAD
- docs(BUSINESS): rewrite §9.2 documentation and methodology — remove wishful hedging
- docs(BUSINESS): remove §9.3 client work — duplicate of §12.4
- docs(BUSINESS): consolidate add-on pricing TBD language in §8.1 and §8.2
- docs(BUSINESS): remove MBM low-income threshold reference from §8.5 break-even
- docs(BUSINESS): reframe §12.1 business registration
- docs(BUSINESS): reframe §12.3 insurance — deferred framing replaced with pre-contract requirement
- docs(BUSINESS): fill §12.4 contracts — remove TBD label
- docs(BUSINESS): fill §12.5 banking
- docs(BUSINESS): fix "testing database" and "potential client" language in §3.2 and §10.1
- docs(BUSINESS): update §2.3 vision — rewrite AI campaign assistance line
- docs(BUSINESS): update §3.2 platform roadmap table — add social publishing to v2.0 row
- docs(BUSINESS): update §10.3 and §10.4 milestone targets — social publishing and marketing platform pricing
- docs(BUSINESS): designate BUSINESS.md private — not for public repo
- docs: add README.md — public-facing derived document for umt-studio repo
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
