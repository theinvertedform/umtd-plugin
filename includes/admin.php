<?php
/**
 * Admin script enqueue and agent name sync.
 *
 * Handles two concerns:
 * - Syncing the post title and slug from ACF name fields on save (umtd_agents only).
 * - Enqueueing admin-fields.js on edit screens for configured post types.
 *
 * @package umt-studio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that require admin-fields.js enqueued on their edit screens.
 *
 * Declared as a global so umtd_enqueue_admin_scripts() can reference it without
 * being coupled to a hardcoded list. Add post types here as new admin script
 * dependencies arise.
 *
 * Note: global scope is a known limitation — a future refactor to a config array
 * passed via filter would be cleaner. See DEFERRED.md — Class-based Plugin Architecture.
 */
$umtd_admin_script_post_types = array(
	'umtd_agents',
	'umtd_works',
);

add_action( 'init', function() {
    remove_post_type_support( 'attachment', 'title' );
}, 99 );

/**
 * Sync post title and slug from ACF name fields on save.
 *
 * Fires on acf/save_post at priority 20 (after ACF has written field values,
 * which run at priority 10).
 *
 * Post title is the sort key, not the display name:
 * - Person:       "Last, First" (or last name only if no first name)
 * - Organization: name_display
 *
 * Templates must always use get_field( 'name_display', $id ) for output —
 * never get_the_title(), which returns the sort key.
 *
 * The remove_action / add_action wrapper around wp_update_post() prevents
 * infinite recursion: wp_update_post() triggers save_post, which would
 * re-trigger acf/save_post and call this function again. Temporarily
 * unhooking breaks the loop.
 *
 * @param int $post_id WP post ID.
 * @return void
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
			// Standard sort format: "Last, First". First name optional.
			$title = $first ? $last . ', ' . $first : $last;
		} else {
			// No last name — fall back to name_display rather than leaving title empty.
			$title = $name_display;
		}
	} else {
		$title = $name_display;
	}

	if ( empty( $title ) ) {
		return;
	}

	// Temporarily unhook to prevent infinite recursion via wp_update_post → save_post → acf/save_post.
	remove_action( 'acf/save_post', 'umtd_sync_agent_title', 20 );

	wp_update_post( array(
		'ID'         => $post_id,
		'post_title' => $title,
		'post_name'  => sanitize_title( $title ),
	) );

	add_action( 'acf/save_post', 'umtd_sync_agent_title', 20 );
}

/**
 * Enqueue admin-fields.js on edit screens for configured post types.
 *
 * Checks both the screen hook (post.php / post-new.php) and the post type
 * against $umtd_admin_script_post_types before enqueueing. UMTD_VERSION is
 * used as the cache-busting version string.
 *
 * @param string $hook Current admin page hook suffix.
 * @return void
 */
add_action( 'admin_enqueue_scripts', 'umtd_enqueue_admin_scripts' );

function umtd_enqueue_admin_scripts( $hook ) {
	global $post, $umtd_admin_script_post_types;

	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	if ( ! $post || ! in_array( $post->post_type, $umtd_admin_script_post_types, true ) ) {
		return;
	}

	wp_enqueue_script(
		'umtd-admin-fields',
		plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-fields.js',
		array( 'jquery' ),
		UMTD_VERSION,
		true
	);
}


add_filter( 'attachment_fields_to_edit', function( $form_fields, $post ) {
    // Remove caption and description entirely
    unset( $form_fields['post_excerpt'] );
    unset( $form_fields['post_content'] );

    // Make alt text read-only
    if ( isset( $form_fields['image-alt'] ) ) {
        $alt = get_post_meta( $post->ID, '_wp_attachment_image_alt', true );
        $form_fields['image-alt']['input'] = 'html';
        $form_fields['image-alt']['html'] = '<input type="text" readonly value="' . esc_attr( $alt ) . '" style="background:#f0f0f0;cursor:default;" />';
    }

    return $form_fields;
}, 10, 2 );


