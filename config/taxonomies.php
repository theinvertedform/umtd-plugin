<?php
/**
 * Taxonomy definitions for umt-studio.
 *
 * Consumed by umtd_register_taxonomies() in umt-studio.php, which applies the
 * umtd_taxonomies filter before registration. All keys are used directly for
 * registration — there are no passthrough keys; the config is the complete definition.
 *
 * Planned but not yet registered:
 * - umtd_agent_role — pending ACF Pro Repeater migration. See DEFERRED.md — Agent Role Model.
 *
 * @package umt-studio
 * @see umt-studio.php umtd_register_taxonomies()
 */

return array(

	'umtd_work_type' => array(
		'enabled'      => true,
		'plural'       => 'Work Types',
		'singular'     => 'Work Type',
		'hierarchical' => true,
		'post_types'   => array( 'umtd_works' ),
		'capabilities' => array(
		    'manage_terms' => 'manage_options',
		    'edit_terms'   => 'manage_options',
		    'delete_terms' => 'manage_options',
		    'assign_terms' => 'edit_posts',
		),
	),

	'umtd_event_type' => array(
		'enabled'      => true,
		'plural'       => 'Event Types',
		'singular'     => 'Event Type',
		'hierarchical' => true,
		'post_types'   => array( 'umtd_events' ),
		'capabilities' => array(
		    'manage_terms' => 'manage_options',
		    'edit_terms'   => 'manage_options',
		    'delete_terms' => 'manage_options',
		    'assign_terms' => 'edit_posts',
		),
	),

	'umtd_medium' => array(
	    'enabled'      => true,
	    'plural'       => 'Mediums',
	    'singular'     => 'Medium',
	    'hierarchical' => false,
	    'post_types'   => array( 'umtd_works' ),
		'capabilities' => array(
		    'manage_terms' => 'manage_options',
		    'edit_terms'   => 'manage_options',
		    'delete_terms' => 'manage_options',
		    'assign_terms' => 'edit_posts',
		),
	),
);
