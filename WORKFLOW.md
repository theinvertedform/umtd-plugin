# UMT Studio — New Client Workflow

This document covers everything required to onboard a new client onto the umt.studio CMS platform. It assumes the base plugin (`umt-studio`) and base theme (`umt-design`) are stable and deployed to the new server.

---

## Prerequisites

- SSH access to the new server (EC2 or equivalent)
- Fresh WordPress install on the client server
- `umt-studio` and `umt-design` repos cloned and activated on the client server
- ACF free (or Pro) installed and activated
- Git repos initialized for `umt-studio-{client}` and `umt-design-{client}`

---

## Step 1 — Create the Child Plugin

Create a new repo: `umt-studio-{client}`.

Minimum file structure:

```
umt-studio-{client}/
├── umt-studio-{client}.php
└── config/
    └── terms.php
```

### `umt-studio-{client}.php`

Copy `umt-studio-piroir.php` as the starting point. Update:

- `Plugin Name`
- `Text Domain`
- `UMTD_{CLIENT}_PATH` constant
- Function name prefix: `umtd_{client}_`

### `config/terms.php`

Define the client's work type whitelist — a subset of the base plugin vocabulary by term name:

```php
return array(
    'umtd_work_type' => array(
        '300041273' => 'Print',
        // add terms relevant to this client
    ),
);
```

Reference the full vocabulary in `umt-studio/config/terms.php`. Keys are AAT numeric IDs, values are display names. Term identity is the **name** — do not change names of existing terms, as this will break existing assignments.

> ⚠️ **Hung lantern — agent roles:** the base plugin ACF field group currently includes `agents_artists` and `agents_authors` relationship fields. These are Piroir-specific and encode agent roles as field names — a hack pending ACF Pro Repeater migration. For a new client, either: (a) leave them in place and ignore them, (b) hide them via a child plugin ACF override, or (c) wait until ACF Pro is in place and the role model is refactored. See `DEFERRED.md`.

### Activate the child plugin

On the client server, activate `umt-studio-{client}` after `umt-studio`. The activation hook will seed whitelisted terms and remove non-whitelisted ones.

**Dependency:** `umt-studio` must be active before `umt-studio-{client}` is activated. The child plugin requires `UMTD_PATH` to be defined.

---

## Step 2 — Create the Child Theme

Create a new repo: `umt-design-{client}`.

Minimum file structure:

```
umt-design-{client}/
├── style.css       — theme header, Version, Template: umt-design
└── functions.php   — enqueue child styles, client overrides
```

### `style.css` header

```css
/*
 * Theme Name: umt.design — {Client}
 * Template:   umt-design
 * Version:    0.1.0
 * Text Domain: umt-design-{client}
 */
```

`Template: umt-design` is required — this is what makes it a child theme.

### `functions.php`

```php
<?php
add_action( 'wp_enqueue_scripts', 'umt_design_{client}_enqueue' );

function umt_design_{client}_enqueue() {
    wp_enqueue_style(
        'umt-design-{client}',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'umt-design' ),
        wp_get_theme()->get( 'Version' )
    );
}
```

The `array( 'umt-design' )` dependency ensures the base theme stylesheet loads first.

### Activate the child theme

On the client server, activate `umt-design-{client}`. The base theme `umt-design` does not need to be separately activated — WordPress loads it automatically as the parent.

---

## Step 3 — ACF Field Groups

Base plugin field groups load automatically from `umt-studio/acf-json/`. No action required.

> ⚠️ **Hung lantern — ACF save path:** base field groups are read-only on all deployed installs. **Never edit ACF field groups on a client server.** Changes to base field groups must be made on localhost, committed to `umt-studio`, and deployed. See `ARCHITECTURE.md` — ACF Field Groups.

If the client requires additional or modified ACF fields, register them in the child plugin via `acf_add_local_field_group()` on `acf/init`. Do not modify base plugin JSON files on the client server.

---

## Step 4 — WordPress Pages and Nav Menus

The client's editorial nav is built from WordPress pages with custom page templates. Create the following pages and assign templates:

| Page title | Slug | Template |
|---|---|---|
| Events | `/events/` | Events Archive |
| Prints | `/prints/` | Prints Archive |
| Books | `/books/` | Books Archive |
| Artists | `/artists/` | Artists Archive |
| Studio | `/studio/` | Default |

Adjust page titles, slugs, and templates to match the client's nav structure.

After creating pages, go to **Appearance → Menus**:
1. Create a Primary menu — assign to the `primary` location
2. Create a Footer menu — assign to the `footer` location
3. Add the relevant pages to each menu

---

## Step 5 — Flush Rewrite Rules

After activating plugins and theme, flush rewrite rules to ensure CPT and page slugs resolve correctly:

**Settings → Permalinks → Save Changes** (no actual changes needed — just saving flushes rules).

Or deactivate and reactivate `umt-studio` to trigger the activation hook.

---

## Step 6 — Verify

- [ ] `/events/`, `/prints/`, `/books/`, `/artists/` all resolve
- [ ] `/works/`, `/agents/`, `/events/` single post URLs resolve
- [ ] ACF field groups appear on Works, Agents, Events edit screens
- [ ] Work type terms are seeded correctly — check **Posts → Works → Work Types**
- [ ] Nav menus appear in front-end header and footer
- [ ] Child theme stylesheet loading — check browser devtools network tab

---

## Modifying Base Field Groups

1. On **localhost only**, open the ACF field group editor
2. Make changes — ACF will not save automatically (no save path registered)
3. To save: temporarily add a save path to `umt-studio.php` locally, save, then remove it
4. Commit the updated JSON in `umt-studio/acf-json/`
5. Deploy to client servers via `git pull`

> This workflow is awkward. The correct fix is ACF Pro, which supports save paths per plugin and makes local JSON management reliable. See `DEFERRED.md`.

---

## Adding a New Work Type Term to the Base Vocabulary

1. Add the term to `umt-studio/config/terms.php` with its AAT ID as key and display name as value
2. Add it to the client whitelist in `umt-studio-{client}/config/terms.php` if the client needs it
3. Deploy both plugins
4. Re-run `umtd_seed_terms()` — deactivate and reactivate `umt-studio` on the client server, or insert the term manually via WP admin under **Works → Work Types**

