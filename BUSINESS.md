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

*Written last. Summarizes the entire document in ~1 page. Audience: investor, grant committee, strategic partner.*

**[TBD — complete after all other sections are drafted.]**

Key points to cover:
- What UMT Studios does and for whom
- The problem it solves
- The product/service differentiator
- Revenue model summary
- Current stage and immediate funding or growth target (if any)

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

The product itself will have evolved to be an integrated dashboard that manages data, inventory, digital assets, and communication. Social media campaign writing and scheduling will be done in-app, using data and assets from the collection. Optional integration of AI will analyze the database and analytics to suggest marketing campaigns derived from the company's information.

### 2.4 Values

- **Standards alignment** — semantic HTML, schema.org microdata, AAT, FRBR, EDTF; interoperability by default
- **Archival quality** — structured and linked data, enterprise-grade stability and performance
- **Design sensibility** — attention to user needs, careful treatment of philosophical questions
- **Information wants to be free** — everything is transparent, documented, and invoiced

### 2.5 History & Context

I have this sense, the end of the *Phenomenology*, that somewhere in there is a hidden set of relations that could fulfill the ultimate, impossible promise of language and effectuate a real movement between author and reader. Of course, I always tend to fanboyishly want to believe that any highly complex object holds the potential for Absolute Knowledge.

Libraries are precisely the kind of complex systems that I am drawn to. It took me a long time, but I realized that the knowledge and experience gained in systems and network administration, web design, programming, researching bibliographic standards, and studying dialectics actually converged at a single point. This studio is the result of that.

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

**Core capabilities:**
- Custom post types: Works, Agents, Events (FRBR-aligned)
- ACF field groups: archival metadata aligned with CDWA, VRA Core, schema.org
- Controlled vocabulary: AAT-aligned work type taxonomy, seeded on activation
- Agent name logic: sort key vs display name, Person/Organization typed
- Schema.org JSON-LD output: VisualArtwork (Works); Events and Agents planned
- CI/CD ready: git-based deploy, GitHub Actions → AWS SSM

**Licensing model:** GPL. The base plugin and theme are released under the GNU General Public License. This is consistent with WordPress ecosystem norms and does not preclude charging for the specialist work of configuring and deploying the system for a specific client. The onboarding fee (§3.1.2) is a service fee, not a licence fee.

**Current status:** v0.2.0, testing with data from potential client Piroir. Base repos: `umt-studio`, `umt-design`.

---

## 4. Market & Positioning

### 4.1 Target Market

**Primary:** Small to mid-size cultural institutions in Québec and Canada — artist-run centres, commercial galleries, print studios, small museums, media companies, archives, foundations, cinemas, retailers producing editorial content

**Secondary:** Individual artists, writers, and practitioners who need archival-grade personal websites

**Tertiary:** Organizations in adjacent fields — libraries, heritage organizations, academic research projects with public-facing collections

### 4.2 Market Context

**[TBD — quantify where possible.]**

- Number of artist-run centres in Canada: TBD (ARCA member count as proxy)
- Number of commercial galleries in Québec: TBD
- Typical web budget range for target clients: TBD
- Grant funding landscape: Canada Council for the Arts, CALQ, Museums Assistance Program (MAP) — many target clients are grant-funded, meaning budget is real but timing is grant-cycle dependent

**Grant opportunity — Museums Assistance Program (MAP), Collections Management component:**
The MAP Collections Management grant covers up to 75% of eligible project costs to a maximum of \$400,000 per project. Eligible expenses include consultant fees, purchase and implementation of collections management systems, cataloguing, staff training, and software. The umt.studio onboarding and data import services map directly onto eligible expense categories. The annual application deadline is November 1; funded projects run April 1–March 31. Grant funding is a secondary sales avenue — the primary model does not depend on it — but clients should be made aware of it. Best approached in August–September to allow time for scoping and application before the November deadline. Note: MAP grants are highly competitive; approval should not be treated as guaranteed by either party.

### 4.3 Problem Statement

Cultural institutions manage collections that have archival requirements — provenance, attribution, controlled vocabulary, rights metadata — but are typically served by generic CMS solutions (WordPress with off-the-shelf themes, Squarespace, Cargo) that treat all content as blog posts. The result is data that cannot be queried, exported, or integrated with library and museum systems.

At the other end, museum-grade collection management software (CollectiveAccess, Omeka, TMS) is expensive, complex to operate, and requires dedicated staff. There is a gap for institutions too small for enterprise CMS but who want to treat their data more seriously than with a blog platform.

### 4.4 Competitive Landscape

| Competitor | Positioning | Gap |
|---|---|---|
| Generic WordPress agencies | Fast, cheap, template-driven | No data modelling; no IA depth |
| CollectiveAccess / Omeka | Museum-grade CMS | Expensive, complex, requires dedicated staff |
| LibraryHost (managed Omeka/AtoM) | Managed archival hosting, \$34–\$179 USD/month | No design; no public-facing website; generic templates; metered support |
| Cargo / Squarespace | Portfolio/artist sites | No metadata, no queryable data, no longevity |
| Freelance developers | Cost-competitive | No domain knowledge in cultural heritage |
| Museum IT departments | Institutional expertise | Not available to small institutions |

**UMT Studios sits between freelance WordPress shop and museum IT.** The differentiator is domain knowledge in information architecture, metadata standards, and controlled vocabulary, combined with the technical depth to build and operate production infrastructure, and the design sensibility to produce a public-facing site that represents the institution.

### 4.5 Positioning Statement

**[TBD — one crisp sentence.]**

Draft: UMT Studios builds archival web infrastructure for institutions that take their data seriously.

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

**[TBD — current capacity in billable hours/week; maximum sustainable load; threshold for subcontracting or hiring.]**

---

## 6. Team

### 6.1 Founder

**[TBD — bio, relevant experience, domain expertise.]**

Key credentials relevant to the market:
- **[TBD]**

### 6.2 Advisors

**[TBD]**

### 6.3 Hiring Plan

**Phase 1 (current):** Sole proprietorship. All work founder-operated.

**Phase 2 (trigger: TBD revenue threshold):** Subcontract design or front-end development on a per-project basis.

**Phase 3 (trigger: TBD):** First hire — junior developer or project coordinator.

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

**[TBD — describe how a lead becomes a client: intake, scoping, proposal, contract, deposit.]**

Typical sales cycle for cultural institution clients: long (3–12 months from first contact to signed contract). Grant cycles govern budget timing.

### 7.3 Proposal & Contract

**[TBD — describe proposal format, contract type, deposit structure, payment schedule.]**

### 7.4 Client Retention

The monthly subscription is the primary retention mechanism. Every onboarding engagement concludes with a subscription offer. Target: TBD% of onboarding clients on subscription within 12 months of delivery.

---

## 8. Revenue Model & Financials

### 8.1 Revenue Streams

| Stream | Type | Pricing Model |
|---|---|---|
| CMS Onboarding | Project | Assessed fixed quote |
| Data Import | Project | T&M with floor and client-approved ceiling |
| Monthly Subscription | Retainer | Flat monthly, tiered by storage |
| Newsletter add-on | Retainer | Monthly flat, tiered by subscriber count / send volume |
| Ecommerce add-on | Retainer | Monthly flat — TBD tiers |
| Web Design | Project | T&M with minimum |

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

| Add-on | Notes | Pricing |
|---|---|---|
| Newsletter (listmonk) | Tiered by subscriber count and monthly send volume | TBD |
| Ecommerce | TBD platform | TBD |

Add-on pricing is TBD pending operational experience with provisioning and ongoing maintenance time per client.

#### Web Design

| | |
|---|---|
| **Rate** | $200 CAD / hour |
| **Project minimum** | TBD |

Web design is optional and separate from the CMS product. T&M with a project minimum. Minimum TBD pending first web-design-only engagement.

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

Break-even is the minimum monthly revenue to cover fixed costs and founder draw. As a sole proprietorship, draw is 100% of net profit — there is no salary/dividend distinction. The target draw is set to clear the Montreal single-person low-income threshold (MBM ~$28,000 CAD/year after tax), with a comfortable margin.

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

Architecture documentation, workflow guides, and infrastructure runbooks constitute a proprietary methodology. Not currently formalized as a product but have value as the basis for a consulting practice or training offering.

### 9.3 Client Work

Work product created for clients is client-owned upon final payment unless otherwise specified in the contract. Base framework components remain UMT Studios property.

**[TBD — formalize in standard contract language.]**

---

## 10. Milestones & Roadmap

### 10.1 Current State

- v0.2.0 of umt-studio deployed
- One active testing database based on Piroir's dataset
- Infrastructure operational on AWS EC2, ca-central-1
- CI/CD pipeline configured
- Pricing model established (2026-03-26)

### 10.2 90-Day Targets

- [ ] Pitch Piroir and three other potential clients
- [ ] Complete Piroir child theme (umt-design-piroir)
- [ ] Migrate Piroir data and set up infrastructure
- [ ] Formalize proposal and contract templates
- [ ] Establish bookkeeping setup
- [ ] Write the new DB schema
- [ ] Live version of the admin UI
- [ ] Portfolio site live
- [ ] Schema.org engine extended to Events and Agents
- [ ] Staging environment established
- [ ] Agent role model implemented (ACF Pro Repeater migration)
- [ ] DAM capabilities within interface
- [ ] Newsletter and ecommerce add-on pricing finalized
- [ ] Web design rate and minimum finalized

### 10.3 Year 1 Targets

- [ ] Ten completed onboardings
- [ ] \$1,000 MRR from subscriptions
- [ ] GST/QST registration (if threshold met or anticipated)
- [ ] Marketing scheduling within interface

### 10.4 Year 3 Targets

**[TBD]**

- [ ] \$3,000 MRR
- [ ] International profile, travel
- [ ] Anthropological research

---

## 11. Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Single founder — illness, burnout | Medium | High | Subscription contracts with clear SLAs; documented runbooks; subcontractor relationships |
| Client concentration — one or two large clients | High (early stage) | High | Diversify client base; subscriptions spread risk |
| Grant cycle dependency — clients can't pay until grant clears | High | Medium | Payment schedules aligned to grant disbursement; deposits on signing |
| AWS outage / data loss | Low | High | Regular backups; document recovery procedures |
| ACF licensing change | Low | Medium | Minimize ACF Pro dependencies; document field groups in JSON for portability |
| WordPress ecosystem deprecation | Low | High | Standards-based data model (field values, not WP-specific APIs) reduces lock-in |
| Competitor offering similar archival-grade CMS | Low (current) | Medium | Methodology and domain knowledge are the moat, not the software |
| Scope creep on monthly subscription | High | Medium | Contract language explicitly limits support scope; extra work billed separately |
| Client presses legal action over data loss or downtime | Low | High | E&O insurance required before first client contract; clear SLA and limitation of liability in contract |
| Tax compliance — GST/QST threshold | Medium | Medium | Monitor revenue; register proactively before threshold |

---

## 12. Legal & Administrative

### 12.1 Business Registration

**[TBD — Registraire des entreprises du Québec (REQ): confirm registration status, NEQ number.]**

### 12.2 Tax

- **Income tax:** Personal income tax as sole proprietor (T1 / TP-1). Business income reported on T2125 / TP-80.
- **GST/QST:** Not yet registered. Mandatory at \$30,000 CAD revenue — monitor and register proactively before threshold.
- **Fiscal year:** Calendar year (January–December)

### 12.3 Insurance

**Professional liability (E&O) insurance:** Deferred. Not obtained at current stage due to capital constraints. Limitation of liability clause in the standard client contract is the operative protection. Revisit when revenue allows.

### 12.4 Contracts

**[TBD — standard client contract covering: scope, deliverables, payment schedule, IP ownership, limitation of liability, change order process, termination, data portability on offboarding.]**

Key clauses required:
- Explicit definition of support scope (questions + breakfix only; no feature work)
- Limitation of liability
- Data portability — client's right to export their data on termination
- IP ownership — client owns their content and data; UMT Studios owns the base framework
- Import completeness — contractor makes reasonable efforts; no guarantee of field completeness
- T&M ceiling approval — client signs off on import ceiling before work begins

### 12.5 Banking

**[TBD — separate business bank account. Keep personal and business finances strictly separated from day one.]**

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
| Deferred items | `DEFERRED.md` |
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
