# UMT Studio — Roadmap

This document is the authoritative record of planned work. It has two parallel tracks: the first client onboarding sequence and the platform version roadmap covering the three-year product arc.

The Artlogic benchmark: by v3.0 (EOY3, 2028), the umt.studio CMS will offer feature parity with Artlogic across all non-mobile service lines — archive, commerce, CRM, invoicing, marketing automation, social publishing, analytics, and sales pipeline — built on a correct archival data model with AAT/FRBR/VRA Core alignment that Artlogic structurally cannot replicate. Mobile / iOS access is explicitly deferred post-v3.0.

---

## Current Status

The platform is at v0.2.0. Core archive functionality is complete and presentable: CPTs, ACF field groups, controlled vocabulary, schema.org output, CI/CD pipeline, and production infrastructure are all operational. The platform is in active prototype deployment and ready for a first client onboarding engagement.

The next development phase is funded and sequenced around a founding client contract. Pre-contract prerequisites are listed below. On signing, the 16-week onboarding sequence begins.

Business development (identity, portfolio site, sales) is the current operational priority alongside platform readiness. Platform development resumes full-time on contract signing.

---

## First Client Onboarding

Week numbers are relative to contract signing (Week 0).

### Pre-contract

These items are prerequisites for signing. No contract is issued until they are complete.

- [ ] Staging environment live — `staging.{client}.umt.world`, same EC2 host, separate nginx server block, separate MariaDB database
- [x] EBS snapshots configured — DLM policy live, daily at 03:00 UTC, 7-day retention, targets Backup=daily tag on root volume
- [ ] PHP and MariaDB versions confirmed — local dev environment matches production (PHP 8.4, MariaDB production version)
- [ ] Professional liability (E&O) insurance obtained
- [ ] Limitation of liability clause confirmed in contract template
- [ ] Contract template finalized — scope, deliverables, payment schedule, IP ownership, data portability, T&M ceiling approval language
- [ ] Business bank account open
- [ ] GST/QST registration status confirmed

### Weeks 1–2 — Scoping and Schema

Client phase: Scoping assessment and design brief.

- [ ] `SCHEMA.md` finalized — table definitions confirmed with client data model
- [ ] `umtd_register_tables()` — base plugin registers all custom tables via `dbDelta()` on activation; child plugin declares active subset via `umtd_schema_tables` filter
- [ ] `acf/save_post` intercept hooks — redirect ACF writes to custom tables; suppress ACF's own postmeta writes for intercepted fields
- [ ] `umtd_get_field( $field, $post_id, $lang = null )` — reads from custom tables; falls back to `get_field()` for any field not yet covered
- [ ] `umtd_roles` seeded — initial vocabulary: Artist, Author, Printer, Publisher, Photographer, Curator
- [ ] `umtd_series` taxonomy registered on `umtd_works`
- [ ] `agent_type` ENUM column — Person, Organization, Venue
- [ ] All templates updated — replace all remaining `get_field()` calls with `umtd_get_field()`
- [x] Nightly `mysqldump` + `pg_dump` → S3 — automated via cron at 02:00 UTC; all non-system MariaDB databases backed up dynamically; client-accessible from WP admin pending
- [ ] Related Works field label semantics confirmed with client — on Works: series variants or related objects? On Events: works exhibited or works documented?
- [ ] Staging environment SSL confirmed

### Weeks 3–7 — Data Import

Client phase: Data import.

- [ ] WP-CLI import scripts — field-mapped CSV → custom tables for Works, Agents, Events
- [ ] Media population — attachment upload, junction table population, view type assignment
- [ ] Data entry and QA on staging — all records entered and reviewed before staging review
- [ ] Edition and copy fields populated for all relevant Works
- [ ] Series terms created and assigned

### Weeks 5–11 — Child Theme

Client phase: Child theme design and implementation. Overlaps with data import.

- [ ] `umt-design-{client}` repo created — child theme, client typography, colour, branding
- [ ] All archive templates updated to use `umtd_get_field()` throughout
- [ ] Schema.org engine extended — `umtd_events` → `ExhibitionEvent`; `umtd_agents` → `Person` / `Organization`; sitewide `Organization` schema
- [ ] Language switcher — `umtd_localize_url()`, `wp_nav_menu_objects` filter, `umtd_language_switcher()` template function
- [ ] Menu URL rewriting — nav menu item URLs rewritten to active language via filter
- [ ] Bilingual content entry — all translatable fields populated in FR and EN
- [ ] Venue agent subtype — venues excluded from Artists archive, linked from Event singles

### Weeks 10–12 — Staging Review

Client phase: Staging review and client UAT.

- [ ] All data entry complete on staging
- [ ] All UAT issues resolved
- [ ] Performance baseline — page load times acceptable on current EC2 instance
- [ ] Accessibility pass — semantic HTML audit, keyboard navigation, screen reader spot-check

### Weeks 13–16 — Production Deploy

Client phase: Production deploy.

- [ ] DNS cutover — Cloudflare A record updated, TTL managed
- [ ] Final database dump from staging imported to production
- [ ] CI/CD verified — GitHub Actions deploy workflows confirmed on all repos
- [ ] SSL confirmed on production domain
- [ ] Rewrite rules flushed
- [ ] Admin UI locked down — client user role configured, plugin installation disabled, ACF field groups read-only
- [ ] Client credentials delivered — WP admin login, database backup access
- [ ] Handoff documentation — client-facing guide to data entry, terminology, and support process

---

## Platform Roadmap

Versions are sequenced development milestones, not calendar releases. Year targets are approximate. Each version represents a coherent feature layer that expands the platform's competitive surface. Pricing research for new service lines is built into the milestone at which that line becomes available.

---

### v0.x — Archive + Public Platform

**Target: Y1, 2026**

The foundation. A standards-aligned archival CMS with a public-facing website, bilingual support, basic commerce, and newsletter infrastructure. This is the platform as delivered to the first client and the basis for all subsequent onboardings.

**Archive & data model**
- [x] CPTs: Works, Agents, Events (FRBR-aligned)
- [x] ACF field groups: CDWA / VRA Core / schema.org aligned
- [x] AAT-aligned controlled vocabulary, seeded on activation
- [x] Agent name logic — sort key vs display name, Person / Organization typed
- [x] Agent role model — `umtd_roles` vocabulary, relationship to works via junction table
- [ ] Custom DB schema — `umtd_translations`, `umtd_work_media`, `umtd_work_agents`, `umtd_items` (edition/copy level)
- [ ] `umtd_press` CPT — press clippings, linked to exhibitions and agents; essential for commercial gallery clients
- [ ] `umtd_series` taxonomy on works
- [ ] Custom date entry UI — Year / Month / Day / Qualifier / Range per EDTF and VRA Core 4.0; replaces ACF date pickers
- [ ] Medium taxonomy conditional display

**Schema.org & SEO**
- [x] VisualArtwork JSON-LD on Works singles
- [ ] ExhibitionEvent JSON-LD on Events singles
- [ ] Person / Organization JSON-LD on Agents singles
- [ ] Sitewide Organization schema

**Bilingual**
- [ ] Language switcher — `umtd_localize_url()`, nav menu filter
- [ ] FR/EN slug architecture via `config/i18n.php`
- [ ] `umtd_translations` custom table — `post_id | lang | field_name | value`

**Public website**
- [ ] `umt-design-{client}` child theme — typography, colour, branding
- [ ] All archive and single templates using `umtd_get_field()` throughout
- [ ] Accessibility audit — semantic HTML, keyboard navigation, screen reader

**Commerce add-on (WooCommerce)**
- [ ] WooCommerce integration scoped and specified
- [ ] Work availability field — controlled vocabulary: Available, Sold, Not for Sale, On Loan, On Deposit
- [ ] INQUIRE / Add to Cart conditional rendering based on availability and work type
- [ ] WooCommerce product mirroring works records — availability sync on sale
- [ ] Basic order management via WooCommerce admin
- [ ] Stripe payment processing integration
- [ ] **[Pricing research — commerce add-on monthly rate. To be established during v0.x and reflected in BUSINESS.md §8.2 before first commerce client onboarding.]**

**Newsletter add-on (listmonk)**
- [ ] listmonk provisioning documented and repeatable per client
- [ ] Newsletter subscription form — WordPress shortcode or block, feeds listmonk list
- [ ] Subscriber management accessible from WP admin (iframe or API link)
- [ ] **[Pricing research — newsletter add-on tiered by subscriber count and monthly send volume. To be established during v0.x and reflected in BUSINESS.md §8.2 before first newsletter client onboarding.]**

**DAM foundations**
- [ ] Image metadata schema — view type, rights, license, credit line, image source, image date, related work
- [ ] Alt text auto-generation hook — derives from agent name_display, work title, date_display, view_type on attachment save
- [ ] Attachment modal field control — suppress caption/description/alt from media modal; enforce structured fields only
- [ ] Canonical filename schema — derived from accession_number (v0.3+)

**Admin UX**
- [ ] Auto-populate Agent fields from ULAN / Wikidata — search, duplicate check, auto-populate on selection
- [ ] Page generation on child plugin activation — `wp_insert_post()` creates required nav pages
- [ ] WP admin menu placement — CPT sidebar structure finalized
- [ ] PHP/JS style guide — `.editorconfig`, `phpcs.xml` with WordPress-Core ruleset
- [ ] Lazy-load JS — `data-lazy` attribute stubbed on archive grids; implement scroll-based image loading

**Infrastructure**
- [ ] Staging environments — `staging.{client}.umt.world` pattern documented and repeatable
- [x] EBS snapshot automation — DLM policy live, daily at 03:00 UTC, 7-day retention
- [x] Nightly `mysqldump` + `pg_dump` → S3 — automated, dynamic per-client, 30-day retention
- [x] Automated alerting — CloudWatch alarms for EC2 system recovery, instance reboot, and backup dead man's switch; SNS email alerts via umt-alerts topic
- [ ] `wp i18n make-pot` — generated for base plugin and base theme
- [ ] Portfolio site — case studies, methodology, public-facing product page

---

### v1.0 — CRM Foundations

**Target: Y1–Y2, 2026–2027**

The first service line that puts the platform in direct competition with Artlogic and Arternal on internal gallery operations. Collector contacts, inquiry tracking, and acquisition history are built on top of the existing agent and work data model — a collector is a typed agent; an inquiry is a relationship between a collector, a work, and a timestamp. No new architectural primitives required.

**Collector contact management**
- [ ] `umtd_collectors` CPT — or extend `umtd_agents` with a Collector agent_type; decision to be made during scoping
- [ ] Collector profile fields — contact details, acquisition history (linked works), collector interests (linked taxonomy terms), communication log
- [ ] Role-based access — collector records visible to gallery staff only, not public-facing
- [ ] Collector import — CSV import path from Artlogic export format (CSV/XLSX) and Arternal

**Inquiry tracking**
- [ ] `umtd_inquiries` CPT — records inquiry event: collector → work, date, channel (web form / email / fair / in person), status (open / offered / sold / passed)
- [ ] Inquiry created automatically on INQUIRE form submission from public website
- [ ] Inquiry status workflow — gallery staff move inquiries through status pipeline from WP admin
- [ ] Inquiry history visible on work record and collector record

**Acquisition history**
- [ ] Work → collector relationship — sold works linked to acquiring collector with date and price (internal only)
- [ ] Acquisition history visible on work record (internal) and contributes to provenance chain (public)
- [ ] Collector acquisition history — all works acquired, visible on collector record (internal)

**Private viewing**
- [ ] Password-protected exhibition template — curated work selection shared with a specific collector or group
- [ ] Private view link generation from WP admin — expiring URL, optional password
- [ ] Private view access log — which collector accessed, when, which works viewed

**Search and filtering**
- [ ] Front-end search and filtering UI — works filterable by agent, work type, medium, date range; events filterable by type and date

**Pricing** To be established during v1.0 development and reflected in BUSINESS.md §8.2 before first CRM client onboarding. Research comparables: Arternal pricing, Artlogic CRM tier pricing, standalone CRM market rates in the cultural sector.

---

### v1.x — Financial Layer

**Target: Y2, 2027**

Invoicing, consignment, and artist payables. This closes the primary administrative paperwork gap that Artlogic and Arternal both target. At v1.x, the platform covers the full operational lifecycle of a gallery transaction: inquiry → offer → sale → invoice → artist payable.

**Invoicing**
- [ ] `umtd_invoices` CPT — line items linked to works, collector, date, payment status
- [ ] Invoice PDF generation — standard template, exportable
- [ ] Invoice status — draft / sent / paid / overdue
- [ ] QuickBooks / accounting export — CSV or direct integration (scoped during development)
- [ ] GST/QST handling — Québec tax rates applied automatically based on client jurisdiction

**Consignment tracking**
- [ ] `umtd_consignments` CPT — work → consignor (agent), consignment terms, period, commission rate
- [ ] Consignment status — active / returned / sold
- [ ] Consignment visible on work record and agent record

**Artist payables**
- [ ] Artist payable generated on sale — commission split derived from consignment record
- [ ] Payable PDF — artist statement of account
- [ ] Payable status — pending / paid

**Edition inventory (`umtd_items`)**
- [ ] Physical exemplar tracking — copy number, condition, location, owner
- [ ] Item linked to work record (FRBR Item level)
- [ ] Item provenance chain — ownership history as ordered list of agent relationships with dates

**Pricing**
- [ ] **[Pricing research — invoicing / financial layer add-on monthly rate. To be established during v1.x development and reflected in BUSINESS.md §8.2. Research comparables: Artlogic invoicing tier, QuickBooks pricing, Arternal financial features.]**

---

### v2.0 — Marketing Platform

**Target: Y2–Y3, 2027–2028**

Deep listmonk integration, social media publishing, campaign scheduling, and online viewing rooms. At v2.0, the platform covers the full public-facing marketing lifecycle of a gallery program: archive → social → newsletter → campaign → analytics. Collection data and digital assets already in the system become the source material for every outbound communication.

**Social media publishing (Meta platforms)**
- [ ] Facebook and Instagram publishing via Meta Graph API — compose and schedule posts from WP admin
- [ ] Post composer — draws on works, agents, and events records; attach images from DAM directly
- [ ] Scheduling queue — posts scheduled against exhibition calendar; publish immediately or queue
- [ ] Post history linked to works and events — which posts featured which works, engagement metrics per post
- [ ] Engagement analytics — reach, impressions, clicks per post, surfaced in WP admin dashboard

**listmonk deep integration**
- [ ] Subscriber segmentation — by collector interests (linked taxonomy terms), acquisition history, geography
- [ ] Campaign scheduling from WP admin — compose, schedule, send without leaving WordPress
- [ ] Send history linked to works and exhibitions — which campaigns featured which works, open/click rates per work
- [ ] Campaign analytics dashboard — open rates, click rates, unsubscribes, per-campaign and per-work

**Online viewing rooms**
- [ ] `umtd_viewing_rooms` CPT — curated work selection with editorial text, shareable URL
- [ ] Viewing room templates — multiple layouts (grid, editorial, slideshow)
- [ ] Viewing room analytics — visits, time on page, works clicked
- [ ] Viewing room inquiry integration — INQUIRE button feeds `umtd_inquiries` CRM

**Analytics layer**
- [ ] Reporting dashboard in WP admin — works viewed, agents viewed, exhibitions viewed, inquiry volume, newsletter performance, social reach
- [ ] Data sourced from existing records — no third-party analytics dependency for core metrics
- [ ] Optional Matomo / self-hosted analytics integration for traffic data

**Pricing**
- [ ] **[Pricing research — marketing platform add-on monthly rate. To be established during v2.0 development and reflected in BUSINESS.md §8.2. Research comparables: Artlogic marketing tier, Buffer/Hootsuite pricing as proxy for scheduling value, listmonk self-hosted cost baseline.]**

---

### v2.x — Full Sales Pipeline

**Target: Y3, 2028**

Offer tracking, collector preference profiling, and a sales pipeline dashboard. At v2.x, the platform covers the complete collector relationship lifecycle that Artlogic's PrivateViews and Arternal's CRM target — without requiring a separate tool.

**AI-assisted campaign suggestions**
- [ ] Optional AI analysis of collection data, exhibition schedule, and engagement analytics to surface campaign timing and content suggestions from within WP admin

**Offer tracking**
- [ ] `umtd_offers` CPT — work → collector, offered price, offer date, expiry, status (pending / accepted / declined / expired)
- [ ] Offer history on work record and collector record
- [ ] Offer email generation — formatted offer letter PDF, sent via listmonk or direct SMTP

**Collector preference profiling**
- [ ] Interest taxonomy on collector record — linked to `umtd_work_type`, `umtd_medium`, `umtd_agents`
- [ ] Match engine — surfaces works matching a collector's interest profile
- [ ] Preference derived automatically from acquisition and inquiry history

**Sales pipeline dashboard**
- [ ] Pipeline view — all open inquiries and offers, stage, value, assigned staff member
- [ ] Pipeline analytics — conversion rate by stage, average time to close, revenue by agent/work type
- [ ] Fair mode — filtered pipeline view for art fair context; works flagged for fair, inquiries captured at fair

**Work relations**
- [ ] `umtd_work_relations` table — explicit object-to-object relationships (isDerivedFrom, isDocumentedBy, isPartOf)
- [ ] Relationship graph visible on work single — related works, source works, documentation

**Pricing**
- [ ] **[Pricing research — full sales pipeline add-on or revised all-inclusive tier. To be established during v2.x development. At this version, consider whether individual add-on pricing remains viable or whether a consolidated "Professional" tier covering CRM + invoicing + marketing + pipeline makes more commercial sense. Reference Artlogic's bundle pricing as primary comparable.]**

---

### v3.0 — Platform Parity

**Target: EOY3, 2028**

At v3.0, the umt.studio CMS reaches feature parity with Artlogic across all non-mobile service lines. All subsequent development is interface-level: UX refinement, performance, accessibility, design quality. The architectural moat — archival data model, AAT/FRBR/VRA Core alignment, GPL, Canadian infrastructure, data sovereignty — is fully established and cannot be replicated by competitors without rebuilding from scratch.

**REST API**
- [ ] Public read API — works, agents, events, exhibitions; paginated, filterable by taxonomy, date range, agent
- [ ] Authenticated write API — for future headless front-end or third-party integrations
- [ ] API documentation — OpenAPI spec, published with portfolio site

**Artlogic migration path**
- [ ] Documented import path from Artlogic CSV/XLSX export — field mapping spec, import script
- [ ] Artlogic export covers: artworks (CSV/XLSX), contacts (CSV/XLSX), accounts, invoices, sales and offers. Import scripts handle each. Note: Artlogic exports flat rows with no standards alignment — AAT IDs, FRBR relationships, and schema.org structure are applied during import by the umt.studio migration tooling. This is a structural advantage over any other migration path.
- [ ] Arternal migration path — similar field mapping from Arternal export

**Artsy integration**
- [ ] Artsy partner API access — requires negotiation with Artsy as a platform partner; initiate outreach at v2.x when client base justifies the relationship
- [ ] Works published to Artsy from WP admin on partner API approval
- [ ] Artsy inquiry routing — inquiries received via Artsy routed into `umtd_inquiries`

**Stabilization**
- [ ] Schema migration versioning — version table and `dbDelta()`-based migration runner for clean schema changes across multiple client installs
- [ ] DR instance — stopped EC2 in second region, updated from weekly AMI snapshots, activatable in ~20 minutes
- [ ] Uninstall hook — clean removal of CPT data and taxonomy terms
- [ ] Full WCAG 2.1 AA accessibility audit across all public templates
- [ ] Caching layer — evaluate and implement if traffic warrants; irrelevant at current scale
- [ ] Class-based plugin architecture — current procedural structure is acceptable; refactor if complexity justifies it post-v3.0

**Mobile / iOS**
- [ ] Deferred post-v3.0. Evaluate based on client demand and revenue after v3.0. Likely addressed via mobile-optimized responsive web app rather than native development.

