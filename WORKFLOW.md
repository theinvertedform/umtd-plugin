# UMT Studio — New Client Workflow

This document covers the code-side steps to onboard a new client: creating the child plugin and child theme, configuring ACF, and setting up WordPress pages and menus. Server provisioning (nginx, MariaDB, WordPress install, git clone, SSL) is covered in `INFRASTRUCTURE.md` — Client Deploy Process.

---

## Prerequisites

- Server provisioned and WordPress installed per `INFRASTRUCTURE.md`
- `umt-studio` and `umt-design` cloned and activated on the client server
- ACF (free or Pro) installed and activated
- Git repos initialized for `umt-studio-{client}` and `umt-design-{client}`

---

## Step 1 — Create the Child Plugin

Create a new repo: `umt-studio-{client}`.

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

Define the client's work type whitelist — a subset of the base vocabulary by term **name**:

```php
return array(
    'umtd_work_type' => array(
        'Print',
        'Artist Book',
        // add terms relevant to this client
    ),
);
```

A flat indexed array of term names — AAT IDs are omitted because comparison in `umtd_piroir_activate()` is by name only. This differs from the base `config/terms.php` format (`AAT_ID => name`) by design. Term names must exactly match the base vocabulary in `umt-studio/config/terms.php`. Do not rename existing values — this breaks existing term assignments.

> ⚠️ The base ACF field group includes `agents_artists` and `agents_authors` relationship fields. These are Piroir-specific hacks encoding agent role as field name, pending ACF Pro Repeater migration. For a new client: (a) leave them in place and ignore them, (b) hide via child plugin ACF override, or (c) wait for the role model refactor. See `DEFERRED.md`.

### Activate

Activate `umt-studio-{client}` **after** `umt-studio`. The activation hook seeds whitelisted terms and deletes non-whitelisted ones. The child plugin requires `UMTD_PATH` — `umt-studio` must be active first.

---

## Step 2 — Create the Child Theme

Create a new repo: `umt-design-{client}`.

```
umt-design-{client}/
├── style.css
└── functions.php
```

### `style.css`

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

`array( 'umt-design' )` ensures the base stylesheet loads first.

### Activate

Activate `umt-design-{client}`. WordPress loads `umt-design` as the parent automatically — it does not need to be separately activated.

---

## Step 3 — ACF Field Groups

Base plugin field groups load automatically from `umt-studio/acf-json/`. No action required.

> ⚠️ **Never edit ACF field groups on a client server.** Base field groups are read-only on all deployed installs — no save path is registered. See `ARCHITECTURE.md`.

If the client requires additional or modified ACF fields, register them in the child plugin via `acf_add_local_field_group()` on `acf/init`.

---

## Step 4 — WordPress Pages and Nav Menus

Create the following pages and assign templates:

| Page title | Slug | Template |
|---|---|---|
| Events | `/events/` | Events Archive |
| Prints | `/prints/` | Prints Archive |
| Books | `/books/` | Books Archive |
| Artists | `/artists/` | Artists Archive |
| Studio | `/studio/` | Default |

Adjust to match the client's nav structure.

Then **Appearance → Menus**: create a Primary menu (location: `primary`) and Footer menu (location: `footer`), and add the relevant pages to each.

---

## Step 5 — Flush Rewrite Rules

After activating plugins and theme: **Settings → Permalinks → Save Changes**. No changes needed — saving flushes rewrite rules and ensures CPT and page slugs resolve correctly.

---

## Step 6 — Verify

- [ ] `/events/`, `/prints/`, `/books/`, `/artists/` resolve
- [ ] `/works/{slug}/`, `/agents/{slug}/`, `/events/{slug}/` resolve
- [ ] ACF field groups appear on Works, Agents, Events edit screens
- [ ] Work type terms seeded — check **Posts → Works → Work Types**
- [ ] Nav menus in front-end header and footer
- [ ] Child theme stylesheet loading — check browser devtools network tab

---

## Modifying Base Field Groups

Base field groups must be edited on localhost only:

1. Open the ACF field group editor on localhost
2. To save: temporarily add a save path to `umt-studio.php`, save the field group, then remove it
3. Commit the updated JSON in `umt-studio/acf-json/`
4. Deploy to client servers via `git pull`

ACF Pro would make this cleaner by supporting per-plugin save paths. See `DEFERRED.md`.

---

## Adding a New Work Type Term

1. Add to `umt-studio/config/terms.php` — AAT ID as key, display name as value
2. Add to `umt-studio-{client}/config/terms.php` if the client needs it
3. Deploy both plugins
4. Re-seed: deactivate and reactivate `umt-studio`, or insert the term manually via **Posts → Works → Work Types**
