# umt.studios — Business Plan

*Founding document. Last revised: 2026-03-26.*
*Sole proprietorship, Québec, Canada.*

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Company Overview](#2-company-overview)
3. [Services & Products](#3-services--products)
4. [Market & Positioning](#4-market--positioning)
5. [Operations](#5-operations)
6. [Team](#6-team)
7. [Marketing & Sales](#7-marketing--sales)
8. [Revenue Model & Financials](#8-revenue-model--financials)
9. [IP & Proprietary Assets](#9-ip--proprietary-assets)
10. [Milestones & Roadmap](#10-milestones--roadmap)
11. [Risk Assessment](#11-risk-assessment)
12. [Legal & Administrative](#12-legal--administrative)
13. [Appendix](#13-appendix)

---

## 1. Executive Summary

**UMT Studios** builds archival web infrastructure for cultural institutions — artist-run centres, galleries, print studios, and small museums — that take their collections seriously.

The problem is structural. Cultural institutions operate between two inadequate options: generic platforms (Squarespace, WordPress off-the-shelf) that treat collection records as blog posts, and commercial gallery software (Artlogic, Arternal) built on a sales-and-inventory paradigm where the archive is a byproduct of the CRM. Neither produces a durable, queryable, standards-aligned institutional record. When the platform lapses or the vendor pivots, the collection history goes with it.

UMT Studios addresses this with a managed WordPress CMS — the **umt.studio platform** — built on archival data standards (AAT, FRBR, CDWA, VRA Core, schema.org) from the ground up. Works, Agents, and Events are first-class archival objects with controlled vocabulary, proper metadata, exportable data, and schema.org output. The platform is licensed under the GPL and deployed on UMT Studios-managed Canadian infrastructure, with full data portability on exit. It is not a template. It is not a marketing site. It is a permanent institutional record that also supports commerce, CRM, newsletters, and marketing — built on a data model that makes every subsequent feature more valuable.

That marketing layer is a core part of the platform vision. At v2.0, the platform integrates campaign scheduling for Meta platforms (Facebook, Instagram) directly from the WordPress admin — composing and publishing posts that draw on collection data, exhibition records, and digital assets already in the system, without leaving the platform. The institutional archive becomes the source of the marketing calendar. No third-party scheduling tool, no data re-entry, no disconnection between what is in the collection and what is being communicated publicly about it.

The business model is SaaS: a one-time onboarding fee (from $5,000 CAD) covers information architecture, schema design, and deployment; a monthly subscription ($250–$500 CAD) covers managed hosting, maintenance, and support. Data import is billed separately on a time-and-materials basis. The recurring subscription is the primary revenue mechanism and the basis for long-term client relationships.

The platform is in active development at v0.2.0, deployed on AWS EC2 infrastructure in ca-central-1, with Atelier-Galerie A. Piroir as the founding reference client. The three-year roadmap delivers progressive feature parity with Artlogic — archive and public website in Year 1, CRM and invoicing in Year 2, full sales pipeline, social publishing, and REST API by EOY Year 3 — built on a correct archival data model that competitors cannot replicate without rebuilding from scratch.

UMT Studios is a Montréal-based sole proprietorship founded in 2026 by Uriah Marc Todoroff, an information architect and systems administrator with twenty years of Linux infrastructure experience, a background in art history and archival research, and seven months of hands-on collection management at Atelier-Galerie A. Piroir.

---

## 2. Company Overview

### 2.1 Identity

| | |
|---|---|
| **Legal name** | UMT Studios |
| **Legal structure** | Sole proprietorship (entreprise individuelle) |
| **Jurisdiction** | Québec, Canada |
| **Founded** | March 2026 |
| **Location** | Montréal, QC |
| **Website** | TBD |
| **Operating language** | English / French (bilingual) |

### 2.2 Mission

*One or two sentences. What do we do and why does it matter?*

UMT Studios designs archival infrastructure that turns institutional history into cultural history. We apply the highest standards of software engineering, data modeling, and information architecture to create a user experience that empowers public access to reliable information.

### 2.3 Vision

*Where is UMT Studios in five years?*

UMT Studios will be working on grant-funded projects to catalogue and present information to the public. We will be designing catalogues with specific scope, conducting research, fact-checking, and undertaking original investigative work in order to collect data and design user experiences in the public interest. We will be contributing to research into culture through the creation of information-rich databases. We will be developing websites that function as public information repositories, providing services to galleries and other cultural institutions locally and internationally.

The product itself will have evolved to be an integrated dashboard that manages data, inventory, digital assets, and communication. Social media campaign writing and scheduling will be done in-app, using data and assets from the collection. The platform will optionally surface AI-assisted campaign suggestions derived from collection data and engagement analytics.

### 2.4 Values

- **Standards alignment** — semantic HTML, schema.org microdata, AAT, FRBR, EDTF; interoperability by default
- **Archival quality** — structured and linked data, enterprise-grade stability and performance
- **Design sensibility** — attention to user needs, careful treatment of philosophical questions
- **Information wants to be free** — everything is transparent, documented, and invoiced

### 2.5 History & Context

UMT Studios emerged from the intersection of three parallel practices: cultural heritage work, archival research, and open-source systems administration. The studio was founded when those practices converged on a single, concrete problem — the inadequacy of existing platforms for institutional collection management — and produced a solution.

---

## 3. Services & Products

### 3.1 Service Lines

The business has four client-facing service lines. Systems administration and information architecture are not sold as standalone services — they are absorbed into the product onboarding fee (§3.1.2).

#### 3.1.1 Web Design & Development

Custom website design and development. Static sites, WordPress, bespoke front-end. Emphasis on semantic markup, accessibility, and performance.

- **Deliverables:** Design system, HTML/CSS/JS front-end, CMS integration, documentation
- **Typical engagement:** T&M with a project minimum
- **Target client:** Small cultural institutions, galleries, artist-run centres, independent practitioners, cinemas, libraries, museums, fashion designers, clothing stores
- **Note:** Web design is optional and separate from the CMS product. A client can take the CMS without commissioning design work, and vice versa.

#### 3.1.2 umt.studio CMS Onboarding

Configuration and deployment of the umt.studio CMS for a specific client. This is the primary intellectual and creative work of the practice. It absorbs information architecture, metadata schema design, collection survey, controlled vocabulary curation, and infrastructure provisioning into a single fixed-fee engagement.

- **Deliverables:** Collection survey and IA document, client-specific YAML config, child plugin, provisioned infrastructure, deployed staging and production environments, documentation
- **Pricing:** Assessed quote. Public floor: \$5,000 CAD. Ceiling: scales with collection complexity, breadth, and institutional prestige. The client never sees the internal rate logic — they receive a fixed quote.
- **Rationale:** This fee compensates for R&D amortized across clients. GPL on the software does not preclude charging for the specialist work of configuring and deploying it. The onboarding is the most valuable work we do — it is equivalent to what a freelance registrar or information architect would charge for a collection survey and schema design engagement. Comparable day rates in the sector run \$700–1,000 CAD. A small, clean collection at floor price represents approximately 5–7 days of combined technical and intellectual work.

#### 3.1.3 Data Import

Migration of a client's existing collection data into the umt.studio CMS. Billed as T&M against a client-approved ceiling, established during a scoping assessment.

Two billable rates apply depending on the nature of the work:

| Work type | Rate (CAD/hr) |
|---|---|
| Technical import — scripted migration, field mapping, WP-CLI, QA | $150 |
| Archival research — sourcing bios, verifying attribution, citing provenance | $250 |

- **Floor:** \$1,500 CAD
- **Ceiling:** Negotiated per engagement after scoping assessment. Client approves the ceiling before work begins.
- **Scoping assessment:** Small fixed fee (may be bundled into onboarding). Produces a record count, data quality assessment, source platform analysis, and ceiling estimate.
- **Rationale:** Import complexity varies too widely for fixed pricing. A structured ArtworkArchive CSV export is largely scriptable; Squarespace portfolio pages require semantic parsing record by record. Clean, well-structured data costs the client less — this is intentional. The \$250/hr archival research rate reflects the specialist nature of the work: verifying attribution, sourcing bios from academic archives and primary sources, and citing everything. This is journalism and archival work, not data entry.
- **Contract language for completeness:** The contract states that the contractor will make reasonable efforts to populate available fields using verifiable sources. Fields for which no verifiable source can be identified will be left blank or flagged for client review. Completeness is not guaranteed — it is bounded by what the historical record contains.

#### 3.1.4 Monthly Subscription

Managed hosting, maintenance, and support on UMT Studios infrastructure. Billed monthly. This is the recurring revenue mechanism of the business.

See §8.2 for full pricing and add-on structure.

**Scope (identical across all tiers):**
- Hosting on UMT Studios-managed AWS EC2, ca-central-1
- Supervised automated updates (WordPress core, plugins) run weekly
- Breakfix — faults resolved within agreed SLA
- Support — questions answered within 1 business day

**Scope explicitly excluded (all tiers):**
- Feature additions or changes
- Content entry
- Design work
- Consulting or strategic sessions
- Any work beyond answering questions and fixing breakage

Additional time beyond this scope is billed separately at the applicable hourly rate. No subscription tier purchases more human time than any other. A higher-tier client does not receive faster responses or more hours.

**Rationale for scope limitation:** The subscription funds availability and infrastructure continuity, not bespoke service. Keeping scope identical across tiers protects the founder's time and prevents higher-paying clients from treating the monthly fee as a retainer for consulting access.

---

### 3.2 Product: umt.studio CMS

White-label WordPress CMS framework — base plugin (`umt-studio`) + base theme (`umt-design`), extended per client via child plugin + child theme.

**Core capabilities (current, v0.x):**
- Custom post types: Works, Agents, Events (FRBR-aligned)
- ACF field groups: archival metadata aligned with CDWA, VRA Core, schema.org
- Controlled vocabulary: AAT-aligned work type taxonomy, seeded on activation
- Agent name logic: sort key vs display name, Person/Organization typed
- Schema.org JSON-LD output: VisualArtwork (Works); Events and Agents planned
- CI/CD ready: git-based deploy, GitHub Actions → AWS SSM

**Platform roadmap — three-year arc:**

The platform is developed in versioned milestones toward full-stack parity with commercial gallery software (principally Artlogic) by EOY3, built on a correct archival data model that competitors cannot replicate without rebuilding from scratch. Each version adds a coherent service layer and expands the competitive surface.

| Version | Target | Service layer added |
|---|---|---|
| v0.x | Y1, 2026 | Archive + public website, bilingual, commerce (WooCommerce), newsletter (listmonk), DAM, press management |
| v1.0 | Y1–Y2, 2026–2027 | CRM foundations — collector contacts, inquiry tracking, acquisition history, private viewing |
| v1.x | Y2, 2027 | Financial layer — invoicing, consignment, artist payables, edition inventory |
| v2.0 | Y2–Y3, 2027–2028 | Marketing platform — listmonk deep integration, social publishing (Meta platforms), campaign scheduling, online viewing rooms, analytics |
| v2.x | Y3, 2028 | Full sales pipeline — offer tracking, collector preference profiling, pipeline dashboard |
| v3.0 | EOY3, 2028 | Platform parity — REST API, Artlogic migration tooling, Artsy integration, DR infrastructure |

By v3.0, the platform covers: archive, commerce, CRM, invoicing, marketing automation, analytics, and sales pipeline — all built on AAT/FRBR/VRA Core-aligned data. Mobile/iOS access is explicitly deferred post-v3.0. See ROADMAP.md for full version detail.

**Licensing model:** GPL. The base plugin and theme are released under the GNU General Public License. This is consistent with WordPress ecosystem norms and does not preclude charging for the specialist work of configuring and deploying the system for a specific client. The onboarding fee (§3.1.2) is a service fee, not a licence fee.

**Current status:** v0.2.0, deployed with Atelier-Galerie A. Piroir as the founding reference client. Base repos: `umt-studio`, `umt-design`.

---

## 4. Market & Positioning

### 4.1 Target Market

**Primary:** Small to mid-size cultural institutions in Québec and Canada — artist-run centres, commercial galleries, print studios, small museums, media companies, archives, foundations, cinemas, retailers producing editorial content

**Secondary:** Individual artists, writers, and practitioners who need archival-grade personal websites

**Tertiary:** Organizations in adjacent fields — libraries, heritage organizations, academic research projects with public-facing collections

### 4.2 Market Context

The primary market is the Canadian cultural sector — artist-run centres, commercial galleries, print studios, small museums, and heritage organizations. This sector is characterized by institutions with serious collection management needs, limited technical staff, and budget structures tied to grant cycles (Canada Council for the Arts, CALQ, Museums Assistance Program). Many operate on Squarespace or generic WordPress installations that do not meet their archival requirements. The Québec concentration is the initial focus; English-Canadian and international expansion follows as the client base and case study library develops.

**Grant opportunity — Museums Assistance Program (MAP), Collections Management component:**
The MAP Collections Management grant covers up to 75% of eligible project costs to a maximum of $400,000 per project. Eligible expenses include consultant fees, purchase and implementation of collections management systems, cataloguing, staff training, and software. The umt.studio onboarding and data import services map directly onto eligible expense categories. The annual application deadline is November 1; funded projects run April 1–March 31. Best approached in August–September to allow time for scoping and application before the November deadline. Grant funding is a secondary sales avenue — the primary model does not depend on it — but clients should be made aware of it as a mechanism for funding the onboarding engagement.

### 4.3 Problem Statement

Cultural institutions manage collections that have archival requirements — provenance, attribution, controlled vocabulary, rights metadata — but are typically served by two inadequate alternatives.

At the generic end: WordPress with off-the-shelf themes, Squarespace, Cargo. These treat all content as blog posts. Data cannot be queried, exported, or integrated with library and museum systems. When the platform contract lapses or the template becomes obsolete, the institutional record goes with it.

At the sector-specific end: Artlogic and its competitors (Arternal, ArtBase) dominate the commercial gallery software market. These platforms are built on a sales-and-inventory paradigm — a work record is an inventory item, not a cultural document. Artlogic explicitly positions its website product as a sales catalogue integrated with a CRM. When a work sells, it moves toward invisible. Historical exhibitions, past agents, the 40-year program of a gallery like Blouin Division — none of this is a first-class object in an inventory model. Artlogic does allow data export (CSV/XLSX for artworks, contacts, invoices, and offers), but the export is flat rows with no standards alignment: no AAT IDs, no FRBR relationships, no schema.org structure. The data comes out; the data model does not.

At the institutional end: museum-grade collection management software (CollectiveAccess, Omeka, TMS) is expensive, complex to operate, and requires dedicated staff. It serves large institutions with permanent collections management staff. It is not available to a mid-size commercial gallery or artist-run centre.

The gap is real and unserved: institutions that take their data seriously but cannot staff a museum IT department, and who need a platform that treats their archive as a permanent public record — not a rolling catalogue of what is currently available — while also supporting the commercial operations (commerce, CRM, marketing) that keep the institution funded.

UMT Studios builds that platform. The proposition is not archive *or* commerce. It is archive *as* the foundation for commerce — a correct data model that makes every subsequent feature (inquiry, invoice, newsletter, analytics) more valuable because the underlying record is standards-aligned, permanently legible, and institutionally durable.

### 4.4 Competitive Landscape

**Commercial gallery software (primary competitors)**

| Competitor | Founded | Positioning | Their gap vs UMT Studios |
|---|---|---|---|
| Artlogic | 1994, London | Dominant all-in-one gallery platform — inventory, CRM, website, marketing, payments. 126 staff, ~$16M USD revenue, 6,000+ clients in 70 countries. Absorbed ArtBase, exhibit-E, galleryManager (2022) and ArtCloud (2025). | Sales-oriented inventory model. No archival standards (AAT, FRBR, VRA Core, CDWA). Website is a sales catalogue, not an institutional record. Sold works disappear from the public record. Data exports as flat CSV/XLSX with no standards alignment — rows come out, the data model does not. Template websites are visually indistinct. No API. |
| Arternal | 2015, New York | Pure CRM-first — collector relationship management, sales pipeline, invoicing, offers. Explicitly markets against archival thinking: "your software was built to archive art, not to sell it." Does not offer a public website. | No public-facing website. No archival standards. No controlled vocabulary. Reinforces the paradigm that archive and commerce are in opposition — UMT Studios refutes this. |
| ArtBase | 1993, New York | Desktop/cloud database for galleries, museums, dealers. Absorbed by Artlogic 2022. | Now subsumed into Artlogic. Historically served museums but with no standards alignment. |

**Adjacent software (secondary reference points)**

| Competitor | Positioning | Gap |
|---|---|---|
| Generic WordPress agencies | Fast, cheap, template-driven | No data modelling; no IA depth |
| CollectiveAccess / Omeka | Museum-grade CMS | Expensive, complex, requires dedicated staff; no managed hosting |
| LibraryHost (managed Omeka/AtoM) | Managed archival hosting, \$34–\$179 USD/month | No design; no public-facing website; generic templates; metered support |
| Cargo / Squarespace | Portfolio/artist sites | No metadata, no queryable data, no longevity |
| Freelance developers | Cost-competitive | No domain knowledge in cultural heritage |
| Museum IT departments | Institutional expertise | Not available to small institutions |

**Positioning**

UMT Studios occupies the intersection of archival rigour and commercial viability that none of these competitors hold. Against Artlogic and Arternal: the platform is archive-first, built on correct data models and open standards, with commerce, CRM, and marketing as additional layers — not the foundation. Against generic WordPress and Squarespace: production-grade infrastructure, specialist domain knowledge, and data that outlasts the platform. Against CollectiveAccess and Omeka: managed infrastructure, design sensibility, and a public-facing website that serves both institutional and commercial needs.

The three-year platform roadmap (see ROADMAP.md) closes the feature gap with Artlogic progressively. By v3.0, the platform offers parity on all non-mobile service lines. The archival data model is a structural advantage that Artlogic cannot replicate without rebuilding from scratch — their existing client base and revenue model prevent it.

### 4.5 Positioning Statement

UMT Studios builds archival web infrastructure for institutions that take their data seriously.

### 4.6 Value Proposition by Audience

| Audience | Value |
|---|---|
| Gallery director | Permanent, queryable record of the collection; not a marketing site that rots |
| Curator / registrar | Proper metadata, controlled vocabulary, exportable data |
| Artist / practitioner | Archival-grade personal site without enterprise overhead |
| Grant committee | Standards-aligned, durable, GPL-licensed |

---

## 5. Operations

### 5.1 Toolchain

| Domain | Tools |
|---|---|
| Version control | Git, GitHub |
| CMS | WordPress + ACF |
| Infrastructure | AWS EC2, nginx, PHP-FPM, MariaDB, PostgreSQL |
| CI/CD | GitHub Actions → AWS SSM |
| SSL | Let's Encrypt / Certbot |
| Email | AWS SES + listmonk |
| URL shortening | shlink |
| Editor | Vim |
| OS | Gentoo Linux (dev), Ubuntu 24.04 LTS (production) |

### 5.2 Infrastructure Model

Production: AWS EC2, ca-central-1. One instance hosts multiple clients. Infrastructure cost is near-zero per marginal client at current scale — AWS EC2 runs approximately \$10–15 CAD/month; S3 Standard storage costs ~\$0.03 CAD/GB/month; SES costs ~\$0.10 CAD/1,000 emails. The monthly subscription fee is priced to reflect availability and the founder's time, not infrastructure cost recovery.

Client sites are hosted on UMT Studios-managed infrastructure, not handed off to clients on shared hosting. This is a quality decision, a security decision, and a recurring revenue mechanism.

### 5.3 Delivery Process

1. **Discovery** — scope, IA requirements, client data inventory, vocabulary alignment
2. **Specification** — field map, taxonomy spec, URL architecture, design brief
3. **Development** — child plugin, child theme, ACF overrides, content migration
4. **Staging review** — client UAT on staging environment
5. **Production deploy** — DNS cutover, SSL, CI/CD pipeline
6. **Handoff** — documentation, training, subscription onboarding

### 5.4 Documentation Standard

Every client engagement produces:
- `ARCHITECTURE.md` — system design, field tables, query patterns
- `WORKFLOW.md` — onboarding checklist, operational procedures
- `INFRASTRUCTURE.md` — server config, services, secrets management, deploy process
- `CHANGELOG.md` — versioned record of all changes

### 5.5 Capacity

Current sustainable capacity: approximately 30–35 billable hours per week as a sole proprietor, accounting for non-billable operations, sales, and documentation overhead. Maximum concurrent active onboardings: two. Threshold for subcontracting: a third concurrent onboarding or any engagement requiring design work beyond the founder's capacity.

---

## 6. Team

### 6.1 Founder

**Uriah Marc Todoroff** is a Montréal-based information architect, systems administrator, and web developer with a background spanning cultural heritage, archival practice, and open-source infrastructure.

He holds a B.F.A. in Philosophy, Art History & Film Studies from Concordia University and a certificate in Web Design & User Experience, also from Concordia. His academic work was shaped by sustained engagement with bibliographic standards, archival research methodology, and the philosophy of information — including the ontological questions that underpin library science and collection description. His research into knowledge management, hypermedia, and new media ran alongside a parallel technical practice: twenty years of Linux administration, self-hosted infrastructure, and web development, begun in adolescence and maintained continuously since.

The convergence point was seven months as a stagiaire at Atelier-Galerie A. Piroir, a Montréal fine art print studio and gallery, preceded by a mentorship with gallerist Eli Kerr through the Art Volt program. At Piroir, he catalogued and archived thousands of prints and artist books, managed a print inventory worth several million dollars, maintained the studio's website and digital assets, and handled client-facing communications including invoices and certificates of authenticity. Alongside prior freelance consultation with artists and organizations on archival systems and data management, the experience made the problem concrete: serious cultural institutions managing collections in environments designed for selling products, not describing works. The data model was wrong. The archive was an afterthought.

UMT Studios is the response. The platform applies archival standards (AAT, FRBR, CDWA, VRA Core) to a managed WordPress infrastructure, producing collection records that are queryable, exportable, interoperable, and built to outlast any particular platform or vendor. The commitment to GPL licensing and open standards reflects a conviction that institutional archives are public records, and that the quality and durability of those records is how cultural history gets written.

### 6.2 Hiring Plan

**Phase 1 (current):** Sole proprietorship. All work founder-operated.

**Phase 2 (Y2):** Subcontract design or front-end development on a per-project basis as client volume warrants.

**Phase 3 (Y3):** First hire — junior developer or project coordinator — as subscription MRR reaches a level that sustains a salary.

---

## 7. Marketing & Sales

### 7.1 Acquisition Channels

**Primary (current):**
- Direct referral — existing network in Montréal arts community
- Client-to-client referral — cultural institutions talk to each other

**Secondary (planned):**
- Portfolio site — case studies, documentation of methodology
- Presence in ARCA, AGAC, RCAAQ networks
- Conference presentations — code4lib, museum tech, digital humanities

**Tertiary (longer-term):**
- Writing — technical articles on archival web infrastructure, metadata standards
- Open-source presence — GitHub visibility on base repos

### 7.2 Sales Process

Initial contact → scoping call → fixed-fee proposal → contract and deposit → onboarding. The typical sales cycle for cultural institution clients is long (3–12 months from first contact to signed contract), governed by grant cycles and institutional decision-making timelines. Pipeline management is accordingly patient: relationships are maintained through network presence and referrals, not volume outreach.

### 7.3 Proposal & Contract

Proposals are fixed-fee quotes delivered as a single document covering scope, deliverables, timeline, and payment schedule. Payment is milestone-based: 50% on signing, 50% on production deploy. Payment plans available on request. The standard contract covers scope, deliverables, IP ownership, limitation of liability, data portability, support scope definition, and T&M ceiling approval for import work.

### 7.4 Client Retention

The monthly subscription is the primary retention mechanism. Every onboarding engagement concludes with a subscription offer. The target is full subscription conversion of all onboarding clients — churn is addressed by contract minimum terms (12 months) and the switching cost of migrating a structured archival dataset.

---

## 8. Revenue Model & Financials

### 8.1 Revenue Streams

| Stream | Type | Pricing model | Available |
|---|---|---|---|
| CMS Onboarding | Project | Assessed fixed quote | Now |
| Data Import | Project | T&M with floor and client-approved ceiling | Now |
| Monthly Subscription | Retainer | Flat monthly, tiered by storage | Now |
| Web Design | Project | T&M with minimum | Now |
| Commerce add-on (WooCommerce) | Retainer | Monthly flat — priced at v0.x availability | v0.x |
| Newsletter add-on (listmonk) | Retainer | Monthly flat, tiered by subscriber count / send volume — priced at v0.x availability | v0.x |
| CRM add-on | Retainer | Monthly flat — priced at v1.0 availability | v1.0 |
| Invoicing / financial add-on | Retainer | Monthly flat — priced at v1.x availability | v1.x |
| Marketing platform add-on | Retainer | Monthly flat — priced at v2.0 availability | v2.0 |
| Full sales pipeline add-on | Retainer | Priced at v2.x — may consolidate into Professional tier | v2.x |

### 8.2 Pricing

#### Onboarding Fee

| | |
|---|---|
| **Public floor** | $5,000 CAD |
| **Confidential minimum** | $3,500 CAD (for strategic clients where relationship justifies it) |
| **Ceiling** | Assessed quote — no published maximum |

Pricing is assessed per client and delivered as a fixed quote. The client never sees hourly math. Internal pricing logic: collection complexity × estimated days × blended rate, adjusted for institutional prestige and strategic value. Onboarding pricing is confidential — do not publish or share between clients.

#### Data Import

| Work type | Rate (CAD/hr) |
|---|---|
| Technical import | $150 |
| Archival research | $250 |

| | |
|---|---|
| **Floor** | $1,500 CAD |
| **Ceiling** | Negotiated per engagement after scoping assessment |

The scoping assessment is a small fixed-fee engagement (may be bundled into onboarding) that produces a record count, data quality assessment, source platform analysis, and ceiling estimate. Hours are tracked and reported. The client approves the ceiling before work begins and is billed actual hours up to that ceiling.

#### Monthly Subscription

| Tier | Storage | Monthly (CAD) |
|---|---|---|
| Standard | Up to 50 GB | $250 |
| Plus | Up to 150 GB | $350 |
| Pro | Up to 500 GB | $500 |

Storage limits are soft internal thresholds. Clients will not be penalized for minor overages. Clients with very large DAM requirements (e.g. high-resolution TIFF archives) are quoted separately. All tiers include identical service scope: hosting, weekly supervised updates, breakfix, 1-business-day support response. No tier purchases additional human time.

**Rationale:** Infrastructure cost per client is near-zero (see §5.2). Tiers reflect institutional size as a pricing signal, not cost recovery. The \$250 floor was benchmarked against: Squarespace Business (~\$45 CAD/month, no data model, self-serve); LibraryHost Omeka Standard (~\$88 CAD/month, no design, metered support); and the founder's minimum acceptable hourly return on maintenance time (~3–4 hours/month per client at \$150/hr = ~\$500 marginal cost across a small client base, amortized as client count grows). The band is intentionally narrow (\$250–\$500) to avoid clients at higher tiers feeling entitled to disproportionate human attention.

#### Add-ons (available on any subscription tier)

| Add-on | Available | Pricing |
|---|---|---|
| Commerce (WooCommerce + Stripe) | v0.x | Priced at v0.x availability |
| Newsletter (listmonk) | v0.x | Priced at v0.x availability — tiered by subscriber count and monthly send volume |
| CRM (collector contacts, inquiry tracking, private viewing) | v1.0 | Priced at v1.0 availability |
| Invoicing / financial (invoices, consignment, artist payables) | v1.x | Priced at v1.x availability |
| Marketing platform (social publishing, campaign scheduling, viewing rooms, analytics) | v2.0 | Priced at v2.0 availability |
| Full sales pipeline (offers, preference profiling, pipeline dashboard) | v2.x | Priced at v2.x — may consolidate into a Professional tier |

No pricing commitment is made to clients for add-ons not yet in production.

#### Web Design

| | |
|---|---|
| **Rate** | $200 CAD / hour |
| **Project minimum** | $3,000 CAD |

Web design is optional and separate from the CMS product. T&M against a project minimum.

**Québec tax obligations:** GST (5%) + QST (9.975%) apply to services rendered to clients in Québec. Small supplier exemption threshold: \$30,000 CAD revenue. Registration not yet required — monitor revenue against threshold and register proactively before crossing it.

### 8.3 Cost Structure

**Fixed costs (monthly):**

| Item | Cost (CAD/month) |
|---|---|
| AWS EC2 (production, ca-central-1) | ~$15 |
| AWS S3 storage (~$0.03/GB) | Negligible at current scale |
| AWS SES (~$0.10/1,000 emails) | Negligible at current scale |
| Cloudflare (DNS, CDN, DDoS) | $0 — free tier sufficient at current client scale |
| Domain registrations (~$15–20 CAD/year per domain) | ~$2 amortized |
| Claude (AI assistant) | ~$20 |
| Software / SaaS subscriptions | $0 — all other tools are free tier or open source |
| Accounting | $0 — ledger-based, no software subscription |
| **Total fixed** | **~$37 CAD/month** |

**Variable costs (per project):**

| Item | Notes |
|---|---|
| Stock assets, fonts, licensed software | Project-specific; passed through to client at cost |

### 8.4 Revenue Projections

Revenue projections are structured as: number of onboardings × average onboarding value + import work + subscription MRR × months active. Y1 runs April–December 2026 (9 months following first contract). Subscription revenue is prorated — clients onboarded mid-year contribute partial-year MRR.

Conservative case assumes 3 onboardings at floor price and slow subscription uptake. Realistic case assumes 5 onboardings with Piroir as public case study driving referrals, all clients on Standard subscription tier.

| | Y1 (Conservative) | Y1 (Realistic) | Y2 | Y3 |
|---|---|---|---|---|
| Onboardings closed | 3 | 5 | 6 | 8 |
| Onboarding revenue | $15,000 | $25,000 | $30,000 | $40,000 |
| Import revenue | $4,500 | $7,500 | $9,000 | $12,000 |
| Web design revenue | $0 | $4,000 | $8,000 | $12,000 |
| Subscription MRR (end of year) | $750 | $1,250 | $2,750 | $5,000 |
| Subscription revenue (in-year) | $2,250 | $5,000 | $24,000 | $48,000 |
| **Total revenue** | **$21,750** | **$41,500** | **$71,000** | **$112,000** |
| Total fixed costs | $444 | $444 | $444 | $444 |
| **Net (pre-tax)** | **~$21,300** | **~$41,000** | **~$70,500** | **~$111,500** |

Notes: All figures CAD. No salaries or subcontractor costs at current stage. GST/QST registration required if Y1 realistic or Y2 conservative revenue is achieved — monitor against $30,000 threshold. Y2 and Y3 subscription revenue assumes clients retained from prior years.

### 8.5 Break-Even Analysis

Break-even is the minimum monthly revenue to cover fixed costs and founder draw. As a sole proprietorship, draw is 100% of net profit. The operating target is a net founder draw of $40,000+ CAD/year.

Gross revenue required to net ~$40,000 after Québec provincial + federal income tax and QPP contributions at sole proprietor rates: approximately $52,000–$55,000 CAD/year, or **~$4,400/month**.

| Item | Amount (CAD/month) |
|---|---|
| Fixed costs | ~$37 |
| Founder draw (target gross) | ~$4,400 |
| **Break-even revenue** | **~$4,437** |
| At $150/hr (import/technical): hours/month required | ~30 hours |
| At $200/hr (web design): hours/month required | ~22 hours |

In practice, revenue is not hourly — onboarding fees and subscriptions are the primary mechanisms. At 5 subscription clients ($1,250 MRR) plus one onboarding per quarter ($5,000 / 3 months = $1,667/month equivalent), monthly revenue is approximately $2,900. Break-even on subscriptions alone requires approximately 18 clients at Standard tier — a Year 3 target, not Year 1. Onboarding revenue bridges the gap in Y1 and Y2.

---

## 9. IP & Proprietary Assets

### 9.1 umt.studio CMS Framework

The base plugin (`umt-studio`) and base theme (`umt-design`) are released under the GNU General Public License (GPL). This is consistent with WordPress ecosystem norms. GPL does not preclude charging for configuration, deployment, and ongoing management services.

The proprietary assets are: the methodology for configuring and deploying the system for a specific client, the documentation, and the accumulated domain knowledge. These are not open-sourced.

Per-client child plugins and child themes are derivatives of the base, also GPL. Client work product is client-owned upon final payment. Base framework components remain UMT Studios property.

### 9.2 Documentation & Methodology

Architecture documentation, workflow guides, and infrastructure runbooks constitute a proprietary methodology — the accumulated process knowledge of configuring, deploying, and operating the platform for cultural heritage clients. This methodology is the basis for the onboarding fee and is not open-sourced.



## 10. Milestones & Roadmap

See ROADMAP.md for full version detail, feature lists, and sequenced TBD items. This section summarizes the three-year business arc.

### 10.1 Current State (March 2026)

- v0.2.0 of umt-studio deployed
- Reference client dataset (Piroir) deployed on production infrastructure at v0.2.0
- Infrastructure operational on AWS EC2, ca-central-1
- CI/CD pipeline configured
- Pricing model established

### 10.2 Year 1 — Foundation (2026)

Platform target: v0.x complete. Commerce and newsletter add-ons live and priced. First client (Piroir) launched. Portfolio site live with case study.

Business targets:
- [ ] Piroir contract signed and delivered
- [ ] Three additional onboardings closed
- [ ] Subscription MRR: \$750–\$1,250 (3–5 clients)
- [ ] Commerce and newsletter add-on pricing finalized (ROADMAP.md §v0.x)
- [ ] Portfolio site live — case studies, methodology, public product page
- [ ] Bookkeeping setup established
- [ ] Business bank account opened
- [ ] GST/QST registration if revenue threshold approached

### 10.3 Year 2 — Expansion (2027)

Platform target: v1.0 and v1.x complete. CRM and financial layer live. Marketing platform (v2.0) in active development. Social publishing add-on priced and available. Platform credibly competes with Artlogic and Arternal on internal gallery operations for the first time. First commercial gallery client onboarding using CRM and invoicing.

Business targets:
- [ ] Six additional onboardings (cumulative: ~10)
- [ ] Subscription MRR: \$2,750 (11 clients)
- [ ] CRM add-on and invoicing add-on pricing finalized (ROADMAP.md §v1.0, §v1.x)
- [ ] First Artlogic migration completed — import tooling proven
- [ ] First commercial gallery client (Blouin Division scale) under active pitch or contracted
- [ ] Revenue sufficient to consider first subcontract engagement for design or front-end

### 10.4 Year 3 — Platform Parity (2028)

Platform target: v2.0, v2.x, and v3.0 complete. Full-stack parity with Artlogic on all non-mobile service lines. REST API live. Artsy integration available pending partner API negotiation. Marketing platform add-on priced and available. All service line pricing established.

Business targets:
- [ ] Eight additional onboardings (cumulative: ~18)
- [ ] Subscription MRR: \$5,000+ (20 clients across tiers and add-ons)
- [ ] All add-on pricing finalized
- [ ] International client or conference presence
- [ ] Revenue projection: \$112,000 CAD (see §8.4)
- [ ] Evaluate incorporation, first hire, or subcontractor structure
- [ ] DR infrastructure operational (ROADMAP.md §v3.0)

---

## 11. Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Single founder — illness, burnout | Medium | High | Subscription contracts with clear SLAs; documented runbooks; subcontractor relationships |
| Client concentration — one or two large clients | High (early stage) | High | Diversify client base; subscriptions spread risk |
| Grant cycle dependency — clients can't pay until grant clears | High | Medium | Payment schedules aligned to grant disbursement; deposits on signing. Note: grants are a secondary sales avenue; the business model does not depend on them |
| AWS outage / data loss | Low | High | Regular backups; document recovery procedures |
| ACF licensing change | Low | Medium | Minimize ACF Pro dependencies; document field groups in JSON for portability |
| WordPress ecosystem deprecation | Low | High | Standards-based data model (field values, not WP-specific APIs) reduces lock-in |
| Artlogic builds an archival product line | Low | Medium | Their sales-oriented client base and revenue model actively prevent it. Structural advantage — they cannot adopt FRBR/AAT without rebuilding their data model and breaking existing client data. Moat is architectural, not just strategic. |
| Competitor offering similar archival-grade CMS | Low (current) | Medium | Methodology and domain knowledge are the moat, not the software. GPL licence encourages forks but not managed competitors — the value is in deployment and domain expertise, not the codebase alone. |
| Scope creep on monthly subscription | High | Medium | Contract language explicitly limits support scope; extra work billed separately |
| Client presses legal action over data loss or downtime | Low | High | E&O insurance required before first client contract; clear SLA and limitation of liability in contract |
| Tax compliance — GST/QST threshold | Medium | Medium | Monitor revenue; register proactively before threshold |
| Feature gap vs Artlogic during Y1–Y2 | High (early stage) | Medium | Addressed by three-year platform roadmap. Archive and public website are complete at v0.x; CRM and invoicing follow at v1.x. During Y1–Y2, pitch to clients where archive is the primary need and CRM is secondary or handled separately. |

---

## 12. Legal & Administrative

### 12.1 Business Registration

Sole proprietorship operating under the founder's name. Registration with the Registraire des entreprises du Québec (REQ) to be confirmed; NEQ number to be recorded here on issuance.

### 12.2 Tax

- **Income tax:** Personal income tax as sole proprietor (T1 / TP-1). Business income reported on T2125 / TP-80.
- **GST/QST:** Not yet registered. Mandatory at \$30,000 CAD revenue — monitor and register proactively before threshold.
- **Fiscal year:** Calendar year (January–December)

### 12.3 Insurance

Professional liability (E&O) insurance is required before the first client contract is signed. Limitation of liability clause in the standard contract is the operative protection in the interim.

### 12.4 Contracts

Standard client contract covers: scope, deliverables, payment schedule, IP ownership, limitation of liability, change order process, termination, and data portability on offboarding.

Key clauses:
- Explicit definition of support scope (questions + breakfix only; no feature work)
- Limitation of liability
- Data portability — client's right to export their data on termination
- IP ownership — client owns their content and data; UMT Studios owns the base framework
- Import completeness — contractor makes reasonable efforts; no guarantee of field completeness
- T&M ceiling approval — client signs off on import ceiling before work begins

### 12.5 Banking

Separate business bank account to be opened before first client invoice. Personal and business finances are kept strictly separated.

---

## 13. Appendix

### A. Glossary

| Term | Definition |
|---|---|
| AAT | Art & Architecture Thesaurus (Getty) — controlled vocabulary for art and material culture |
| ACF | Advanced Custom Fields — WordPress plugin for custom field management |
| CDWA | Categories for the Description of Works of Art |
| CPT | Custom Post Type (WordPress) |
| EDTF | Extended Date/Time Format (ISO 8601-2) |
| FRBR | Functional Requirements for Bibliographic Records — entity model for works, agents, events |
| GPL | GNU General Public License |
| MAP | Museums Assistance Program (Canadian Heritage) |
| MRR | Monthly Recurring Revenue |
| REQ | Registraire des entreprises du Québec |
| T&M | Time and Materials |
| ULAN | Union List of Artist Names (Getty) |
| VRA Core | Visual Resources Association Core — metadata standard for visual culture |

### B. Key Documents

| Document | Location |
|---|---|
| System architecture | `ARCHITECTURE.md` |
| Client onboarding workflow | `WORKFLOW.md` |
| Infrastructure & server config | `INFRASTRUCTURE.md` |
| Platform roadmap (versioned milestones) | `ROADMAP.md` |
| Changelog | `CHANGELOG.md` |

### C. References & Standards

- Getty Art & Architecture Thesaurus: https://www.getty.edu/research/tools/vocabularies/aat/
- Getty ULAN: https://www.getty.edu/research/tools/vocabularies/ulan/
- VRA Core 4.0: https://www.loc.gov/standards/vracore/
- CDWA: https://www.getty.edu/research/publications/electronic_publications/cdwa/
- Schema.org: https://schema.org
- EDTF / ISO 8601-2: https://www.loc.gov/standards/datetime/
- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/
- Registraire des entreprises du Québec: https://www.registreentreprises.gouv.qc.ca/
- Museums Assistance Program — Collections Management: https://www.canada.ca/en/canadian-heritage/services/funding/museums-assistance/collections-management.html
- LibraryHost (comparable managed archival hosting): https://libraryhost.com/pricing/
