<?php
/**
 * Agent name management.
 *
 * @package umt-studio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sync post title and slug from controlled name fields on save.
 *
 * For persons:      title = "Last, First"
 * For organizations: title = name_display
 */
add_action( 'acf/save_post', 'umtd_sync_agent_title', 20 );

function umtd_sync_agent_title( $post_id ) {
	if ( get_post_type( $post_id ) !== 'umtd_agents' ) {
		return;
	}

	$agent_type   = get_field( 'agent_type', $post_id );
	$name_display = get_field( 'name_display', $post_id );

	if ( 'person' === $agent_type ) {
		$first = trim( get_field( 'name_first', $post_id ) );
		$last  = trim( get_field( 'name_last', $post_id ) );

		if ( $last ) {
			$title = $first ? $last . ', ' . $first : $last;
		} else {
			$title = $name_display;
		}
	} else {
		$title = $name_display;
	}

	if ( empty( $title ) ) {
		return;
	}

	// Unhook to prevent infinite loop
	remove_action( 'acf/save_post', 'umtd_sync_agent_title', 20 );

	wp_update_post( array(
		'ID'         => $post_id,
		'post_title' => $title,
		'post_name'  => sanitize_title( $title ),
	) );

	add_action( 'acf/save_post', 'umtd_sync_agent_title', 20 );
}

add_action( 'admin_enqueue_scripts', 'umtd_enqueue_agent_admin_scripts' );

function umtd_enqueue_agent_admin_scripts( $hook ) {
	global $post;
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
		return;
	}
	if ( ! $post || $post->post_type !== 'umtd_agents' ) {
		return;
	}
	wp_enqueue_script(
		'umtd-agent-name',
		plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/agent-name.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);
}
