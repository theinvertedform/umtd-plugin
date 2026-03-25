# umt.studios---Business Plan

*Founding document. Last revised: 2026-03-25.*
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

UMT Studios will be working on grant-funded projects to catalogue and present information to the public. We will be designing catalogues with specific scope, conducting research, fact-checking, and undertaking original investigative work in order to collect data and design user experiences in the public interest. We will be contributing to research into culture through the creation of information-rich databases. We will be developing websites that function as public information repositories. providing services to galleries and other cultural institutions locally and internationally.

The product itself will have evolved to be an integrated dashboard that manages data, inventory, digital assets, and communication. Social media campaign writing and scheduling will be done in-app, using data and assets from the collection. Optional integration of AI will analyze the database and analytics to suggest marketing campaigns derived from the company's information.

### 2.4 Values

- **Standards alignment** — semantic HTML, schema.org microdata, AAT, FRBR, EDTF; interoperability by default
- **Archival quality** — structured and linked data, enterprise-grade stability and performance
- **Design sensibility** — attention to user needs, careful treatment of philosophical questions
- **Information wants to be free** — everything is transparent, documented, and invoiced

### 2.5 History & Context

I have this sense, the end of the *Phenomenology*, that somewhere in there is a hidden set of relations that could fulfill the ultimate, impossible promise of language and effectuate a real movement between author and reader. Of course, I always tend to fanboyishly want to believe that any highly complex object holds the potential for Absolute Knowledge.

Libraries are precisely the kind of complex systems that I am drawn to. It took me a long time, but I realized that I could put the knowledge and experience gained in systems and network administration, web design, programming, researching bibliographic standards, and studying dialectics actually converged at a single point. This studio is the result of that.

---

## 3. Services & Products

### 3.1 Service Lines

#### 3.1.1 Web Design & Development

Custom website design and development. Static sites, WordPress, bespoke CMS integrations. Emphasis on semantic markup, accessibility, and performance.

- **Deliverables:** Design system, HTML/CSS/JS front-end, CMS integration, documentation
- **Typical engagement:** Fixed-scope project, 4–16 weeks
- **Target client:** Small cultural institutions, galleries, artist-run centres, independent practitioners, cinemas, libraries, museums, fashion designers, clothing stores

#### 3.1.2 WordPress Development

Plugin and theme development, custom post types, ACF field groups, REST API, performance, security hardening. Both greenfield and legacy site overhauls.

- **Deliverables:** Plugin(s), theme, field group JSON, deployment runbook
- **Typical engagement:** Fixed-scope or retainer
- **Differentiator:** Archival-grade data modelling (FRBR-aligned CPTs, controlled vocabulary, schema.org JSON-LD), combined with a purpose-built UI with marketing integration

#### 3.1.3 Systems Administration & Infrastructure

Linux server provisioning and maintenance (Ubuntu/Debian, AWS EC2, nginx, PHP-FPM, MariaDB, PostgreSQL). SSL, CI/CD pipelines, backup strategy, service monitoring.

- **Deliverables:** Provisioned server, documentation, runbook, ongoing retainer
- **Typical engagement:** Project + monthly retainer
- **Target client:** Existing clients who need hosting; small orgs without internal sysadmin

#### 3.1.4 Information Architecture & Content Strategy

Taxonomy design, metadata schema development, controlled vocabulary curation (AAT, ULAN, Wikidata alignment), data modelling for collections and archives.

- **Deliverables:** IA document, taxonomy specification, field map, migration guide
- **Typical engagement:** Discovery/strategy phase of a larger project, or standalone engagement
- **Differentiator:** FRBR, CDWA, VRA Core 4.0, schema.org fluency; not generic IA

#### 3.1.5 Ongoing Maintenance & Support

Monthly retainer covering plugin updates, security monitoring, content entry support, minor feature additions, server maintenance.

- **Deliverables:** Monthly report, response SLA, ticketing channel
- **Tiers:** TBD (see §8)

### 3.2 Product: umt.studio CMS

White-label WordPress CMS framework — base plugin (`umt-studio`) + base theme (`umt-design`), extended per client via child plugin + child theme.

**Core capabilities:**
- Custom post types: Works, Agents, Events (FRBR-aligned)
- ACF field groups: archival metadata aligned with CDWA, VRA Core, schema.org
- Controlled vocabulary: AAT-aligned work type taxonomy, seeded on activation
- Agent name logic: sort key vs display name, Person/Organization typed
- Schema.org JSON-LD output: VisualArtwork (Works); Events and Agents planned
- CI/CD ready: git-based deploy, GitHub Actions → AWS SSM

**Licensing model:** **[TBD]** — currently deployed as bespoke per-client. Future options: (a) remain bespoke white-label, (b) open-source base with paid child plugin support, (c) hosted SaaS. Decision deferred until client base justifies the overhead.

**Current status:** v0.2.0, testing with data from potential client Piroir. Base repos: `umt-studio`, `umt-design`.

---

## 4. Market & Positioning

### 4.1 Target Market

**Primary:** Small to mid-size cultural institutions in Québec and Canada — artist-run centres, commercial galleries, print studios, small museums, media companies, archives, foundations, cinemas, media companies, retailers producing editorial content

**Secondary:** Individual artists, writers, and practitioners who need archival-grade personal websites

**Tertiary:** Organizations in adjacent fields — libraries, heritage organizations, academic research projects with public-facing collections.

### 4.2 Market Context

**[TBD — quantify where possible.]**

- Number of artist-run centres in Canada: TBD (ARCA member count as proxy)
- Number of commercial galleries in Québec: TBD
- Typical web budget range for target clients: TBD
- Grant funding landscape: Canada Council for the Arts, CALQ, CAC digital infrastructure grants — many target clients are grant-funded, meaning budget is real but timing is grant-cycle dependent

### 4.3 Problem Statement

Cultural institutions manage collections that have archival requirements — provenance, attribution, controlled vocabulary, rights metadata — but are typically served by generic CMS solutions (WordPress with off-the-shelf themes, Squarespace, Cargo) that treat all content as blog posts. The result is data that cannot be queried, exported, or integrated with library and museum systems.

At the other end, museum-grade collection management software (CollectiveAccess, Omeka, TMS) is expensive, complex to operate, and requires dedicated staff. There is a gap for institutions too small for enterprise CMS but who want to treat their data more seriously than with a blog platform.

### 4.4 Competitive Landscape

| Competitor | Positioning | Gap |
|---|---|---|
| Generic WordPress agencies | Fast, cheap, template-driven | No data modelling; no IA depth |
| CollectiveAccess / Omeka | Museum-grade CMS | Expensive, complex, requires dedicated staff |
| Cargo / Squarespace | Portfolio/artist sites | No metadata, no queryable data, no longevity, hostile interface |
| Freelance developers | Cost-competitive | No domain knowledge in cultural heritage |
| Museum IT departments | Institutional expertise | Not available to small institutions |

**UMT Studios sits between freelance WordPress shop and museum IT.** The differentiator is domain knowledge in information architecture, metadata standards, controlled vocabulary, combined with the technical depth required to build and operate production infrastructure.

### 4.5 Positioning Statement

**[TBD — one crisp sentence.]**

Draft: UMT Studios builds archival web infrastructure for institutions that take their data seriously.

### 4.6 Value Proposition by Audience

| Audience | Value |
|---|---|
| Gallery director | Permanent, queryable record of the collection; not a marketing site that rots |
| Curator / registrar | Proper metadata, controlled vocabulary, exportable data |
| Artist / practitioner | Archival-grade personal site without enterprise overhead |
| Grant committee | Standards-aligned, durable, open-source-compatible |

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

Production: AWS EC2, ca-central-1. One instance per client group (current) or per client (scaling). Full documentation maintained in `INFRASTRUCTURE.md` per client deployment.

Client sites are hosted on UMT Studios-managed infrastructure — not handed off to clients on shared hosting. This is both a quality decision and a recurring revenue mechanism (§8).

### 5.3 Delivery Process

1. **Discovery** — scope, IA requirements, client data inventory, vocabulary alignment
2. **Specification** — field map, taxonomy spec, URL architecture, design brief
3. **Development** — child plugin, child theme, ACF overrides, content migration
4. **Staging review** — client UAT on staging environment
5. **Production deploy** — DNS cutover, SSL, CI/CD pipeline
6. **Handoff** — documentation, training, retainer onboarding

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

**[TBD — describe proposal format, contract type (fixed-scope vs T&M), deposit structure, payment schedule.]**

### 7.4 Client Retention

Maintenance retainers are the primary retention mechanism. Every project should conclude with a retainer offer. Target: TBD% of project clients on retainer within 12 months of delivery.

---

## 8. Revenue Model & Financials

### 8.1 Revenue Streams

| Stream | Type | Pricing Model |
|---|---|---|
| Web design & development | Project | Fixed-scope or T&M |
| WordPress development | Project | Fixed-scope or T&M |
| Systems administration | Project + retainer | Day rate + monthly flat |
| IA / content strategy | Project | Fixed-scope |
| Hosting & infrastructure | Retainer | Monthly flat per client |
| Maintenance & support | Retainer | Monthly flat, tiered |
| umt.studio CMS licensing | TBD | TBD (see §3.2) |

### 8.2 Pricing

**[TBD — set actual rates. Suggested structure below as placeholder.]**

| Service | Rate |
|---|---|
| Day rate (design / development) | TBD / day |
| Day rate (sysadmin) | TBD / day |
| Day rate (IA / strategy) | TBD / day |
| Hosting retainer (per client, per month) | TBD / month |
| Maintenance retainer — Basic | TBD / month |
| Maintenance retainer — Standard | TBD / month |
| Maintenance retainer — Priority | TBD / month |

**Québec tax obligations:** GST (5%) + QST (9.975%) apply to services rendered to clients in Québec. Small supplier threshold: \$30,000 CAD revenue before mandatory registration. **[TBD: confirm current registration status and threshold.]**

### 8.3 Cost Structure

**Fixed costs (monthly):**

| Item | Cost (CAD/month) |
|---|---|
| AWS EC2 (production) | TBD |
| AWS data transfer / SES | TBD |
| Domain registrations | TBD |
| Software / SaaS subscriptions | TBD |
| Accounting software (Wave or equivalent) | TBD |
| Professional insurance (E&O) | TBD (annualized / 12) |
| **Total fixed** | **TBD** |

**Variable costs (per project):**

| Item | Notes |
|---|---|
| Subcontracted design / development | TBD % of project revenue |
| Stock assets, fonts, licensed software | Project-specific |

### 8.4 Revenue Projections

**[TBD — Y1, Y2, Y3. Structure as: number of projects × average project value + number of retainer clients × average MRR.]**

| | Y1 | Y2 | Y3 |
|---|---|---|---|
| Project revenue | TBD | TBD | TBD |
| Retainer MRR (end of year) | TBD | TBD | TBD |
| Total revenue | TBD | TBD | TBD |
| Total costs | TBD | TBD | TBD |
| Net | TBD | TBD | TBD |

### 8.5 Break-Even Analysis

**[TBD — minimum monthly revenue to cover fixed costs + founder draw.]**

| Item | Amount (CAD/month) |
|---|---|
| Fixed costs | TBD |
| Founder draw (target) | TBD |
| **Break-even revenue** | **TBD** |
| At day rate of TBD: days/month required | TBD |

---

## 9. IP & Proprietary Assets

### 9.1 umt.studio CMS Framework

The base plugin (`umt-studio`) and base theme (`umt-design`) constitute the primary proprietary asset. Per-client child plugins and child themes are derivatives.

**Current status:** Proprietary, all rights reserved. No open-source licence assigned.

**Considerations:**
- Open-sourcing the base would increase visibility and attract contributors but would require formalizing the API surface and committing to backward compatibility
- Keeping it proprietary preserves optionality for SaaS or licensing models
- GPL is the default for WordPress plugins distributed publicly — if ever distributed via wp.org, GPL compliance is mandatory

**Decision deferred.** Revisit at: TBD milestone.

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

### 10.3 Year 1 Targets

- [ ] Ten completed projects
- [ ] \$1000 MRR from retainers
- [ ] GST/QST registration (if threshold met or anticipated)
- [ ] Marketing scheduling within interface

### 10.4 Year 3 Targets

**[TBD]**

- [ ] \$3000 MRR
- [ ] International profile, travel
- [ ] Anthropological research

---

## 11. Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Single founder — illness, burnout | Medium | High | Retainer contracts with clear SLAs; documented runbooks; subcontractor relationships |
| Client concentration — one or two large clients | High (early stage) | High | Diversify client base; retainers spread risk |
| Grant cycle dependency — clients can't pay until grant clears | High | Medium | Payment schedules aligned to grant disbursement; deposits on signing |
| AWS outage / data loss | Low | High | Regular backups; document recovery procedures; consider multi-AZ or multi-region |
| ACF licensing change | Low | Medium | Minimize ACF Pro dependencies; document field groups in JSON for portability |
| WordPress ecosystem deprecation | Low | High | Standards-based data model (field values, not WP-specific APIs) reduces lock-in |
| Competitor offering similar archival-grade CMS | Low (current) | Medium | Methodology and domain knowledge are the moat, not the software |
| Scope creep — cultural institution clients | High | Medium | Fixed-scope contracts with clear change order process |
| Tax compliance — GST/QST threshold | Medium | Medium | Monitor revenue; register proactively before threshold |

---

## 12. Legal & Administrative

### 12.1 Business Registration

**[TBD — Registraire des entreprises du Québec (REQ): confirm registration status, NEQ number.]**

### 12.2 Tax

- **Income tax:** Personal income tax as sole proprietor (T1 / TP-1). Business income reported on T2125 / TP-80.
- **GST/QST:** **[TBD — registration status. Mandatory at \$30,000 CAD revenue.]**
- **Fiscal year:** Calendar year (January–December)

### 12.3 Insurance

**[TBD — Professional liability (E&O) insurance. Required before any client contract. Source: TBD broker or association.]**

### 12.4 Contracts

**[TBD — standard client contract covering: scope, deliverables, payment schedule, IP ownership, limitation of liability, change order process, termination.]**

### 12.5 Banking

**[TBD — separate business bank account. Recommended: keep personal and business finances strictly separated from day one.]**

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
