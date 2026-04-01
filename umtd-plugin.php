<?php
/**
 * Plugin Name: umt.studio CMS
 * Description: Base plugin for the umt.studio white-label archive CMS. Registers CPTs (Works, Agents, Events), taxonomies, ACF field groups, schema.org JSON-LD, agent name logic, and AAT-aligned controlled vocabulary.
 * Author: UMT Studios
 * Version: 0.2.0
 * License: GPL
 * Text Domain: umtd
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Absolute path to the base plugin directory, with trailing slash.
 * Required by child plugins to load base vocabulary via UMTD_PATH . 'config/terms.php'.
 */
define( 'UMTD_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Base plugin version. Follows semantic versioning.
 * MAJOR — breaking change to filter hook signatures or ACF field key renames.
 * MINOR — new feature, backwards compatible.
 * PATCH — bugfix.
 */
define( 'UMTD_VERSION', '0.2.0' );

add_action( 'init', 'umtd_register_post_types' );
add_action( 'init', 'umtd_register_taxonomies' );

add_action( 'init', function() {
    load_plugin_textdomain( 'umtd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

/**
 * Load i18n config and apply umtd_i18n filter.
 *
 * Called by both umtd_register_post_types() and umtd_register_taxonomies().
 * Child plugins override default_lang, languages, and/or slug entries via
 * the umtd_i18n filter. Deferred to after plugins_loaded via init hook so
 * child plugin filters registered on plugins_loaded are available.
 *
 * @return array
 */
function umtd_get_i18n() {
	return apply_filters( 'umtd_i18n', require UMTD_PATH . 'config/i18n.php' );
}

/**
 * Register all CPTs defined in config/post-types.php.
 *
 * Primary rewrite slug is {default_lang}/{slug} — e.g. 'fr/artistes'. This
 * ensures all internally generated URLs (get_permalink, archives) include the
 * language prefix. Bare slugs without a language prefix are never registered
 * and will 404.
 *
 * has_archive is set to the prefixed slug string so the archive URL matches
 * the single URL pattern — e.g. 'fr/artistes' not true.
 *
 * Additional active languages generate supplementary rewrite rules at
 * /{lang}/{slug}/ and /{lang}/{slug}/{post-name}/ with lang query var set.
 *
 * @see config/post-types.php
 * @see config/i18n.php
 * @see umtd_activate()
 */
function umtd_register_post_types() {
	$post_types = apply_filters( 'umtd_post_types', require UMTD_PATH . 'config/post-types.php' );
	$i18n       = umtd_get_i18n();

	$passthrough_keys = array(
		'has_archive',
		'hierarchical',
		'capability_type',
		'map_meta_cap',
		'publicly_queryable',
		'exclude_from_search',
	);

	$default_lang = $i18n['default_lang'];

	foreach ( $post_types as $type => $args ) {
		if ( empty( $args['enabled'] ) ) {
			continue;
		}

		// Primary slug includes language prefix — generates /fr/artistes/{slug}/ URLs natively.
		// Bare slugs without prefix are never registered and will 404.
		$base_slug = isset( $i18n['slugs'][ $type ][ $default_lang ] )
			? $i18n['slugs'][ $type ][ $default_lang ]
			: $type;
		$slug = $default_lang . '/' . $base_slug;

		// has_archive must match the prefixed slug to generate the correct archive URL.
		// If explicitly false in config (e.g. umtd_events), preserve that.
		// If true or unset, set to the prefixed slug string.
		$has_archive = isset( $args['has_archive'] ) ? $args['has_archive'] : true;
		if ( true === $has_archive ) {
			$has_archive = $slug;
		}

		$defaults = array(
			'public'       => true,
			'has_archive'  => $has_archive,
			'show_in_rest' => true,
		);

		$labels = array(
			'labels' => array(
				'name'          => $args['plural'],
				'singular_name' => $args['singular'],
				'add_new'       => __( 'Add New', 'umtd' ),
				'add_new_item'  => sprintf( __( 'Add New %s', 'umtd' ), $args['singular'] ),
				'edit_item'     => sprintf( __( 'Edit %s', 'umtd' ), $args['singular'] ),
			),
		);

		$computed = array(
			'rewrite'      => array( 'slug' => $slug ),
			'supports'     => isset( $args['supports'] )     ? $args['supports']     : array( 'title' ),
			'show_in_menu' => isset( $args['show_in_menu'] ) ? $args['show_in_menu'] : true,
		);

		// has_archive handled above — remove from passthrough to prevent override conflict.
		$overrides = array_intersect_key( $args, array_flip( $passthrough_keys ) );
		unset( $overrides['has_archive'] );

		register_post_type(
			$type,
			array_merge( $defaults, $labels, $computed, $overrides )
		);

		// Supplementary rewrite rules for non-default active languages.
		foreach ( $i18n['languages'] as $lang ) {
			if ( $lang === $default_lang ) {
				continue;
			}
			$lang_slug = isset( $i18n['slugs'][ $type ][ $lang ] )
				? $i18n['slugs'][ $type ][ $lang ]
				: $base_slug;

			// Archive: /{lang}/{lang_slug}/
			add_rewrite_rule(
				'^' . $lang . '/' . $lang_slug . '/?$',
				'index.php?post_type=' . $type . '&lang=' . $lang,
				'top'
			);

			// Single: /{lang}/{lang_slug}/{post-name}/
			add_rewrite_rule(
				'^' . $lang . '/' . $lang_slug . '/([^/]+)/?$',
				'index.php?post_type=' . $type . '&name=$matches[1]&lang=' . $lang,
				'top'
			);
		}
	}
}

/**
 * Register all taxonomies defined in config/taxonomies.php.
 *
 * Primary rewrite slug is {default_lang}/{slug} — e.g. 'fr/type-oeuvre'.
 * Additional active languages generate supplementary rewrite rules.
 *
 * @see config/taxonomies.php
 * @see config/i18n.php
 * @see umtd_activate()
 */
function umtd_register_taxonomies() {
	$taxonomies = apply_filters( 'umtd_taxonomies', require UMTD_PATH . 'config/taxonomies.php' );
	$i18n       = umtd_get_i18n();

	$default_lang = $i18n['default_lang'];

	foreach ( $taxonomies as $taxonomy => $args ) {
		if ( empty( $args['enabled'] ) ) {
			continue;
		}

		$base_slug = isset( $i18n['slugs'][ $taxonomy ][ $default_lang ] )
			? $i18n['slugs'][ $taxonomy ][ $default_lang ]
			: $taxonomy;
		$slug = $default_lang . '/' . $base_slug;

		register_taxonomy(
			$taxonomy,
			$args['post_types'],
			array(
				'labels' => array(
					'name'          => $args['plural'],
					'singular_name' => $args['singular'],
					'add_new_item'  => sprintf( __( 'Add New %s', 'umtd' ), $args['singular'] ),
					'edit_item'     => sprintf( __( 'Edit %s', 'umtd' ), $args['singular'] ),
					'search_items'  => sprintf( __( 'Search %s', 'umtd' ), $args['plural'] ),
				),
				'hierarchical'      => $args['hierarchical'],
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => $slug ),
			)
		);

		// Supplementary rewrite rules for non-default active languages.
		foreach ( $i18n['languages'] as $lang ) {
			if ( $lang === $default_lang ) {
				continue;
			}
			$lang_slug = isset( $i18n['slugs'][ $taxonomy ][ $lang ] )
				? $i18n['slugs'][ $taxonomy ][ $lang ]
				: $base_slug;

			add_rewrite_rule(
				'^' . $lang . '/' . $lang_slug . '/([^/]+)/?$',
				'index.php?' . $taxonomy . '=$matches[1]&lang=' . $lang,
				'top'
			);
		}
	}
}

/**
 * Plugin activation hook.
 *
 * Registers CPTs and taxonomies before flush_rewrite_rules() so WordPress can
 * build rewrite rules for them. umtd_register_post_types() and
 * umtd_register_taxonomies() are also hooked to 'init', but the activation hook
 * fires before 'init', so they must be called explicitly here.
 *
 * Seeds the full AAT-aligned controlled vocabulary on first activation.
 * Child plugin activation hooks run the whitelist filter after this — they
 * require UMTD_PATH to be defined, so umtd-plugin must be active first.
 *
 * @see umtd_seed_terms()
 */
register_activation_hook( __FILE__, 'umtd_activate' );

function umtd_activate() {
    umtd_register_post_types();
    umtd_register_taxonomies();
    umtd_seed_terms();
    flush_rewrite_rules();
}

/**
 * Seed the controlled vocabulary on activation.
 *
 * Inserts all terms defined in config/terms.php into their respective
 * taxonomies. Skips terms that already exist. Stores the AAT numeric ID as
 * 'aat_id' term meta — for reference only, not used as an identifier.
 *
 * Term identity is the name (array value), not the AAT ID (array key).
 * Do not rename existing terms — this breaks existing term assignments.
 *
 * @see config/terms.php
 */
function umtd_seed_terms() {
    $vocabularies = require UMTD_PATH . 'config/terms.php';
    foreach ( $vocabularies as $taxonomy => $terms ) {
        foreach ( $terms as $aat_id => $name ) {
            if ( ! term_exists( $name, $taxonomy ) ) {
                $result = wp_insert_term( $name, $taxonomy );
                if ( ! is_wp_error( $result ) ) {
                    add_term_meta( $result['term_id'], 'aat_id', $aat_id, true );
                }
            }
        }
    }
}

// Load base plugin ACF field groups from acf-json/.
// No save_json filter registered — base field groups are read-only on all
// deployed installs. To modify: edit on localhost, commit updated JSON, deploy.
// See ARCHITECTURE.md — ACF Field Groups.
add_filter( 'acf/settings/load_json', function( $paths ) {
    $paths[] = UMTD_PATH . 'acf-json';
    return $paths;
} );

//add_filter( 'acf/settings/save_json', function( $path ) {
//    return UMTD_PATH . 'acf-json';
//} );

add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'lang';
    return $vars;
} );

// Schema.org JSON-LD output for umtd_works singles.
// Events and agents schema not yet implemented. See DEFERRED.md.
require_once UMTD_PATH . 'includes/schema.php';

// Agent name sync (post title from ACF name fields) and admin script enqueue.
require_once UMTD_PATH . 'includes/admin.php';
