# UMT Studio — New Client Workflow

This document covers the code-side steps to onboard a new client: creating the child plugin and child theme, configuring ACF, and setting up WordPress pages and menus. Server provisioning (nginx, MariaDB, WordPress install, git clone, SSL) is covered in `INFRASTRUCTURE.md` — Client Deploy Process.

---

## Prerequisites

- Server provisioned and WordPress installed per `INFRASTRUCTURE.md`
- `umt-studio` and `umt-design` cloned and activated on the client server
- ACF installed and activated
- Git repos initialized for `umt-studio-{client}` and `umt-design-{client}`

---

## Step 1 — Create the Child Plugin

Create a new repo: `umt-studio-{client}`, forked or copied from `umt-studio-child`.

```
umt-studio-{client}/
├── umt-studio-{client}.php
└── config/
    └── terms.php
```

### `umt-studio-{client}.php`

Copy `umt-studio-child.php` as the starting point. Update the plugin header only:

- `Plugin Name`
- `Text Domain`

Constants (`UMTD_CHILD_*`) and function prefixes (`umtd_child_*`) are intentionally generic and do not need to change per client.

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

A flat indexed array of term names — AAT IDs are omitted because comparison in the activation hook is by name only. This differs from the base `config/terms.php` format (`AAT_ID => name`) by design. Term names must exactly match the base vocabulary in `umt-studio/config/terms.php`. Do not rename existing values — this breaks existing term assignments.

### Declare Active Languages

In `umt-studio-{client}.php`, inside the `plugins_loaded` callback, declare which languages are active for this client via the `umtd_i18n` filter:

```php
add_action( 'plugins_loaded', function() {
    if ( ! defined( 'UMTD_PATH' ) ) {
        // admin notice + return
    }
    add_filter( 'umtd_i18n', function( $i18n ) {
        $i18n['default_lang'] = 'fr'; // primary language — drives registered slug
        $i18n['languages']    = array( 'fr', 'en' ); // all active languages
        return $i18n;
    } );
    // ACF load path filter also goes here
} );
```

`default_lang` sets the primary URL prefix — e.g. `fr` produces `/fr/artistes/{slug}/` as the canonical URL. Each additional language in `languages` gets supplementary rewrite rules. Languages absent from the array generate no routes.

Slug translations for all languages are defined in the base plugin `config/i18n.php` — the child plugin only declares which languages are active.

### Adding a New Language to the Platform

To add a language that doesn't yet have slug translations in the base plugin:

1. Add slug translations to `umt-studio/config/i18n.php` for all CPTs and taxonomies
2. Deploy `umt-studio`
3. Add the language code to the client's `languages` array
4. Deploy the child plugin
5. Flush rewrite rules — Settings → Permalinks → Save

### Activate

Activate `umt-studio-{client}` **after** `umt-studio`. The activation hook seeds whitelisted terms and deletes non-whitelisted ones. WordPress plugin load order is not guaranteed, but the `plugins_loaded` pattern in the child plugin handles this correctly — dependency-sensitive code runs after all plugins have loaded.

### CI/CD

The deploy workflow is already present in `umt-studio-child` at `.github/workflows/deploy.yml` with the `on:` trigger commented out. To activate it for a client repo:

1. Uncomment the `on:` block
2. Substitute `{client}` in both SSM target paths — base plugin and child plugin steps
3. Push to main to verify the workflow fires correctly

See `INFRASTRUCTURE.md` — Actions Workflow for the full template and path conventions.

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

### CI/CD

The child theme repo requires its own deploy workflow following the same pattern as the child plugin — deploying the base theme first, then the child theme. Add `.github/workflows/deploy.yml` with the base and child theme target paths substituted. See `INFRASTRUCTURE.md` — Actions Workflow.

---

## Step 3 — ACF Field Groups

Base plugin field groups load automatically from `umt-studio/acf-json/`. No action required.

**Never edit ACF field groups on a client server.** Base field groups are read-only on all deployed installs — no save path is registered. See `ARCHITECTURE.md`.

If the client requires additional or modified ACF fields, register them in the child plugin via `acf_add_local_field_group()` on `acf/init`, inside the `plugins_loaded` callback.

---

## Step 4 — WordPress Pages and Nav Menus

Create pages and assign templates to match the client's nav structure:

| Page title | Slug | Template |
|---|---|---|
| Events | `/events/` | Events Archive |
| Artists | `/artists/` | Artists Archive |
| Studio | `/studio/` | Default |

Adjust page titles, slugs, and templates to match the client's information architecture. Page slugs are not language-prefixed — only CPT single URLs are language-prefixed via the i18n rewrite system.

Then **Appearance → Menus**: create a Primary menu (location: `primary`) and Footer menu (location: `footer`), and add the relevant pages to each.

---

## Step 5 — Flush Rewrite Rules

After activating plugins and theme: **Settings → Permalinks → Save Changes**. Required after any activation that registers CPTs, taxonomies, or custom rewrite rules — the i18n rewrite rules are not flushed automatically on activation.

---

## Step 6 — Verify

- [ ] CPT single URLs resolve with language prefix — e.g. `/fr/artistes/{slug}/`, `/en/artists/{slug}/`
- [ ] Bare CPT slugs without language prefix 404
- [ ] Page template URLs resolve
- [ ] ACF field groups appear on Works, Agents, Events edit screens
- [ ] Work type terms seeded — check **Posts → Works → Work Types**
- [ ] Nav menus in front-end header and footer
- [ ] Child theme stylesheet loading — check browser devtools network tab
- [ ] GitHub Actions deploy workflow triggers on push and succeeds

---

## Modifying Base Field Groups

Base field groups must be edited on localhost only:

1. Open the ACF field group editor on localhost
2. Temporarily add a save path to `umt-studio.php`, save the field group, then remove it
3. Commit the updated JSON in `umt-studio/acf-json/`
4. Deploy to client servers via `git push` → GitHub Actions

---

## Adding a New Work Type Term

1. Add to `umt-studio/config/terms.php` — AAT ID as key, display name as value
2. Add to `umt-studio-{client}/config/terms.php` if the client needs it
3. Deploy both plugins
4. Re-seed: deactivate and reactivate `umt-studio`, or insert the term manually via **Posts → Works → Work Types**
