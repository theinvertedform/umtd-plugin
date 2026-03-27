# UMT Studio — Roadmap

This document is the authoritative record of planned work. It replaces `DEFERRED.md`. Items are either sequenced against the active client timeline (Active) or held in the Backlog without a delivery date.

The client-facing Piroir project timeline runs 9–16 weeks from contract signing. Platform development items are sequenced to land before the client phase that depends on them. The two timelines are parallel and coordinated — platform work is not deferred until after the client engagement, it is built into it.

---

## Active — Piroir Engagement

Week numbers are relative to contract signing (Week 0).

### Pre-contract (before Week 0)

These items are prerequisites for signing. No contract is issued until they are complete.

- [ ] Staging environment live — `staging.piroir.umt.world`, same EC2 host, separate nginx server block, separate MariaDB database
- [ ] EBS snapshots configured — automated daily, 7-day retention, via AWS Data Lifecycle Manager
- [ ] Limitation of liability clause confirmed in contract template
- [ ] Contract template finalized — scope, deliverables, payment schedule, IP ownership, data portability, T&M ceiling approval language

### Weeks 1–2 — Scoping and Schema

Client phase: Scoping assessment and design brief.

- [ ] `SCHEMA.md` finalized — table definitions confirmed with Piroir data model in mind
- [ ] `umtd_register_tables()` — base plugin registers all custom tables via `dbDelta()` on activation; child plugin declares active subset via `umtd_schema_tables` filter
- [ ] `acf/save_post` intercept hooks — redirect ACF writes to custom tables for all intercepted fields; suppress ACF's own postmeta writes for those fields
- [ ] `umtd_get_field( $field, $post_id, $lang = null )` — reads from custom tables; falls back to `get_field()` for any field not yet covered; passes `$lang` to `umtd_translations`
- [ ] `umtd_roles` seeded — initial vocabulary: Artist, Author, Printer, Publisher, Photographer, Curator
- [ ] `umtd_view_types` seeded — Recto, Verso, Detail, Installation View, Exhibition View, Before Treatment, After Treatment
- [ ] `umtd_series` taxonomy registered on `umtd_works`
- [ ] `agent_type` ENUM column — Person, Organization, Venue; venues excluded from public archive listings by default
- [ ] All templates updated — replace all remaining `get_field()` calls with `umtd_get_field()`; no direct ACF calls in any theme template
- [ ] Nightly `mysqldump` → S3 (`umt-temp-transfer` or dedicated client bucket) — automated via cron; client-accessible from WP admin UI
- [ ] `staging.piroir.umt.world` SSL — certbot, added to existing cert or new cert

### Weeks 3–7 — Data Import

Client phase: Data import.

- [ ] WP-CLI import scripts — field-mapped CSV → custom tables for Works, Agents, Events
- [ ] `umtd_work_media` population — attachment upload, `umtd_work_media` junction table population, `view_type_id` assignment
- [ ] Data entry and QA on staging — all records entered and reviewed before staging review phase
- [ ] `edition_size` and `printer_copies` fields — data entered for all print Works
- [ ] `umtd_series` terms created and assigned

### Weeks 5–11 — Child Theme

Client phase: Child theme design and implementation. Overlaps with data import.

- [ ] `umt-design-piroir` repo created — child theme, client typography, colour, branding
- [ ] All archive templates updated to use `umtd_get_field()` throughout
- [ ] Schema.org engine extended — `umtd_events` → `ExhibitionEvent`; `umtd_agents` → `Person` / `Organization`; sitewide `Organization` schema
- [ ] Language switcher — `umtd_localize_url()`, `wp_nav_menu_objects` filter, `umtd_language_switcher()` template function
- [ ] Menu URL rewriting — nav menu item URLs rewritten to active language via filter
- [ ] Bilingual content entry — all translatable fields populated in FR and EN via `umtd_translations`
- [ ] Venue agent subtype — venues excluded from Artists archive, linked from Event singles

### Weeks 10–12 — Staging Review

Client phase: Staging review and client UAT.

- [ ] All data entry complete on staging
- [ ] All UAT issues resolved
- [ ] Performance baseline — page load times acceptable on current EC2 instance
- [ ] Accessibility pass — semantic HTML audit, keyboard navigation, screen reader spot-check

### Week 13–16 — Production Deploy

Client phase: Production deploy.

- [ ] DNS cutover — Cloudflare A record updated, TTL managed
- [ ] Final `mysqldump` from staging imported to production
- [ ] CI/CD verified — GitHub Actions deploy workflows confirmed on all three repos
- [ ] SSL confirmed on production domain
- [ ] Rewrite rules flushed — Settings → Permalinks → Save
- [ ] Admin UI locked down — client user role configured, plugin installation disabled, ACF field groups read-only
- [ ] Client credentials delivered — WP admin login, S3 dump access
- [ ] Handoff documentation — client-facing guide to data entry, terminology, and support process

---

## Backlog

Items without a delivery date. Sequenced after Piroir launch unless a second client engagement requires earlier delivery.

### Data Model

- **Item-level inventory (`umtd_items`)** — individual physical exemplars of a Work (copy number, condition, location). FRBR Item level. Prerequisite for replacing ArtworkArchive as inventory tool.
- **Work relations (`umtd_work_relations`)** — explicit object-to-object relationships (isDerivedFrom, isDocumentedBy). Deferred until a client archive requires it; series membership is currently handled by `umtd_series` taxonomy.
- **Schema migration versioning** — a version table and `dbDelta()`-based migration runner to manage schema changes across multiple client installs cleanly.

### Admin UX

- **Auto-populate Agent fields from ULAN / Wikidata** — search existing agents (duplicate check), Getty ULAN, Wikidata, Wikipedia on the agent edit screen; auto-populate available fields on selection. Estimated 4–6 hours.
- **Custom date entry UI** — replace ACF date pickers for `date_earliest` / `date_latest` with Year / Month / Day / Qualifier / Range toggle. Qualifier vocabulary per EDTF and VRA Core 4.0. `date_display` derived on save.
- **Medium taxonomy conditional display** — show only relevant `umtd_medium` terms based on selected `umtd_work_type` on the work edit screen.
- **Event title convention** — no enforcement hook exists. Editorial pattern: `Artist(s) — Event Title` for solo shows, `Exhibition Title` for group shows. Enforce via `acf/save_post` hook or document as editorial convention.
- **Page generation on child plugin activation** — `wp_insert_post()` creates required nav pages with correct slugs and page templates on activation. Currently done manually.
- **Alt text auto-generation** — `acf/save_post` hook on attachments writes `_wp_attachment_image_alt` from structured metadata (agent `name_display`, work title, `date_display`, `view_type`).
- **Attachment modal field control** — caption, description, and alt text remain editable in the WordPress media modal (separate JS rendering path from the full edit screen). Suppression requires overriding core JS templates.

### Front-end

- **REST API** — not started. Required for any external integrations or headless front-end.
- **Search / filtering UI** — not started.
- **Lazy-load JS** — `data-lazy` attribute stubbed on archive grids. No JS written.

### Platform & Infrastructure

- **DR instance** — a stopped EC2 instance in a second region, updated from weekly AMI snapshots, activatable in ~20 minutes if the primary instance fails. Deferred — EBS snapshots and staging environment are sufficient due diligence at current client count.
- **Staging environments for future clients** — same-host subdomain pattern (`staging.{client}.umt.world`) applied to each new client on onboarding.
- **Backup automation audit** — confirm EBS snapshot and `mysqldump` coverage for all client databases as client count grows.
- **`wp i18n make-pot`** — not generated. Required before any third-party translation or localization work.
- **Caching** — irrelevant at current scale. Revisit if traffic warrants.
- **WP admin menu placement** — `show_in_menu` on CPTs currently unset. Requires a menu structure decision before implementing.
- **Class-based plugin architecture** — current procedural structure is acceptable. Revisit at scale.
- **PHP/JS style guide** — formal `.editorconfig` and `phpcs.xml` with WordPress-Core ruleset. Currently conventions only.
- **Uninstall hook** — plugin leaves orphaned CPT data and taxonomy terms on removal.

### Business

- **Portfolio site** — case studies, methodology documentation, public-facing umt.studio product page.
- **Web design rate and minimum** — TBD pending first web-design-only engagement.
- **Newsletter add-on pricing** — TBD pending operational experience with listmonk provisioning time per client.
- **Ecommerce add-on** — platform TBD.
- **Year 3 targets** — TBD.
- **Founder bio (§6.1 BUSINESS.md)** — TBD.
- **Advisors (§6.2 BUSINESS.md)** — TBD.
- **Sales process (§7.2 BUSINESS.md)** — TBD.
- **Revenue projections (§8.4 BUSINESS.md)** — TBD.
- **Break-even analysis (§8.5 BUSINESS.md)** — TBD.
- **Business bank account** — keep personal and business finances separated from first invoice.
- **GST/QST registration** — mandatory at \$30,000 CAD annual revenue. Monitor and register proactively before threshold.
- **REQ registration** — confirm whether sole proprietorship operating under legal name requires registration in Québec.
