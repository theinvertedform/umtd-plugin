<?php
/*
 * Plugin Name: umt.studio prototype CMS
 * Description: Register CPTs for events, artists, and works
 * Author: UMT Studios
 * Version: 0.1
 * License: GPL
 * Text Domain: umtd
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'UMTD_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'init', 'umtd_register_post_types' );

add_action( 'init', function() {
    load_plugin_textdomain( 'umtd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

function umtd_register_post_types() {
	$post_types = apply_filters( 'umtd_post_types', require UMTD_PATH . 'config/post-types.php' );

	// Keys from config that are allowed to override registration defaults
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
			'supports'     => isset( $args['supports'] )    ? $args['supports']    : array( 'title' ),
			'show_in_menu' => isset( $args['show_in_menu'] ) ? $args['show_in_menu'] : true,
		);

		$overrides = array_intersect_key( $args, array_flip( $passthrough_keys ) );

		register_post_type(
			$type,
			array_merge( $defaults, $labels, $computed, $overrides )
		);
	}
}

add_action( 'init', 'umtd_register_taxonomies' );

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
 *
 * rewrite flush on activation
 *
 */
register_activation_hook( __FILE__, function() {
    umtd_register_post_types();
    umtd_register_taxonomies();
    flush_rewrite_rules();
} );

/**
 *
 *	add custom ACF fields
 *
 */
add_filter( 'acf/settings/load_json', function( $paths ) {
    $paths[] = UMTD_PATH . 'acf-json';
    return $paths;
} );

add_filter( 'acf/settings/save_json', function( $path ) {
    return UMTD_PATH . 'acf-json';
} );

// register all terms with AAT ID as term meta
register_activation_hook( __FILE__, 'umtd_activate' );

function umtd_activate() {
    umtd_register_post_types();
    umtd_register_taxonomies();
    umtd_seed_terms();
    flush_rewrite_rules();
}

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

// schema.org for individual works, to be expanded to all pages
require_once plugin_dir_path( __FILE__ ) . 'includes/schema.php';

// sets post title from name_first and name_last fields
require_once plugin_dir_path( __FILE__ ) . 'includes/agents.php';
