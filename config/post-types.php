<?php
/**
 * CPT definitions for umt-studio.
 *
 * Consumed by umtd_register_post_types() in umt-studio.php, which applies the
 * umtd_post_types filter before registration. Keys not listed in
 * $passthrough_keys are consumed internally and not passed to register_post_type().
 *
 * has_archive defaults to true unless overridden here. umtd_events sets it to
 * false to prevent a URL conflict with the /events/ WordPress page — CPT archive
 * URLs are built from the slug, which would collide with the page slug.
 *
 * show_in_menu can be used to control placement in the WP admin sidebar.
 * Currently deferred — see DEFERRED.md.
 *
 * @package umt-studio
 * @see umt-studio.php umtd_register_post_types()
 */

return array(

	'umtd_events' => array(
		'enabled'     => true,
		'plural'      => 'Events',
		'singular'    => 'Event',
		'slug'        => 'events',
		'supports'    => array( 'title', 'thumbnail', 'revisions' ),
		'has_archive' => false, // Prevents URL conflict with the /events/ WordPress page.
	),

	'umtd_works' => array(
		'enabled'  => true,
		'plural'   => 'Works',
		'singular' => 'Work',
		'slug'     => 'works',
		'supports' => array( 'title', 'thumbnail', 'revisions' ),
	),

	'umtd_agents' => array(
		'enabled'  => true,
		'plural'   => 'Agents',
		'singular' => 'Agent',
		'slug'     => 'agents',
		'supports' => array( 'title', 'thumbnail', 'revisions' ),
	),

);
