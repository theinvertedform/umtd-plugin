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
    foreach ( $post_types as $type => $args ) {
        if ( empty( $args['enabled'] ) ) {
            continue;
        }
        register_post_type(
            $type,
            array(
                'labels' => array(
                    'name'			=> $args['plural'],
					'singular_name'	=> $args['singular'],
					'add_new'		=> __( 'Add New', 'umtd' ),
					'add_new_item'	=> sprintf( __( 'Add New %s', 'umtd' ), $args['singular'] ),
					'edit_item'		=> sprintf( __( 'Edit %s', 'umtd' ), $args['singular'] ),
                ),

                'public'        => true,
                'has_archive'   => true,
                'rewrite'       => array( 'slug' => $args['slug'] ),
                'show_in_rest'  => true,
				'supports'		=> isset( $args['supports'] ) ? $args['supports'] : array( 'title' ),
				'show_in_menu'	=> isset( $args['show_in_menu'] ) ? $args['show_in_menu'] : true,
            )
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
/**?>*/
