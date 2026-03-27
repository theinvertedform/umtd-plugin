# umt.studio CMS

Archival web infrastructure for cultural institutions — artist-run centres, galleries, print studios, and small museums.

**License:** GPL | **Current version:** v0.2.0 | **Status:** Prototype — founding client onboarding open

---

## What this is

The umt.studio CMS is a white-label WordPress framework built on archival data standards. It is not a theme or a page builder. It is a structured data platform that treats a cultural institution's collection as a permanent public record — not a rolling catalogue of what is currently available.

Works, Agents, and Events are registered as first-class archival objects with controlled vocabulary (AAT), standards-aligned metadata (CDWA, VRA Core 4.0, FRBR), schema.org JSON-LD output, and full data portability on exit. The platform is deployed on managed Canadian infrastructure (AWS EC2, ca-central-1) and operated as a SaaS — clients do not manage their own servers.

---

## The problem

Cultural institutions manage collections with real archival requirements — provenance, attribution, controlled vocabulary, rights metadata — but are typically served by two inadequate alternatives.

**Generic platforms** (Squarespace, WordPress off-the-shelf) treat all content as blog posts. Data cannot be queried, exported, or integrated with library and museum systems. When the platform lapses or the template becomes obsolete, the institutional record goes with it.

**Commercial gallery software** (Artlogic, Arternal) is built on a sales-and-inventory paradigm. A work record is an inventory item, not a cultural document. Historical exhibitions, past agents, the long program of an institution — none of this is a first-class object in an inventory model. Data exports as flat CSV with no standards alignment: no AAT IDs, no FRBR relationships, no schema.org structure.

**Museum-grade CMS** (CollectiveAccess, Omeka) is expensive, complex to operate, and requires dedicated staff. It is not available to a mid-size gallery or artist-run centre.

The gap is real and unserved: institutions that take their data seriously but cannot staff a museum IT department, and who need a platform that treats their archive as a permanent public record while also supporting the commercial operations that keep the institution funded.

---

## What it does

**Archive (v0.x — current)**
- Custom post types: Works, Agents, Events — FRBR-aligned
- ACF field groups: metadata aligned with CDWA, VRA Core 4.0, schema.org
- Controlled vocabulary: AAT-aligned work type taxonomy, seeded on activation
- Agent model: Person / Organization typed, sort key vs display name, ULAN and Wikidata identifiers
- Schema.org JSON-LD: VisualArtwork on Works singles; Events and Agents planned
- Bilingual: FR/EN slug architecture, custom translation table
- DAM: structured image metadata — view type, rights, license, credit line, related work
- Commerce: WooCommerce integration with work availability sync (v0.x)
- Newsletter: listmonk integration (v0.x)

**CRM (v1.0 — Y1–Y2)**
Collector contact management, inquiry tracking, acquisition history, private viewing rooms. Built on the existing agent and work data model — no new architectural primitives required.

**Financial layer (v1.x — Y2)**
Invoicing, consignment tracking, artist payables, edition inventory. Closes the primary administrative gap that Artlogic and Arternal both target.

**Marketing platform (v2.0 — Y2–Y3)**
Social media publishing (Meta platforms) from WP admin — compose and schedule posts drawing on collection data and digital assets already in the system. Deep listmonk integration, campaign scheduling, online viewing rooms, analytics dashboard. The institutional archive becomes the source of the marketing calendar.

**Full sales pipeline (v2.x — Y3)**
Offer tracking, collector preference profiling, sales pipeline dashboard, AI-assisted campaign suggestions.

**Platform parity (v3.0 — EOY3)**
REST API, Artlogic migration tooling, Artsy integration (pending partner API negotiation), disaster recovery infrastructure. Full feature parity with Artlogic across all non-mobile service lines.

---

## Architecture

Base plugin (`umt-studio`) + base theme (`umt-design`), extended per client via child plugin + child theme. New client = new child plugin + new child theme. Base repos are never modified for client work.

| Repo | Purpose |
|---|---|
| `umt-studio` | CPTs, taxonomies, ACF fields, schema.org, agent logic |
| `umt-design` | Semantic HTML templates, BEM CSS, no client branding |
| `umt-studio-{client}` | Work type whitelist, client-specific ACF overrides |
| `umt-design-{client}` | Client typography, colour, layout, branding |

Infrastructure: AWS EC2 (ca-central-1), nginx, PHP-FPM 8.4, MariaDB, PostgreSQL, Let's Encrypt, GitHub Actions → AWS SSM CI/CD. Full infrastructure documentation in `INFRASTRUCTURE.md`.

---

## Standards

| Standard | Application |
|---|---|
| AAT (Getty) | Controlled vocabulary for work types, media, roles |
| FRBR | Entity model — Works, Agents, Events as first-class objects |
| CDWA | Metadata categories for works description |
| VRA Core 4.0 | Visual resource metadata fields |
| schema.org | JSON-LD structured data output |
| EDTF / ISO 8601-2 | Date representation including partial dates and qualifiers |
| ULAN (Getty) | Agent authority identifiers |

---

## Licensing

The base plugin (`umt-studio`) and base theme (`umt-design`) are released under the GNU General Public License (GPL). This is consistent with WordPress ecosystem norms. GPL does not preclude charging for the specialist work of configuring, deploying, and operating the system for a specific client — the onboarding fee is a service fee, not a licence fee.

Per-client child plugins and child themes are also GPL. Client data and content are client-owned.

---

## Current status and roadmap

The platform is at v0.2.0 and in active prototype deployment. Core archive functionality is complete. The founding client onboarding slot is open — the first client engagement funds and sequences the next development phase.

The sequenced roadmap is in `ROADMAP.md`. The platform reaches full Artlogic feature parity at v3.0 (EOY 2028), built on a correct archival data model that competitors cannot replicate without rebuilding from scratch.

---

## Business

UMT Studios is a Montréal-based sole proprietorship offering the umt.studio CMS as a managed SaaS.

- **Onboarding:** From \$5,000 CAD — information architecture, schema design, child plugin and theme, production deployment
- **Monthly subscription:** \$250–\$500 CAD — managed hosting, supervised updates, breakfix, support
- **Data import:** T&M — \$150/hr technical, \$250/hr archival research
- **Web design:** \$200/hr, from \$3,000 CAD project minimum

The platform is licensed GPL. The service — configuration, deployment, and ongoing management — is not.

---

## Contact

Uriah Marc Todoroff — [umt@umt.world](mailto:umt@umt.world)
