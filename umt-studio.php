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
 * Register all CPTs defined in config/post-types.php.
 *
 * Reads the config array, applies the umtd_post_types filter (allowing child
 * plugins to add or modify CPT definitions), then registers each enabled type.
 *
 * Registration defaults are set here. Per-CPT overrides are passed through only
 * for keys listed in $passthrough_keys — all other config keys are consumed
 * internally (e.g. 'plural', 'singular', 'slug') and not passed to register_post_type().
 *
 * Merge order: defaults → labels → computed → overrides. Overrides always win.
 *
 * Called on 'init' and explicitly during activation (before 'init' fires).
 *
 * @see config/post-types.php
 * @see umtd_activate()
 */
function umtd_register_post_types() {
	$post_types = apply_filters( 'umtd_post_types', require UMTD_PATH . 'config/post-types.php' );

	// Keys from config that are allowed to override registration defaults.
	$passthrough_keys = array(
		'has_archive',
		'hierarchical',
		'capability_type',
		'map_meta_cap',
		'publicly_queryable',
		'exclude_from_search',
	);

	foreach ( $post_types as $type => $args ) {
		if ( empty( $args['enabled'] ) ) {
			continue;
		}

		$defaults = array(
			'public'       => true,
			'has_archive'  => true,
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
			'rewrite'      => array( 'slug' => $args['slug'] ),
			'supports'     => isset( $args['supports'] )     ? $args['supports']     : array( 'title' ),
			'show_in_menu' => isset( $args['show_in_menu'] ) ? $args['show_in_menu'] : true,
		);

		// Only pass through config keys that are valid register_post_type() args
		// and intended as per-CPT overrides of the defaults above.
		$overrides = array_intersect_key( $args, array_flip( $passthrough_keys ) );

		register_post_type(
			$type,
			array_merge( $defaults, $labels, $computed, $overrides )
		);
	}
}

/**
 * Register all taxonomies defined in config/taxonomies.php.
 *
 * Reads the config array, applies the umtd_taxonomies filter, then registers
 * each enabled taxonomy. All registration args are derived directly from the
 * config — there are no passthrough keys; the config is the complete definition.
 *
 * Called on 'init' and explicitly during activation (before 'init' fires).
 *
 * @see config/taxonomies.php
 * @see umtd_activate()
 */
function umtd_register_taxonomies() {
	$taxonomies = apply_filters( 'umtd_taxonomies', require UMTD_PATH . 'config/taxonomies.php' );

	foreach ( $taxonomies as $taxonomy => $args ) {
		if ( empty( $args['enabled'] ) ) {
			continue;
		}
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
				'rewrite'           => array( 'slug' => $args['slug'] ),
			)
		);
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
 * require UMTD_PATH to be defined, so umt-studio must be active first.
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

// Schema.org JSON-LD output for umtd_works singles.
// Events and agents schema not yet implemented. See DEFERRED.md.
require_once UMTD_PATH . 'includes/schema.php';

// Agent name sync (post title from ACF name fields) and admin script enqueue.
require_once UMTD_PATH . 'includes/admin.php';
