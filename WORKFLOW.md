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

> ⚠️ The base ACF field group includes `agents_artists` and `agents_authors` relationship fields. These are Piroir-specific hacks encoding agent role as field name, pending ACF Pro Repeater migration. For a new client: (a) leave them in place and ignore them, (b) hide via child plugin ACF override, or (c) wait for the role model refactor. See `DEFERRED.md`.

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

Add `.github/workflows/deploy.yml` to the repo. The workflow calls the generic deploy script with the target path:

```yaml
--parameters 'commands=["/usr/local/bin/deploy /var/www/{client}/htdocs/wp-content/plugins/umt-studio-{client}"]'
```

See `INFRASTRUCTURE.md` — Deploy Scripts for the full workflow template.

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

Add `.github/workflows/deploy.yml` to the repo with the correct path:

```yaml
--parameters 'commands=["/usr/local/bin/deploy /var/www/{client}/htdocs/wp-content/themes/umt-design-{client}"]'
```

---

## Step 3 — ACF Field Groups

Base plugin field groups load automatically from `umt-studio/acf-json/`. No action required.

> ⚠️ **Never edit ACF field groups on a client server.** Base field groups are read-only on all deployed installs — no save path is registered. See `ARCHITECTURE.md`.

If the client requires additional or modified ACF fields, register them in the child plugin via `acf_add_local_field_group()` on `acf/init`, inside the `plugins_loaded` callback.

---

## Step 4 — WordPress Pages and Nav Menus

Create pages and assign templates to match the client's nav structure. Example for Piroir:

| Page title | Slug | Template |
|---|---|---|
| Événements | `/events/` | Events Archive |
| Estampes | `/prints/` | Prints Archive |
| Livres | `/books/` | Books Archive |
| Artistes | `/artists/` | Artists Archive |
| Studio | `/studio/` | Default |

Note: page slugs are not language-prefixed in the current implementation — only CPT single URLs are language-prefixed via the i18n rewrite system. Page template URL structure is a client decision.

Then **Appearance → Menus**: create a Primary menu (location: `primary`) and Footer menu (location: `footer`), and add the relevant pages to each.

---

## Step 5 — Flush Rewrite Rules

After activating plugins and theme: **Settings → Permalinks → Save Changes**. This is required after any plugin activation that registers CPTs, taxonomies, or custom rewrite rules. The i18n rewrite rules are registered on `init` — they are not flushed automatically on activation.

---

## Step 6 — Verify

- [ ] CPT single URLs resolve with language prefix — e.g. `/fr/artistes/{slug}/`, `/en/artists/{slug}/`
- [ ] Bare CPT slugs without language prefix 404
- [ ] Page template URLs resolve — `/events/`, `/prints/`, `/books/`, `/artists/`
- [ ] ACF field groups appear on Works, Agents, Events edit screens
- [ ] Work type terms seeded — check **Posts → Works → Work Types**
- [ ] Nav menus in front-end header and footer
- [ ] Child theme stylesheet loading — check browser devtools network tab
- [ ] GitHub Actions deploy workflow triggers on push and succeeds

---

## Modifying Base Field Groups

Base field groups must be edited on localhost only:

1. Open the ACF field group editor on localhost
2. To save: temporarily add a save path to `umt-studio.php`, save the field group, then remove it
3. Commit the updated JSON in `umt-studio/acf-json/`
4. Deploy to client servers via `git push` → GitHub Actions

ACF Pro would make this cleaner by supporting per-plugin save paths. See `DEFERRED.md`.

---

## Adding a New Work Type Term

1. Add to `umt-studio/config/terms.php` — AAT ID as key, display name as value
2. Add to `umt-studio-{client}/config/terms.php` if the client needs it
3. Deploy both plugins
4. Re-seed: deactivate and reactivate `umt-studio`, or insert the term manually via **Posts → Works → Work Types**
